<script async setup>
  import { ref, onMounted, computed } from 'vue'
  import api from '@/api/client';
  import TopBar from '@/components/TopBar.vue'
  import SideBar from '@/components/SideBar.vue'
  import TransportModal from '@/components/modals/TransportModal.vue'
  import MealModal from '@/components/modals/MealModal.vue'
  import EnergyModal from '@/components/modals/EnergyModal.vue'
  import RecycleModal from '@/components/modals/RecycleModal.vue'
  import CreatePresetModal from '@/components/modals/CreatePresetModal.vue'
  import EventLoggerModal from '@/components/modals/EventLoggerModal.vue';

  const activeModal = ref(null)
  const categoryIcons = {
    transport: '🚗',
    meal: '🍽️',
    energy: '⚡',
    recycle: '♻️'
  }
  const activityTypes = ref([]);
  const mealTypes     = computed(() => activityTypes.value.filter(t => t.category === 'meal'));
  const transportTypes = computed(() => activityTypes.value.filter(t => t.category === 'transport'));
  const energyTypes   = computed(() => activityTypes.value.filter(t => t.category === 'energy'));
  const recycleTypes  = computed(() => activityTypes.value.filter(t => t.category === 'recycle'));
  const selectedLog = ref(null)
  const presets = ref([])
  const selectedPreset = ref(null)

  const recentLogs = ref([])
  const error = ref('')
  const loading = ref(false)
  const loadingType = ref(false)
  const loadingTemplate = ref(false)
  const q = ref('')

  async function deleteLog(id) {
    if (!confirm('Delete this log?')) return;
    try {
      await api.delete(`/api/activitylogs/${id}`);
      recentLogs.value = recentLogs.value.filter(log => log.id !== id);
    } catch (e) {
      error.value = e.response?.data?.error || e.message;
    }
  }

  function editLog(log) {
    selectedLog.value = log;
    activeModal.value = log.category;
  }

  async function load() {
    error.value = '';
    loading.value = true;
    try {
      const { data } = await api.get('/api/activitylogs', { params: { q: q.value || undefined } });
      recentLogs.value = data.data;
    } catch (e) {
      error.value = e.response?.data?.error || e.message;
    } finally {
      loading.value = false;
    }
  }

  async function loadActivityTypes() {

    loadingType.value = true 
    try {
      const { data } = await api.get('/api/activitytypes');
      activityTypes.value = data.data;
    } catch (e) {
      error.value = e.response?.data?.error || e.message;
    } finally {
      loadingType.value = false
    }
  }

  async function loadTemplates() {
    loadingTemplate.value = true
    try {
      const { data } = await api.get('/api/usertemplates');
      presets.value = data.data;
    } catch (e) {
      error.value = e.response?.data?.error || e.message;
    } finally {
      loadingTemplate.value = false
    }
  }

async function loadAllData() {
  await loadActivityTypes();
  await loadTemplates();
  await load();
}

const totalToday = computed(() => {
const today = new Date().toISOString().split('T')[0]; // "2026-06-28"
return recentLogs.value
  .filter(log => log.created_at.startsWith(today))
  .reduce((sum, log) => sum + parseFloat(log.co2_emission), 0)
  .toFixed(2);
});

const streakDays = computed(() => {
  if (recentLogs.value.length === 0) return 0

  // Get unique dates from logs, sorted descending
  const dates = [...new Set(
    recentLogs.value.map(log => log.created_at.split(' ')[0])
  )].sort((a, b) => b.localeCompare(a))

  const today = new Date().toISOString().split('T')[0]
  const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0]

  // Streak must start from today or yesterday
  if (dates[0] !== today && dates[0] !== yesterday) return 0

  let streak = 1
  for (let i = 1; i < dates.length; i++) {
    const prev = new Date(dates[i - 1])
    const curr = new Date(dates[i])
    const diff = (prev - curr) / 86400000

    if (diff === 1) {
      streak++
    } else {
      break
    }
  }

  return streak
})

onMounted(() => {
  loadAllData()
});
</script>

