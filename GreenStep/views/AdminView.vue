<script setup>
import { ref } from 'vue'
import TopBar from '@/components/TopBar.vue'
import SideBar from '@/components/SideBar.vue'

// ── State Management ─────────────────────────────────────────
const activeTab = ref('challenges')
const isSubmitting = ref(false)
const statusMessage = ref({ type: '', text: '' })

// ── Form Models ──────────────────────────────────────────────
const challengeForm = ref({
  title: '',
  description: '',
  targetGoal: '',
  unit: 'kWh'
})

const badgeForm = ref({
  name: '',
  description: '',
  requirementType: 'activity_count',
  requirementValue: ''
})

const activityForm = ref({
  name: '',
  category: 'energy',
  carbonMultiplier: ''
})

// ── Form Submit Handlers ─────────────────────────────────────
const setStatus = (type, text) => {
  statusMessage.value = { type, text }
  setTimeout(() => { statusMessage.value = { type: '', text: '' } }, 4000)
}

async function handleCreateBadge() {
  isSubmitting.value = true
  try {
    // API client execution goes here: await api.post('/api/admin/badges', badgeForm.value)
    console.log('Creating Badge:', badgeForm.value)
    setStatus('success', '🏅 New system achievement badge created successfully!')
    badgeForm.value = { name: '', description: '', requirementType: 'activity_count', requirementValue: '' }
  } catch (err) {
    setStatus('error', err.response?.data?.error ?? 'Failed to create badge.')
  } finally {
    isSubmitting.value = false
  }
}

