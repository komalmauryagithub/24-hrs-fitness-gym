/**
 * DARK MATTER — Elite Fitness
 * Programs Filter & Protocol Inspection Modal Logic
 * Reads dynamic protocol specifications directly from MySQL database.
 */

document.addEventListener('DOMContentLoaded', () => {
  loadServerProtocolData();
  initProgramFilter();
  initProtocolDetailsModal();
});

// Fallback protocol dictionary if server payload is not injected
let PROTOCOL_DETAILS = {
  'hypertrophy-strength': {
    title: 'STRENGTH & MUSCLE BUILDING',
    category: 'STRENGTH // WEIGHT TRAINING',
    intensity: '5 / 5 [HIGH LOAD]',
    duration: '75 Minutes',
    caloricExpenditure: '650 – 850 kcal',
    targetAdaptation: 'Build lean muscle mass, increase maximum lifting strength, and improve bone density.',
    equipmentStations: 'Power cages, Olympic barbells, calibrated weight plates, dumbbells, and cable stations.',
    coachRatio: '1 Coach : 6 Members',
    recommendedCadence: '3 to 4 workouts / week'
  },
  'metabolic-conditioning': {
    title: 'CARDIO & FAT BURN (HIIT)',
    category: 'CARDIO // FAT LOSS',
    intensity: '4.5 / 5 [HIGH ENERGY]',
    duration: '50 Minutes',
    caloricExpenditure: '750 – 1,000 kcal',
    targetAdaptation: 'Maximum calorie burn, cardiovascular stamina, endurance, and faster metabolism.',
    equipmentStations: 'Curved treadmills, rowing machines, assault air bikes, and indoor sprint turf.',
    coachRatio: '1 Coach : 8 Members',
    recommendedCadence: '2 to 3 workouts / week'
  },
  'cryo-thermal-recovery': {
    title: 'STEAM & SAUNA RECOVERY',
    category: 'RECOVERY // RELAXATION',
    intensity: '1 / 5 [PASSIVE RECOVERY]',
    duration: '45 – 60 Minutes',
    caloricExpenditure: '150 – 250 kcal',
    targetAdaptation: 'Relieve muscle soreness, accelerate joint recovery, relax tight muscles, and de-stress.',
    equipmentStations: 'Hot steam rooms, dry Finnish cedar saunas, and hot shower facilities.',
    coachRatio: 'Attendant on duty',
    recommendedCadence: 'After heavy training sessions or rest days'
  },
  'elite-personal-coaching': {
    title: '1-ON-1 PERSONAL TRAINING',
    category: 'PERSONAL COACHING // TAILORED',
    intensity: 'Custom (Adapted to you)',
    duration: '60 Minutes',
    caloricExpenditure: '500 – 900 kcal',
    targetAdaptation: 'Personalized workout routine, continuous form correction, diet guidance, and goal tracking.',
    equipmentStations: 'Full gym floor priority access, InBody composition scan, dedicated trainer.',
    coachRatio: '1:1 Dedicated Certified Coach',
    recommendedCadence: '2 to 4 sessions / week'
  },
  'combat-conditioning': {
    title: 'BOXING & COMBAT FITNESS',
    category: 'FUNCTIONAL // AGILITY & POWER',
    intensity: '4.5 / 5 [HIGH ENERGY]',
    duration: '60 Minutes',
    caloricExpenditure: '700 – 950 kcal',
    targetAdaptation: 'Improve speed, punching power, hand-eye coordination, reflex agility, and core strength.',
    equipmentStations: 'Heavy punching bags, speed ropes, focus mitts, and boxing conditioning turf.',
    coachRatio: '1 Coach : 6 Members',
    recommendedCadence: '2 to 3 sessions / week'
  },
  'functional-calisthenics': {
    title: 'CALISTHENICS & MOBILITY',
    category: 'FUNCTIONAL // BODY CONTROL',
    intensity: '3.5 / 5 [CONTROL & CORE]',
    duration: '55 Minutes',
    caloricExpenditure: '450 – 600 kcal',
    targetAdaptation: 'Master pull-ups, dips, core abdominal stability, joint flexibility, and bodyweight control.',
    equipmentStations: 'Monkey bars, gymnastics rings, parallel bars, resistance bands, and stall bars.',
    coachRatio: '1 Coach : 8 Members',
    recommendedCadence: '3 sessions / week'
  }
};

/**
 * Load Dynamic Protocol Specifications from server JSON script tag if present
 */
function loadServerProtocolData() {
  const scriptEl = document.getElementById('serverProtocolData');
  if (scriptEl && scriptEl.textContent.trim()) {
    try {
      const parsed = JSON.parse(scriptEl.textContent.trim());
      if (typeof parsed === 'object' && Object.keys(parsed).length > 0) {
        PROTOCOL_DETAILS = parsed;
      }
    } catch (e) {
      console.warn('Could not parse server protocol data, using defaults.', e);
    }
  }
}

/**
 * Filter Cards by Category
 */
function initProgramFilter() {
  const filterTabs = document.querySelectorAll('.programs-filter .filter-tab');
  const programCards = document.querySelectorAll('.program-grid-item');
  const emptyState = document.getElementById('programsEmptyState');

  if (!filterTabs.length || !programCards.length) return;

  filterTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      filterTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      const filterValue = tab.getAttribute('data-filter');
      let visibleCount = 0;

      programCards.forEach(card => {
        const cardCategory = card.getAttribute('data-category');
        if (filterValue === 'all' || cardCategory === filterValue) {
          card.style.display = 'block';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
      }
    });
  });
}

/**
 * Protocol Inspection Modal
 */
function initProtocolDetailsModal() {
  const detailButtons = document.querySelectorAll('.btn-inspect-protocol');
  const modal = document.getElementById('protocolModal');

  if (!detailButtons.length || !modal) return;

  detailButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const protocolKey = btn.getAttribute('data-protocol-id');
      const data = PROTOCOL_DETAILS[protocolKey];

      if (!data) return;

      const titleEl = document.getElementById('modalProtoTitle');
      const catEl = document.getElementById('modalProtoCategory');
      const intEl = document.getElementById('modalProtoIntensity');
      const durEl = document.getElementById('modalProtoDuration');
      const calEl = document.getElementById('modalProtoCalories');
      const adpEl = document.getElementById('modalProtoAdaptation');
      const stnEl = document.getElementById('modalProtoStations');
      const ratEl = document.getElementById('modalProtoRatio');
      const cadEl = document.getElementById('modalProtoCadence');

      if (titleEl) titleEl.textContent = data.title;
      if (catEl) catEl.textContent = data.category;
      if (intEl) intEl.textContent = data.intensity;
      if (durEl) durEl.textContent = data.duration;
      if (calEl) calEl.textContent = data.caloricExpenditure;
      if (adpEl) adpEl.textContent = data.targetAdaptation;
      if (stnEl) stnEl.textContent = data.equipmentStations;
      if (ratEl) ratEl.textContent = data.coachRatio;
      if (cadEl) cadEl.textContent = data.recommendedCadence;

      if (window.openModal) {
        window.openModal('protocolModal', btn);
      }
    });
  });
}