<template>
  <TopBar></TopBar>
  <SideBar></SideBar>

  <main class="activity-main">
    <div class="activity-content-grid">
       <div class="card log-column">
        <div class="activity-header">
          <div>
            <h2 class="card-title">🕒 Recent Activity</h2>
            <p class="subtitle">Your latest logged entries.</p>
            <div class="streak-badge">
            🔥 <span>{{ streakDays }}</span> day streak
            </div>
          </div>
         <div class="header-right">
           <div class="total-today">
              <div class="total-value">{{ totalToday }} kg CO₂</div>
              <div class="total-label">Total Today</div>
            </div>
            <button class="add-activity-btn" @click="activeModal = 'eventLogger'">
              <span> + </span>
            </button>
         </div>
        </div>

        <div class="log-list">
          <div v-if="loading" class="no-logs">
            Loading...
          </div>

          <div v-else>
            <div v-if="recentLogs.length === 0" class="no-logs">
              No recent logs yet.
            </div>

            <div v-else v-for="log in recentLogs" :key="log.id" class="log-item">
              <div class="log-icon-wrapper">
                <span class="log-icon">{{ categoryIcons[log.category] }}</span>
              </div>
              <div class="log-info">
                <div class="log-title">{{ log.title }}</div>
                <div class="log-description">
                  {{ log.activity_name }} • {{ parseFloat(log.amount).toFixed(2) }} {{ log.unit }}
                </div>
                <div class="log-time">{{ log.created_at }}</div>
              </div>
              <div class="log-co2">+{{ parseFloat(log.co2_emission).toFixed(2) }} kg</div>
              <div class="log-actions">
                <button class="action-btn edit-btn" @click="editLog(log)">✏️</button>
                <button class="action-btn delete-btn" @click="deleteLog(log.id)">🗑️</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <EventLoggerModal 
      v-if="activeModal === 'eventLogger'" 
      :presets="presets"
      :loadingTemplate="loadingTemplate"
      @configurePreset="selectedPreset = $event"
      @closeModal="activeModal = null"
      @openModal="activeModal = $event"
      @templateDeleted="loadTemplates"
      @logSubmitted="load"
    />
    <CreatePresetModal
      v-if="activeModal === 'createPreset'"
      :selectedPreset="selectedPreset"
      :activityTypes="activityTypes"
      @closeModal="activeModal = null"
      @templateSubmitted="loadTemplates"
      @templateUpdated="loadTemplates"
    />
    <TransportModal 
      v-if="activeModal === 'transport'" 
      :editLog="selectedLog"
      :options="transportTypes"
      @closeModal="activeModal = null; selectedLog = null" 
      @logSubmitted="load"
    />
    <MealModal 
      v-if="activeModal === 'meal'" 
      :editLog="selectedLog"
      :options="mealTypes"
      @closeModal="activeModal = null; selectedLog = null" 
      @logSubmitted="load"
    />
    <EnergyModal 
      v-if="activeModal === 'energy'" 
      :editLog="selectedLog"
      :options="energyTypes"
      @closeModal="activeModal = null; selectedLog = null" 
      @logSubmitted="load"
    />
    <RecycleModal 
      v-if="activeModal === 'recycle'" 
      :editLog="selectedLog"
      :options="recycleTypes"
      @closeModal="activeModal = null; selectedLog = null" 
      @logSubmitted="load"
    />
  </main>
</template>

<style scoped>
.activity-main {
  padding: 1rem;
  padding-bottom: 90px;
  min-height: 100vh;
  background-color: var(--bg-body);
}

.activity-content-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
  align-items: start;
}

.card {
  background-color: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 1rem 0.85rem; 
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.activity-header {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.card-title {
  font-size: 1.2rem;
  margin-top: 0;
  margin-bottom: 0.3rem;
  color: var(--text-main);
}

.subtitle {
  color: var(--text-muted);
  margin: 0;
  font-size: 0.85rem;
}

.header-right {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  width: 100%;
}

.total-today {
  text-align: left;
}

.total-value {
  font-weight: 800;
  color: var(--primary);
  font-size: 1.1rem;
  white-space: nowrap;
}

.total-label {
  color: var(--text-muted);
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  white-space: nowrap;
}

.add-activity-btn {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  background-color: var(--primary);
  color: var(--primary-light);
  border: 1px solid var(--primary);
  border-radius: 8px;
  padding: 0.5rem 0.9rem;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  flex-shrink: 0;
}

.add-activity-btn:hover {
  background-color: var(--primary-light);
  color: var(--primary);
}

.section-label {
  font-size: 0.8rem;
  text-transform: uppercase;
  color: var(--text-muted);
  margin-bottom: 0.75rem;
  letter-spacing: 1px;
}

.log-list {
  display: flex;
  flex-direction: column;
  max-height: 60vh;
  overflow-y: auto;
  overflow-x: hidden;
}

.no-logs {
  text-align: center;
  color: var(--text-muted);
  padding: 2rem 0;
  font-size: 0.95rem;
}

.log-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid var(--border);
  gap: 0.4rem;
  min-width: 0;
  overflow: hidden;
}

.log-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.log-icon-wrapper {
  background-color: var(--bg-body);
  width: 38px;
  height: 38px;
  min-width: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--border);
  flex-shrink: 0;
}

