<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import TopBar from '@/components/TopBar.vue'
import SideBar from '@/components/SideBar.vue'
import api from '@/api/client'

// ── State Management ─────────────────────────────────────────
const activeTab = ref('challenges')
const isSubmitting = ref(false)
const statusMessage = ref({ type: '', text: '' })
const badgesList = ref([])
const activityTypesList = ref([])

// ── Form Models ──────────────────────────────────────────────
const challengeForm = ref({
  title: '',
  description: '',
  targetGoal: '',
  unit: 'kWh'
})

const badgeForm = ref({
  name: '',
  requirementType: 'activity_count',
  requirementValue: '',
  category: 'meal', // only used by the streak_days criteria type
  activityTypeId: '' // which specific activity type the streak must be logged against
})

// Activity types belonging to the currently selected streak category —
// these populate the "Activity Type" dropdown so admins pick a real,
// existing activity_type_id instead of one being silently defaulted.
const filteredActivityTypes = computed(() =>
  activityTypesList.value.filter(t => t.category === badgeForm.value.category)
)

// Mirrors BadgeController::buildDescription() on the backend EXACTLY — including
// the ucfirst() on category — so the preview never drifts from the saved badge.
const badgeDescriptionPreview = computed(() => {
  const value = badgeForm.value.requirementValue || '___'
  const category = badgeForm.value.category || 'meal'
  const categoryCapitalized = category.charAt(0).toUpperCase() + category.slice(1)
  switch (badgeForm.value.requirementType) {
    case 'activity_count':
      return `Log ${value} eco-activity.`
    case 'carbon_saved':
      return `Save a total of ${value} kg of CO₂ through your activities.`
    case 'streak_days':
      return `Log ${categoryCapitalized} activities for ${value} days in a row.`
    default:
      return 'Complete the required eco-actions to unlock this badge.'
  }
})

const selectedActivity = ref(null)
const activityForm = ref({
  name: '',
  category: 'energy',
  unit: '',
  co2_per_unit: ''
})

// Picking a different streak category invalidates whatever activity type
// was previously selected (it belongs to the old category), so clear it.
watch(() => badgeForm.value.category, () => {
  badgeForm.value.activityTypeId = ''
})

// ── Form Submit Handlers ─────────────────────────────────────
const setStatus = (type, text) => {
  statusMessage.value = { type, text }
  setTimeout(() => { statusMessage.value = { type: '', text: '' } }, 4000)
}

// Maps the form's requirementType to the criteria_type the backend understands,
// and packs the right fields into the request body for that type.
function buildBadgePayload(form) {
  switch (form.requirementType) {
    case 'activity_count':
      return { name: form.name, criteria_type: 'total_logs', threshold: form.requirementValue }
    case 'carbon_saved':
      return { name: form.name, criteria_type: 'total_co2_saved_kg', threshold: form.requirementValue }
    case 'streak_days':
      return { name: form.name, criteria_type: 'activity_category_streak', days: form.requirementValue, category: form.category, activity_type_id: form.activityTypeId }
    default:
      return { name: form.name, criteria_type: form.requirementType, threshold: form.requirementValue }
  }
}

async function fetchBadges() {
  try {
    const res = await api.get('/api/badges')
    badgesList.value = res.data.data
  } catch (err) {
    setStatus('error', err.response?.data?.error ?? 'Failed to load badges.')
  }
}

async function fetchActivityTypes() {
  try {
    const res = await api.get('/api/activitytypes')
    activityTypesList.value = res.data.data
  } catch (err) {
    setStatus('error', err.response?.data?.error ?? 'Failed to load activity types.')
  }
}

async function handleCreateBadge() {
  isSubmitting.value = true
  try {
    await api.post('/api/badges', buildBadgePayload(badgeForm.value))
    setStatus('success', '🏅 New system achievement badge created successfully!')
    badgeForm.value = { name: '', requirementType: 'activity_count', requirementValue: '', category: 'meal', activityTypeId: '' }
    await fetchBadges() // refresh the table so the new badge shows up immediately
  } catch (err) {
    setStatus('error', err.response?.data?.errors ? JSON.stringify(err.response.data.errors) : (err.response?.data?.error ?? 'Failed to create badge.'))
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  fetchBadges()
  fetchActivityTypes()
})

function editActivity(type) {
  selectedActivity.value = type;
  activityForm.value = {
    name: type.name,
    category: type.category,
    unit: type.unit,
    co2_per_unit: type.co2_per_unit
  }
}

