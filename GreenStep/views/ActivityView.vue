<script async setup>
import { ref } from 'vue'
import TopBar from '@/components/TopBar.vue'
import SideBar from '@/components/SideBar.vue'
import TransportModal from '@/components/modals/TransportModal.vue'
import MealModal from '@/components/modals/MealModal.vue'
import EnergyModal from '@/components/modals/EnergyModal.vue'
import RecycleModal from '@/components/modals/RecycleModal.vue'
import CreatePresetModal from '@/components/modals/CreatePresetModal.vue'

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

<template>
  <TopBar></TopBar>
  <SideBar></SideBar>
  <main class="activity-main">
    <div class="view-section active">
      <div class="card">
        <div class="activity-header">
          <div>
            <h2 class="card-title">📝 Event-Based Logger</h2>
            <p class="subtitle">Log activities as they happen for higher accuracy.</p>
          </div>
          <div class="total-today">
            <div class="total-value">6.1 kg CO₂</div>
            <div class="total-label">Total Today</div>
          </div>
        </div>

        <h3 class="section-label">1. Record New Event</h3>
        <div class="quick-add-grid">
          <button class="quick-add-btn" @click="openModal('transport')">
            <span class="icon">🚗</span>
            <span class="label">Transport</span>
          </button>

          <button class="quick-add-btn" @click="openModal('meal')">
            <span class="icon">🍔</span>
            <span class="label">Meal</span>
          </button>

          <button class="quick-add-btn" @click="openModal('energy')">
            <span class="icon">⚡</span>
            <span class="label">Energy</span>
          </button>

          <button class="quick-add-btn" @click="openModal('recycle')">
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

          <button class="add-new-preset-btn" @click="openModal('createPreset')">
            <span class="icon">➕</span> Create Custom Template
          </button>
        </div>
      </div>
    </div>

    <TransportModal v-if="activeModal === 'transport'" @close="activeModal = null" />
    <MealModal v-if="activeModal === 'meal'" @close="activeModal = null" />
    <EnergyModal v-if="activeModal === 'energy'" @close="activeModal = null" />
    <RecycleModal v-if="activeModal === 'recycle'" @close="activeModal = null" />
    <CreatePresetModal v-if="activeModal === 'createPreset'" @close="activeModal = null" @submit="addNewTemplate" />
  </main>
</template>

<style scoped>
/* Main Layout structure (mirrors DashboardView setup) */
.activity-main {
  padding: 2rem;
  min-height: 100vh;
  background-color: var(--bg-body);
  margin-left: 225px;
}

/* Card Styling */
.card {
  background-color: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 2.5rem;
  margin-bottom: 2.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  max-width: 800px; /* Optional: keeps it from stretching too wide on massive screens */
}

/* Header Elements */
.activity-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 2rem;
}

.card-title {
  font-size: 1.5rem;
  margin-top: 0;
  margin-bottom: 0.5rem;
  color: var(--text-main);
}

.subtitle {
  color: var(--text-muted);
  margin: 0;
}

.total-today {
  text-align: right;
}

.total-value {
  font-weight: 800;
  color: var(--primary);
  font-size: 1.6rem;
}

.total-label {
  color: var(--text-muted);
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
}

.section-label {
  font-size: 0.85rem;
  text-transform: uppercase;
  color: var(--text-muted);
  margin-bottom: 1rem;
  letter-spacing: 1px;
}

/* --- Quick Add Buttons Grid --- */
.quick-add-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 2.5rem;
}

.quick-add-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: var(--primary-light);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 1.5rem 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.quick-add-btn:hover {
  background-color: #e5e5c8; /* Slightly darker hover state */
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.quick-add-btn .icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.quick-add-btn .label {
  font-weight: 600;
  color: var(--text-main);
}

/* --- One-Tap Templates List --- */
.presets-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.preset-btn {
  display: flex;
  align-items: center;
  background-color: transparent;
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 1rem;
  width: 100%;
  cursor: pointer;
  transition:
    background-color 0.2s ease,
    border-color 0.2s;
}

.preset-btn:hover {
  background-color: rgba(245, 245, 220, 0.4); /* Faint primary-light overlay */
  border-color: var(--primary);
}

.preset-icon {
  font-size: 1.8rem;
  margin-right: 1.25rem;
}

.preset-details {
  text-align: left;
  flex-grow: 1;
}

.preset-title {
  font-weight: 700;
  color: var(--text-main);
  font-size: 1.05rem;
}

.preset-desc {
  font-size: 0.85rem;
  color: var(--text-muted);
  margin-top: 0.2rem;
}

.log-badge {
  color: var(--primary);
  font-weight: 700;
  padding: 0.5rem 1rem;
  background: var(--primary-light);
  border-radius: 6px;
  transition:
    background-color 0.2s,
    color 0.2s;
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
  padding: 1rem;
  width: 100%;
  cursor: pointer;
  color: var(--text-muted);
  font-weight: 600;
  transition: all 0.2s;
  margin-top: 0.5rem;
}

.add-new-preset-btn:hover {
  border-color: var(--primary);
  color: var(--primary);
  background-color: var(--primary-light);
}

/* Responsive adjustments for mobile */
@media (max-width: 768px) {
  .activity-main {
    padding: 1rem;
    padding-bottom: 90px; /* Accounts for bottom nav */
    margin-left: 0;
  }

  .quick-add-grid {
    grid-template-columns: repeat(2, 1fr); /* 2x2 grid on mobile */
  }

  .activity-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .total-today {
    text-align: left;
  }
}
</style>