async function handleCreateActivity() {
  isSubmitting.value = true
  try {
    // API client execution goes here: await api.post('/api/admin/activities', activityForm.value)
    console.log('Creating Activity Type:', activityForm.value)
    setStatus('success', '🌱 New loggable system activity metrics created!')
    activityForm.value = { name: '', category: 'energy', carbonMultiplier: '' }
  } catch (err) {
    setStatus('error', err.response?.data?.error ?? 'Failed to create activity.')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <TopBar />
  <SideBar />

  <main class="admin-main">
    <div class="view-section active">
      <div class="card main-admin-card">

        <div class="admin-header">
          <div class="admin-avatar">🛠️</div>
          <div class="admin-info">
            <h1>System Configuration Desk</h1>
            <p>Role-privileged workspace for provisioning updates and defining operational baselines.</p>
          </div>
        </div>

        <div class="tab-navigation">
          <button
            class="tab-btn"
            :class="{ active: activeTab === 'badges' }"
            @click="activeTab = 'badges'"
          >
            🏅 Manage Badges
          </button>
          <button
            class="tab-btn"
            :class="{ active: activeTab === 'activities' }"
            @click="activeTab = 'activities'"
          >
            🌱 Manage Tracked Activities
          </button>
        </div>

        <hr style="border: 1px solid var(--border); margin: 0 0 2.5rem 0;">

        <div v-if="statusMessage.text" class="admin-alert" :class="statusMessage.type">
          {{ statusMessage.type === 'success' ? '✅' : '⚠️' }} {{ statusMessage.text }}
        </div>

        <div class="admin-panel-grid">

          <div v-if="activeTab === 'badges'" class="badge-management-layout">

            <div class="card inner-list-card">
              <h2 class="card-title">Active System Badges</h2>

              <div v-if="badgesList.length === 0" class="empty-state">
                No badges configured yet. Use the control panel to register one.
              </div>

              <div v-else class="badge-table-container">
                <table class="badge-table">
                  <thead>
                    <tr>
                      <th>Badge Name</th>
                      <th>Requirement Condition</th>
                      <th>Target</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="badge in badgesList" :key="badge.id">
                      <td>
                        <div class="badge-identity">
                          <span class="badge-icon">🏅</span>
                          <div>
                            <strong>{{ badge.name }}</strong>
                            <p class="badge-table-desc">{{ badge.description }}</p>
                          </div>
                        </div>
                      </td>
                      <td><span class="badge-tag">{{ getRequirementLabel(badge.requirementType) }}</span></td>
                      <td><strong style="color: var(--primary);">{{ badge.requirementValue }}</strong></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <form @submit.prevent="handleCreateBadge" class="card dashed-card side-form-card">
              <h2 class="card-title">Provision New Asset</h2>

              <div class="input-group">
                <label>Badge Display Name</label>
                <input v-model="badgeForm.name" class="admin-input" type="text" required placeholder="e.g., Solar Pioneer Elite" />
              </div>

              <div class="input-group">
                <label>Requirements Summary</label>
                <textarea v-model="badgeForm.description" class="admin-input field-textarea" required placeholder="Describe target milestone conditions..."></textarea>
              </div>

              <div class="input-group">
                <label>Trigger Condition</label>
                <select v-model="badgeForm.requirementType" class="admin-input">
                  <option value="activity_count">Total System Submissions</option>
                  <option value="carbon_saved">Aggregated Carbon Restitution (kg)</option>
                  <option value="streak_days">Consecutive Daily Active Tracking</option>
                </select>
              </div>

              <div class="input-group">
                <label>Milestone Target</label>
                <input v-model.number="badgeForm.requirementValue" class="admin-input" type="number" min="1" required placeholder="Value (e.g., 25)" />
              </div>

              <button type="submit" class="submit-btn" :disabled="isSubmitting">
                {{ isSubmitting ? 'Registering Asset...' : 'Register Global Badge' }}
              </button>
            </form>
          </div>


          <form v-if="activeTab === 'activities'" @submit.prevent="handleCreateActivity" class="card dashed-card">
            <h2 class="card-title">Configure Loggable Activity Class</h2>

            <div class="input-group">
              <label>Action Entry Metric Label</label>
              <input v-model="activityForm.name" class="admin-input" type="text" required placeholder="e.g., Intercampus Electric Shuttle Commute" />
            </div>

            <div class="form-row-split">
              <div class="input-group">
                <label>Impact Domain Field Group</label>
                <select v-model="activityForm.category" class="admin-input">
                  <option value="energy">Power & Electric Energy</option>
                  <option value="transport">Transit & Smart Commuting</option>
                  <option value="diet">Sustainably Managed Dietary Selections</option>
                  <option value="waste">Recycling / Resource Reconstitution</option>
                </select>
              </div>
              <div class="input-group">
                <label>Carbon Reduction Weighting Matrix</label>
                <input v-model.number="activityForm.carbonMultiplier" class="admin-input" type="number" step="0.001" min="0" required placeholder="Reduction factor (kg CO2e saved per unit)" />
              </div>
            </div>

            <button type="submit" class="submit-btn" :disabled="isSubmitting">
              {{ isSubmitting ? 'Injecting Entry Rules...' : 'Append Loggable Class to User Actions' }}
            </button>
          </form>

          <div class="card info-reference-card">
            <h3 class="info-title">System Action Control Matrix</h3>
            <p class="info-body">Modifications dispatched through this workspace directly apply schema alterations to standard user tracking views.</p>

            <div class="notice-stack">
              <div class="notice-item">
                <span class="notice-dot"></span>
                <p><strong>System Synchronization:</strong> Live challenges propagate directly into the active tracking layouts across all user accounts.</p>
              </div>
              <div class="notice-item">
                <span class="notice-dot"></span>
                <p><strong>Database Integrity:</strong> Activity factors calculate metrics downstream. Modify operational numbers only under structural policy alignment.</p>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </main>
</template>

<style scoped>
/* --- Main Structural Foundations --- */
.admin-main {
  padding: 2rem;
  min-height: 100vh;
  background-color: var(--bg-body);
  margin-left: 225px;
}

.card {
  background-color: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.main-admin-card {
  padding: 2.5rem;
  max-width: 1000px;
}

/* --- Panel Layout System --- */
.admin-panel-grid {
  display: grid;
  grid-template-columns: 1.35fr 0.65fr;
  gap: 2rem;
}

.dashed-card {
  box-shadow: none;
  border-style: dashed;
  border-width: 2px;
  padding: 2rem;
}

/* --- Workspace Header Display --- */
.admin-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.admin-avatar {
  font-size: 3.5rem;
  background-color: var(--primary-light);
  width: 90px;
  height: 90px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 50%;
  border: 3px solid var(--primary);
}

.admin-info h1 {
  margin: 0 0 0.25rem 0;
  font-size: 2rem;
  color: var(--text-main);
}

.admin-info p {
  margin: 0;
  color: var(--text-muted);
  font-size: 1.05rem;
  font-weight: 500;
}

/* --- Module Selector Navigation --- */
.tab-navigation {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.tab-btn {
  background: transparent;
  border: none;
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--text-muted);
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.tab-btn.active {
  background-color: var(--primary-light);
  color: var(--primary);
  border: 1px solid var(--border);
}

/* --- Inputs & Form Layout Controls --- */
.input-group {
  margin-bottom: 1.5rem;
}

.input-group label {
  display: block;
  font-size: 0.85rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.admin-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border);
  border-radius: 8px;
  background-color: var(--bg-body);
  color: var(--text-main);
  font-size: 1rem;
  font-family: inherit;
  box-sizing: border-box;
  outline: none;
  transition: border-color 0.2s;
}

.admin-input:focus {
  border-color: var(--primary);
}

.field-textarea {
  min-height: 100px;
  resize: vertical;
}

.form-row-split {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.card-title {
  font-size: 1.35rem;
  margin-top: 0;
  margin-bottom: 1.5rem;
  color: var(--text-main);
}

/* --- Core Buttons --- */
.submit-btn {
  background-color: var(--primary);
  border: none;
  color: white;
  padding: 0.85rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  width: 100%;
  transition: background-color 0.2s;
}

.submit-btn:hover {
  background-color: #465926;
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* --- Informational Context Card Panel --- */
.info-reference-card {
  padding: 1.5rem;
  background-color: var(--primary-light);
  border-color: var(--border);
  height: fit-content;
  box-shadow: none;
}

.info-title {
  margin-top: 0;
  font-size: 1.1rem;
  color: var(--primary);
}

.info-body {
  font-size: 0.9rem;
  color: var(--text-main);
  line-height: 1.4;
}

.notice-stack {
  margin-top: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.notice-item {
  display: flex;
  gap: 0.5rem;
  align-items: flex-start;
}

.notice-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: var(--primary);
  margin-top: 0.4rem;
  flex-shrink: 0;
}

.notice-item p {
  margin: 0;
  font-size: 0.85rem;
  color: var(--text-muted);
  line-height: 1.4;
}

/* --- Status Alert Styling --- */
.admin-alert {
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-weight: 600;
  border: 1px solid transparent;
}

.admin-alert.success {
  background-color: #e6f4ea;
  border-color: #b7e1cd;
  color: #137333;
}

.admin-alert.error {
  background-color: #fce8e6;
  border-color: #fad2cf;
  color: #c5221f;
}

/* --- Responsive Layout Breaks --- */
@media (max-width: 1024px) {
  .admin-panel-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .admin-main {
    margin-left: 0;
    padding: 1rem;
    padding-bottom: 90px;
  }
  .main-admin-card {
    padding: 1.5rem;
  }
  .tab-navigation {
    flex-direction: column;
    gap: 0.5rem;
  }
  .form-row-split {
    grid-template-columns: 1fr;
  }
}
</style>