function cancelEditActivity() {
  selectedActivity.value = null;
  activityForm.value = { name: '', category: 'energy', unit: '', co2_per_unit: '' }
}

async function handleCreateActivity() {
  isSubmitting.value = true
  try {
    const payload = {
      name: activityForm.value.name,
      category: activityForm.value.category,
      unit: activityForm.value.unit,
      co2_per_unit: Number(activityForm.value.co2_per_unit)
    }

    console.log(payload)
    if (selectedActivity.value) {
      await api.put(`/api/activitytypes/${selectedActivity.value.id}`, payload)
      setStatus('success', '✅ Activity type updated successfully!')
    } else {
      await api.post('/api/activitytypes', payload)
      setStatus('success', '🌱 New activity type created!')
    }

    cancelEditActivity()
    await fetchActivityTypes()
  } catch (err) {
    setStatus('error', err.response?.data?.errors
      ? Object.values(err.response.data.errors).join(' • ')
      : (err.response?.data?.error ?? 'Failed to save activity type.'))
  } finally {
    isSubmitting.value = false
  }
}

async function handleDeleteActivity(id) {
  if (!confirm('Delete this activity type?')) return;
  try {
    await api.delete(`/api/activitytypes/${id}`)
    setStatus('success', '🗑️ Activity type deleted.')
    await fetchActivityTypes()
  } catch (err) {
    setStatus('error', err.response?.data?.error ?? 'Failed to delete activity type.')
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
                    <tr v-for="badge in badgesList" :key="badge.badge_id">
                      <td data-label="Badge">
                        <div class="badge-identity">
                          <span class="badge-icon">🏅</span>
                          <div>
                            <strong>{{ badge.name }}</strong>
                            <p class="badge-table-desc">{{ badge.description }}</p>
                          </div>
                        </div>
                      </td>
                      <td data-label="Status"><span class="badge-tag">{{ badge.unlocked ? 'Unlocked' : 'Locked' }}</span></td>
                      <td data-label="Earned"><strong style="color: var(--primary);">{{ badge.earned_at ?? '—' }}</strong></td>
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
                <label>Generated Description Preview</label>
                <p class="generated-description-preview">{{ badgeDescriptionPreview }}</p>
              </div>

              <div class="input-group">
                <label>Trigger Condition</label>
                <select v-model="badgeForm.requirementType" class="admin-input">
                  <option value="activity_count">Total System Submissions</option>
                  <option value="carbon_saved">Aggregated Carbon Restitution (kg)</option>
                  <option value="streak_days">Consecutive Daily Active Tracking</option>
                </select>
              </div>

              <div class="input-group" v-if="badgeForm.requirementType === 'streak_days'">
                <label>Activity Category</label>
                <select v-model="badgeForm.category" class="admin-input">
                  <option value="meal">Meal</option>
                  <option value="transport">Transport</option>
                  <option value="energy">Energy</option>
                  <option value="recycle">Recycling</option>
                </select>
              </div>

              <div class="input-group" v-if="badgeForm.requirementType === 'streak_days'">
                <label>Activity Type</label>
                <select v-model="badgeForm.activityTypeId" class="admin-input" required>
                  <option value="" disabled>Select an activity type…</option>
                  <option v-for="type in filteredActivityTypes" :key="type.id" :value="type.id">
                    {{ type.name }}
                  </option>
                </select>
                <p v-if="filteredActivityTypes.length === 0" class="empty-state" style="margin-top: 0.5rem; padding: 0.75rem;">
                  No activity types exist for this category yet — create one under "Manage Tracked Activities" first.
                </p>
              </div>

              <div class="input-group">
                <label>Milestone Target</label>
                <input v-model.number="badgeForm.requirementValue" class="admin-input" type="number" min="1" required placeholder="Value (e.g., 25)" />
              </div>

              <button
                type="submit"
                class="submit-btn"
                :disabled="isSubmitting || (badgeForm.requirementType === 'streak_days' && !badgeForm.activityTypeId)"
              >
                {{ isSubmitting ? 'Registering Asset...' : 'Register Global Badge' }}
              </button>
            </form>
          </div>

          <div v-if="activeTab === 'activities'" class="badge-management-layout">

            <!-- List -->
            <div class="card inner-list-card">
              <h2 class="card-title">🌱 Activity Types</h2>

              <div v-if="activityTypesList.length === 0" class="empty-state">
                No activity types configured yet.
              </div>

              <div v-else class="badge-table-container">
                <table class="badge-table">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Category</th>
                      <th>Unit</th>
                      <th>CO₂/Unit (kg)</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="type in activityTypesList" :key="type.id">
                      <td data-label="Name">
                        <div class="badge-identity">
                          <span class="badge-icon">
                            {{ { transport: '🚗', meal: '🍽️', energy: '⚡', recycle: '♻️' }[type.category] }}
                          </span>
                          <strong>{{ type.name }}</strong>
                        </div>
                      </td>
                      <td data-label="Category">
                        <span class="badge-tag">{{ type.category }}</span>
                      </td>
                      <td data-label="Unit">{{ type.unit }}</td>
                      <td data-label="CO₂/Unit">
                        <strong style="color: var(--primary);">{{ type.co2_per_unit }} kg</strong>
                      </td>
                      <td data-label="Actions">
                        <div class="row-actions">
                          <button class="row-btn edit" @click="editActivity(type)">✏️ Edit</button>
                          <button class="row-btn delete" @click="handleDeleteActivity(type.id)">🗑️</button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Create / Edit Form -->
            <form @submit.prevent="handleCreateActivity" class="card dashed-card side-form-card">
              <h2 class="card-title">{{ selectedActivity ? '✏️ Edit Activity Type' : '➕ New Activity Type' }}</h2>

              <div class="input-group">
                <label>Name</label>
                <input v-model="activityForm.name" class="admin-input" type="text" required placeholder="e.g., Private Car (Petrol)" />
              </div>

              <div class="input-group">
                <label>Category</label>
                <select v-model="activityForm.category" class="admin-input">
                  <option value="transport">🚗 Transport</option>
                  <option value="meal">🍔 Meal</option>
                  <option value="energy">⚡ Energy</option>
                  <option value="recycle">♻️ Recycle</option>
                </select>
              </div>

              <div class="input-group">
                <label>Unit</label>
                <input v-model="activityForm.unit" class="admin-input" type="text" required placeholder="e.g., km, kg, hour" />
              </div>

              <div class="input-group">
                <label>CO₂ per Unit (kg)</label>
                <input v-model.number="activityForm.co2_per_unit" class="admin-input" type="number" step="0.0001" min="0" required placeholder="e.g., 0.2100" />
              </div>

              <div class="form-row-split">
                <button type="submit" class="submit-btn" :disabled="isSubmitting">
                  {{ isSubmitting ? 'Saving...' : selectedActivity ? 'Update' : 'Create' }}
                </button>
                <button v-if="selectedActivity" type="button" class="cancel-btn" @click="cancelEditActivity">
                  Cancel
                </button>
              </div>
            </form>

          </div>

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

/* ── Mobile: no sidebar offset ── */
@media (max-width: 768px) {
  .admin-main {
    margin-left: 0;
    padding: 1rem;
  }
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

@media (max-width: 768px) {
  .main-admin-card {
    padding: 1.25rem;
  }
}

/* --- Panel Layout System --- */
.admin-panel-grid {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

@media (max-width: 900px) {
  .admin-panel-grid {
    grid-template-columns: 1fr;
  }
}

.dashed-card {
  box-shadow: none;
  border-style: dashed;
  border-width: 2px;
  padding: 2rem;
}

@media (max-width: 768px) {
  .dashed-card {
    padding: 1.25rem;
  }
}

/* --- Workspace Header Display --- */
.admin-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

@media (max-width: 480px) {
  .admin-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
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
  flex-shrink: 0;
}

@media (max-width: 480px) {
  .admin-avatar {
    width: 60px;
    height: 60px;
    font-size: 2rem;
  }
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

@media (max-width: 480px) {
  .admin-info h1 {
    font-size: 1.35rem;
  }

  .admin-info p {
    font-size: 0.9rem;
  }
}

/* --- Module Selector Navigation --- */
.tab-navigation {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
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

@media (max-width: 480px) {
  .tab-btn {
    font-size: 0.9rem;
    padding: 0.6rem 1rem;
    flex: 1;
    text-align: center;
  }
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

.generated-description-preview {
  margin: 0;
  padding: 0.75rem;
  border: 1px dashed var(--border);
  border-radius: 8px;
  background-color: var(--bg-body);
  color: var(--text-muted);
  font-size: 0.9rem;
  font-style: italic;
  line-height: 1.4;
}

.form-row-split {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

@media (max-width: 480px) {
  .form-row-split {
    grid-template-columns: 1fr;
  }
}

.card-title {
  font-size: 1.35rem;
  margin-top: 0;
  margin-bottom: 1.5rem;
  color: var(--text-main);
}

@media (max-width: 480px) {
  .card-title {
    font-size: 1.1rem;
  }
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
  width: 100%;
  box-sizing: border-box;
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

/* --- Badge Management Table --- */
.badge-management-layout {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 2rem;
  align-items: start;
}

@media (max-width: 900px) {
  .badge-management-layout {
    grid-template-columns: 1fr;
  }
}

.inner-list-card {
  padding: 2rem;
}

@media (max-width: 768px) {
  .inner-list-card {
    padding: 1.25rem;
  }
}

.empty-state {
  padding: 2rem 1rem;
  text-align: center;
  color: var(--text-muted);
  font-size: 0.95rem;
  border: 1px dashed var(--border);
  border-radius: 8px;
}

.badge-table-container {
  overflow-x: auto;
  border: 1px solid var(--border);
  border-radius: 8px;
  -webkit-overflow-scrolling: touch; /* smooth scroll on iOS */
}

.badge-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.92rem;
  min-width: 420px; /* prevents squishing on small screens */
}

.badge-table thead th {
  text-align: left;
  padding: 0.75rem 1rem;
  background-color: var(--primary-light);
  color: var(--primary);
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
  border-bottom: 1px solid var(--border);
}

.badge-table tbody tr {
  border-bottom: 1px solid var(--border);
  transition: background-color 0.2s;
}

.badge-table tbody tr:last-child {
  border-bottom: none;
}

.badge-table tbody tr:hover {
  background-color: var(--bg-body);
}

.badge-table td {
  padding: 0.85rem 1rem;
  vertical-align: middle;
  color: var(--text-main);
}

.badge-identity {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.badge-icon {
  flex-shrink: 0;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  background-color: var(--primary-light);
  border: 1px solid var(--primary);
  border-radius: 50%;
}

.badge-identity strong {
  display: block;
  color: var(--text-main);
  font-size: 0.95rem;
}

.badge-table-desc {
  margin: 0.15rem 0 0 0;
  font-size: 0.8rem;
  color: var(--text-muted);
  font-weight: 500;
  line-height: 1.3;
}

.badge-tag {
  display: inline-block;
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
  background-color: var(--primary-light);
  color: var(--primary);
  border: 1px solid var(--primary);
}

.side-form-card {
  height: fit-content;
}

.row-actions {
  display: flex;
  gap: 0.4rem;
}

.row-btn {
  border: none;
  border-radius: 6px;
  padding: 0.3rem 0.6rem;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.row-btn.edit {
  background-color: var(--primary-light);
  color: var(--primary);
  border: 1px solid var(--primary);
}

.row-btn.edit:hover {
  background-color: var(--primary);
  color: white;
}

.row-btn.delete {
  background-color: #fde8e8;
  color: #d9534f;
  border: 1px solid #d9534f;
}

.row-btn.delete:hover {
  background-color: #d9534f;
  color: white;
}

.cancel-btn {
  background-color: transparent;
  border: 1px solid var(--border);
  color: var(--text-muted);
  padding: 0.85rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  width: 100%;
  transition: all 0.2s;
}

.cancel-btn:hover {
  background-color: var(--bg-body);
}

/* --- Responsive: badges --- */
@media (max-width: 1024px) {
  .badge-table-desc {
    display: none;
  }
}

@media (max-width: 600px) {
  .badge-table thead {
    display: none;
  }
  .badge-table,
  .badge-table tbody,
  .badge-table tr,
  .badge-table td {
    display: block;
    width: 100%;
  }
  .badge-table {
    min-width: unset; /* allow full width in card mode */
  }
  .badge-table tr {
    padding: 0.75rem 1rem;
  }
  .badge-table td {
    padding: 0.25rem 0;
    border: none;
  }
  .badge-table td:not(:first-child)::before {
    content: attr(data-label);
    display: inline-block;
    width: 110px;
    font-size: 0.75rem;
    color: var(--text-muted);
    text-transform: uppercase;
    font-weight: 600;
  }
}
</style>