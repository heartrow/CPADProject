<template>
  <div class="modal-overlay" @click.self="$emit('closeModal')">
    <div class="modal-card">
      <div class="modal-header">
        <div>
          <h2 class="card-title">📝 Log an Activity</h2>
          <p class="subtitle">Log activities as they happen for higher accuracy.</p>
        </div>
        <button class="close-btn" @click="$emit('closeModal')">✕</button>
      </div>

      <h3 class="section-label">1. Record New Event</h3>
      <div class="quick-add-grid">
        <button class="quick-add-btn" @click="$emit('openModal', 'transport')">
          <span class="icon">🚗</span>
          <span class="label">Transport</span>
        </button>
        <button class="quick-add-btn" @click="$emit('openModal', 'meal')">
          <span class="icon">🍔</span>
          <span class="label">Meal</span>
        </button>
        <button class="quick-add-btn" @click="$emit('openModal', 'energy')">
          <span class="icon">⚡</span>
          <span class="label">Energy</span>
        </button>
        <button class="quick-add-btn" @click="$emit('openModal', 'recycle')">
          <span class="icon">♻️</span>
          <span class="label">Recycle</span>
        </button>
      </div>

      <h3 class="section-label">2. One-Tap Templates</h3>
      <div class="presets-list">
        <button
          v-for="preset in presets"
          :key="preset.id"
          class="preset-btn"
          @click="logPreset(preset.title)"
        >
          <span class="preset-icon">{{ preset.icon }}</span>
          <div class="preset-details">
            <div class="preset-title">{{ preset.title }}</div>
            <div class="preset-desc">{{ preset.desc }} • Est {{ preset.co2 }} kg CO₂</div>
          </div>
          <span class="log-badge">+ LOG</span>
        </button>

        <button class="add-new-preset-btn" @click="$emit('openModal', 'createPreset')">
          <span class="icon">➕</span> Create Custom Template
        </button>
      </div>
    </div>
  </div>
</template>

<script async setup>
  import { ref } from 'vue'

  const activeModal = ref(null)

  const presets = ref([
    {
      id: 1,
      icon: '🚗',
      title: 'Commute to Johor Campus',
      desc: '2025 Proton Saga (Petrol) • 15km',
      co2: 2.4,
    },
    {
      id: 2,
      icon: '⚡',
      title: 'Coding Session',
      desc: 'SFF Custom PC & Monitor • 4 Hours',
      co2: 0.8,
    },
    {
      id: 3,
      icon: '🍔',
      title: 'Standard Lunch',
      desc: 'Mixed Diet (Chicken/Fish)',
      co2: 1.2,
    },
  ])

  const openModal = (type) => {
    activeModal.value = type
  }

  const addNewTemplate = (newTemplateData) => {
    presets.value.push(newTemplateData)
  }

  const logPreset = (activityName) => {
    console.log(`Logged preset: ${activityName}`)
  }
</script>

<style scoped>
  .modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(2px);
  }

  .modal-card {
    background-color: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 2rem;
    width: 90%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
  }

  .card-title {
    font-size: 1.4rem;
    margin: 0 0 0.3rem 0;
    color: var(--text-main);
  }

  .subtitle {
    color: var(--text-muted);
    margin: 0;
    font-size: 0.9rem;
  }

  .close-btn {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: var(--text-muted);
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    transition: all 0.2s;
  }

  .close-btn:hover {
    background-color: var(--bg-body);
    color: var(--text-main);
  }

  .section-label {
    font-size: 0.8rem;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 1rem;
    letter-spacing: 1px;
  }

  .quick-add-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
    margin-bottom: 2rem;
  }

  .quick-add-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: var(--primary-light);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 1.2rem 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .quick-add-btn:hover {
    background-color: #e5e5c8;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .quick-add-btn .icon {
    font-size: 1.8rem;
    margin-bottom: 0.4rem;
  }

  .quick-add-btn .label {
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--text-main);
  }

  .presets-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
  }

  .preset-btn {
    display: flex;
    align-items: center;
    background-color: transparent;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 0.85rem 1rem;
    width: 100%;
    cursor: pointer;
    transition: background-color 0.2s ease, border-color 0.2s;
  }

  .preset-btn:hover {
    background-color: rgba(245, 245, 220, 0.4);
    border-color: var(--primary);
  }

  .preset-icon {
    font-size: 1.6rem;
    margin-right: 1rem;
  }

  .preset-details {
    text-align: left;
    flex-grow: 1;
  }

  .preset-title {
    font-weight: 700;
    color: var(--text-main);
    font-size: 0.95rem;
  }

  .preset-desc {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 0.15rem;
  }

  .log-badge {
    color: var(--primary);
    font-weight: 700;
    padding: 0.4rem 0.8rem;
    background: var(--primary-light);
    border-radius: 6px;
    font-size: 0.85rem;
    transition: background-color 0.2s, color 0.2s;
  }

  .preset-btn:hover .log-badge {
    background-color: var(--primary);
    color: var(--primary-light);
  }

  .add-new-preset-btn {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    background-color: transparent;
    border: 2px dashed var(--border);
    border-radius: 10px;
    padding: 0.85rem;
    width: 100%;
    cursor: pointer;
    color: var(--text-muted);
    font-weight: 600;
    transition: all 0.2s;
    margin-top: 0.25rem;
  }

  .add-new-preset-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
    background-color: var(--primary-light);
  }

  @media (max-width: 768px) {
    .quick-add-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }
</style>