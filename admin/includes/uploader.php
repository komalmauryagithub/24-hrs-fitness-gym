<?php
/**
 * DARK MATTER — Elite Fitness
 * Secure Image Uploader & Persistent Storage Driver
 *
 * Supports:
 * 1. Local Filesystem / Mounted Persistent Cloud Disks (Render Disks, Railway Volumes)
 * 2. S3-Compatible Cloud Object Storage (Cloudflare R2, AWS S3, DigitalOcean Spaces, MinIO)
 */

declare(strict_types=1);

namespace DarkMatter\Admin;

require_once dirname(__DIR__, 2) . '/includes/Env.php';
use DarkMatter\Env;

class Uploader
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    private const MAX_FILE_SIZE = 5242880; // 5 MB

    /**
     * Process an uploaded file and store it safely in the target destination.
     *
     * @param array  $fileEntry   The $_FILES['input_name'] entry.
     * @param string $subDir      Subdirectory inside uploads/ (e.g. 'gallery', 'trainers').
     * @param string|null $error  Output error message if upload fails.
     * @return string|null        The relative path or CDN URL of the saved file or null on failure.
     */
    public static function processUpload(array $fileEntry, string $subDir = 'gallery', ?string &$error = null): ?string
    {
        if (!isset($fileEntry['error']) || is_array($fileEntry['error'])) {
            $error = 'Invalid upload parameters.';
            return null;
        }

        switch ($fileEntry['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $error = 'No file was transmitted.';
                return null;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error = 'Exceeded maximum permitted file size threshold (5MB limit).';
                return null;
            default:
                $error = 'Internal server file transfer anomaly encountered.';
                return null;
        }

        if ($fileEntry['size'] > self::MAX_FILE_SIZE) {
            $error = 'File payload exceeds the maximum 5MB constraint.';
            return null;
        }

        // Validate MIME type via FileInfo binary inspection
        $tmpPath = $fileEntry['tmp_name'];
        if (!is_uploaded_file($tmpPath)) {
            $error = 'Invalid temporary upload file.';
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpPath);

        if (!isset(self::ALLOWED_MIME_TYPES[$mime])) {
            $error = 'File type rejected. Only JPEG, PNG, WebP, and GIF images are permitted.';
            return null;
        }

        // Validate extension matches MIME type
        $originalExt = strtolower(pathinfo((string)$fileEntry['name'], PATHINFO_EXTENSION));
        $safeExt = self::ALLOWED_MIME_TYPES[$mime];

        if ($safeExt === 'jpg' && !in_array($originalExt, ['jpg', 'jpeg'], true)) {
            $error = 'File extension does not correspond to genuine JPEG header bytes.';
            return null;
        }

        $safeSubDir = preg_replace('/[^a-zA-Z0-9_\-]/', '', $subDir);
        $randomFileName = bin2hex(random_bytes(16)) . '.' . $safeExt;
        $driver = strtolower((string) Env::get('STORAGE_DRIVER', 'local'));

        // 1. S3-Compatible Cloud Object Storage Driver
        if ($driver === 's3') {
            return self::uploadToS3($tmpPath, $safeSubDir . '/' . $randomFileName, $mime, $error);
        }

        // 2. Local Filesystem / Mounted Persistent Cloud Disk Driver
        $targetDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $safeSubDir;

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                $error = 'Failed to initialize secure upload destination directory.';
                return null;
            }
        }

        $destinationPath = $targetDir . DIRECTORY_SEPARATOR . $randomFileName;

        if (!move_uploaded_file($tmpPath, $destinationPath)) {
            $error = 'Failed to persist uploaded asset to disk.';
            return null;
        }

        return 'uploads/' . $safeSubDir . '/' . $randomFileName;
    }

    /**
     * Upload asset to S3-compatible cloud bucket using native REST API and SigV4.
     */
    private static function uploadToS3(string $localFile, string $remoteKey, string $mime, ?string &$error = null): ?string
    {
        $bucket   = (string) Env::get('S3_BUCKET', '');
        $region   = (string) Env::get('S3_REGION', 'us-east-1');
        $key      = (string) Env::get('S3_KEY', '');
        $secret   = (string) Env::get('S3_SECRET', '');
        $endpoint = (string) Env::get('S3_ENDPOINT', 'https://s3.amazonaws.com');
        $publicUrl= (string) Env::get('S3_PUBLIC_URL', '');

        if ($bucket === '' || $key === '' || $secret === '') {
            $error = 'S3 Object Storage is enabled but missing credentials in environment configuration.';
            return null;
        }

        $fileData = file_get_contents($localFile);
        if ($fileData === false) {
            $error = 'Unable to read upload payload for cloud transfer.';
            return null;
        }

        // Parse host
        $parsedEndpoint = parse_url($endpoint);
        $endpointHost = $parsedEndpoint['host'] ?? 's3.amazonaws.com';
        $host = "{$bucket}.{$endpointHost}";

        // If path-style endpoint or custom endpoint
        $uri = '/' . ltrim($remoteKey, '/');
        $requestUrl = (rtrim($endpoint, '/')) . '/' . $bucket . $uri;

        $now = time();
        $amzDate = gmdate('Ymd\THis\Z', $now);
        $dateStamp = gmdate('Ymd', $now);

        $payloadHash = hash('sha256', $fileData);

        // Canonical Request
        $canonicalHeaders = "content-type:{$mime}\nhost:{$host}\nx-amz-content-sha256:{$payloadHash}\nx-amz-date:{$amzDate}\n";
        $signedHeaders = "content-type;host;x-amz-content-sha256;x-amz-date";

        $canonicalRequest = "PUT\n{$uri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";

        // String to sign
        $algorithm = "AWS4-HMAC-SHA256";
        $credentialScope = "{$dateStamp}/{$region}/s3/aws4_request";
        $stringToSign = "{$algorithm}\n{$amzDate}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);

        // Signature Key Calculation
        $kDate    = hash_hmac('sha256', $dateStamp, "AWS4" . $secret, true);
        $kRegion  = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', "s3", $kRegion, true);
        $kSigning = hash_hmac('sha256', "aws4_request", $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authorizationHeader = "{$algorithm} Credential={$key}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        // Perform HTTP PUT via cURL
        $ch = curl_init($requestUrl);
        curl_setopt_array($ch, [
            CURLOPT_PUT            => true,
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => $fileData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                "Host: {$host}",
                "Content-Type: {$mime}",
                "x-amz-date: {$amzDate}",
                "x-amz-content-sha256: {$payloadHash}",
                "Authorization: {$authorizationHeader}"
            ]
        ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($statusCode >= 200 && $statusCode < 300) {
            if ($publicUrl !== '') {
                return rtrim($publicUrl, '/') . '/' . ltrim($remoteKey, '/');
            }
            return $requestUrl;
        }

        error_log("[S3 UPLOAD ERROR HTTP {$statusCode}]: {$response} | {$curlError}");
        $error = "Cloud object storage rejected upload (HTTP {$statusCode}). Check S3 credentials.";
        return null;
    }

    /**
     * Validate an image URL or local asset path.
     */
    public static function validateImageUrl(string $url): bool
    {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return false;
        }

        // Allow local uploads or assets
        if (str_starts_with($trimmed, 'uploads/') || str_starts_with($trimmed, 'assets/')) {
            return true;
        }

        // Allow standard HTTPS or HTTP URLs
        if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
            $scheme = parse_url($trimmed, PHP_URL_SCHEME);
            return in_array($scheme, ['https', 'http'], true);
        }

        return false;
    }

    /**
     * Resolve correct public asset URL whether stored locally or on cloud CDN.
     */
    public static function assetUrl(string $path): string
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return '';
        }

        // If absolute URL, return as-is
        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }

        $appUrl = rtrim((string) Env::get('APP_URL', ''), '/');
        return ($appUrl !== '' ? $appUrl . '/' : '') . ltrim($trimmed, '/');
    }
}