.log-icon {
  font-size: 1.1rem;
}

.log-info {
  flex-grow: 1;
  min-width: 0;
}

.log-title {
  font-weight: 700;
  color: var(--text-main);
  margin-bottom: 0.15rem;
  font-size: 0.9rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.log-description {
  font-size: 0.75rem;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.log-time {
  font-size: 0.7rem;
  color: var(--text-muted);
  margin-top: 0.15rem;
}

.log-co2 {
  font-weight: 800;
  color: #d9534f;
  font-size: 0.8rem;
  white-space: nowrap;
  flex-shrink: 0;
}

.log-actions {
  display: flex;
  gap: 0.2rem;
  flex-shrink: 0;
}

.action-btn {
  border: none;
  border-radius: 6px;
  padding: 0.25rem 0.4rem;
  cursor: pointer;
  font-size: 0.75rem;
  font-weight: 600;
  transition: all 0.2s;
}

.edit-btn {
  background-color: var(--primary-light);
  color: var(--primary);
  border: 1px solid var(--primary);
}

.edit-btn:hover {
  background-color: var(--primary);
  color: var(--primary-light);
}

.delete-btn {
  background-color: #fde8e8;
  color: #d9534f;
  border: 1px solid #d9534f;
}

.delete-btn:hover {
  background-color: #d9534f;
  color: #fff;
}

.streak-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  margin-top: 0.4rem;
  background-color: #fff4e5;
  border: 1px solid #f5a623;
  color: #b85c00;
  border-radius: 999px;
  padding: 0.2rem 0.7rem;
  font-size: 0.78rem;
  font-weight: 700;
}

.streak-badge span {
  color: #f5a623;
  font-size: 0.9rem;
}

/* Small phones < 480px */
@media (max-width: 479px) {
  .card {
    padding: 0.85rem 0.75rem;
    border-radius: 10px;
  }

  .log-icon-wrapper {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border-radius: 8px;
  }

  .log-icon {
    font-size: 0.95rem;
  }

  .log-info {
    flex: 1 1 0;
    min-width: 0;
    width: 0;
  }

  .log-title {
    font-size: 0.82rem;
  }

  .log-description {
    font-size: 0.7rem;
  }

  .log-time {
    font-size: 0.65rem;
  }

  .log-co2 {
    font-size: 0.75rem;
  }

  .action-btn {
    padding: 0.5rem 0.5rem;
    font-size: 0.7rem;
  }

  .log-item {
    gap: 0.3rem;
  }

  .log-actions {
    gap: 0.15rem;
  }

  .card-title {
    font-size: 1rem;
  }

  .total-value {
    font-size: 1rem;
  }

  .total-label {
    font-size: 0.65rem;
  }

  .add-activity-btn {
    padding: 0.4rem 0.7rem;
    font-size: 1rem;
  }
}

/* Tablet */
@media (min-width: 480px) {
  .activity-header {
    flex-direction: row;
    justify-content: space-between;
    align-items: flex-start;
  }

  .header-right {
    width: auto;
    justify-content: flex-end;
  }

  .total-today {
    text-align: right;
  }
}

@media (min-width: 640px) {
  .activity-main {
    padding: 1.5rem;
    padding-bottom: 2rem;
    margin-left: 0;
  }

  .card {
    padding: 1.75rem;
  }

  .card-title {
    font-size: 1.35rem;
  }

  .total-value {
    font-size: 1.4rem;
  }
}

/* Desktop */
@media (min-width: 769px) {
  .activity-main {
    padding: 2rem;
    margin-left: 225px;
  }

  .card {
    padding: 2.5rem;
  }

  .card-title {
    font-size: 1.5rem;
  }

  .total-value {
    font-size: 1.6rem;
  }

  .log-icon-wrapper {
    width: 45px;
    height: 45px;
    min-width: 45px;
  }

  .log-icon {
    font-size: 1.4rem;
  }

  .log-title {
    font-size: 1rem;
  }

  .log-co2 {
    font-size: 1.1rem;
  }

  .log-list {
    max-height: 70vh;
  }
}
</style>