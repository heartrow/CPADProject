<script setup>
import { ref, computed, onMounted } from 'vue'
import TopBar from '@/components/TopBar.vue'
import SideBar from '@/components/SideBar.vue'
import api from '@/api/client.js'
import { useAuth } from '@/stores/auth.js'

// ── State ────────────────────────────────────────────────────
const activeTab = ref('overview')
const auth      = useAuth()

const badges    = ref([])
const isLoading = ref(false)
const errorMsg  = ref(null)

// ── Edit Profile State ────────────────────────────────────────
const isEditing = ref(false)
const isSaving = ref(false)
const profileError = ref(null)
const profileSuccess = ref(null)

const editForm = ref({
  name: '',
  location: '',
  program: '',
  avatar: '',
  carbon_factor: ''
})

const AVATAR_OPTIONS = ['👨‍💻', '👩‍💻', '🌱', '🌿', '🥗', '⚡', '♻️', '🚌', '🌎', '🍃']
const CARBON_FACTOR_OPTIONS = ['Standard MY Baseline', 'Eco-Conscious MY Baseline']

// ── Computed ─────────────────────────────────────────────────
const unlockedCount = computed(() => badges.value.filter(b => b.unlocked).length)
const totalCount    = computed(() => badges.value.length)

// ── Helpers ──────────────────────────────────────────────────
const BADGE_ICONS = {
  'First Step'               : '🌱',
  'Green Beginner'           : '🌿',
  'Eco Warrior'              : '⚡',
  'Plant-Based Pioneer'      : '🥗',
  'Public Transport Champion': '🚌',
  default                    : '🏅'
}

function getIcon(name) {
  return BADGE_ICONS[name] ?? BADGE_ICONS.default
}

function formatDate(dateStr) {
  if (!dateStr) return null
  return new Date(dateStr).toLocaleDateString('en-MY', {
    year : 'numeric',
    month: 'long',
    day  : 'numeric'
  })
}

// ── Edit Actions ──────────────────────────────────────────────
function startEdit() {
  profileError.value = null
  profileSuccess.value = null
  editForm.value = {
    name: auth.user?.name ?? '',
    location: auth.user?.location ?? 'Johor, Malaysia',
    program: auth.user?.program ?? 'Software Engineering Student',
    avatar: auth.user?.avatar ?? '👨‍💻',
    carbon_factor: auth.user?.carbon_factor ?? 'Standard MY Baseline'
  }
  isEditing.value = true
}

function cancelEdit() {
  isEditing.value = false
  profileError.value = null
}

async function saveProfile() {
  profileError.value = null
  profileSuccess.value = null
  isSaving.value = true

  try {
    const res = await api.put('/auth/profile', editForm.value)
    auth.setUser(res.data.user)
    profileSuccess.value = 'Profile updated successfully!'
    isEditing.value = false
  } catch (err) {
    const d = err.response?.data
    profileError.value = d?.errors ? Object.values(d.errors).join(' • ') : (d?.error ?? 'Failed to update profile.')
  } finally {
    isSaving.value = false
  }
}

// ── Fetch badges ─────────────────────────────────────────────
// Uses api/client.js (Axios) — JWT is attached automatically by the request interceptor
async function fetchBadges() {
  isLoading.value = true
  errorMsg.value  = null

  try {
    const res = await api.get('/api/badges')

    badges.value = res.data.data.map(b => ({
      id         : b.badge_id,
      icon       : getIcon(b.name),
      name       : b.name,
      description: b.description,
      imageUrl   : b.image_url,
      unlocked   : b.unlocked,
      date       : formatDate(b.earned_at)
    }))
  } catch (err) {
    // api/client.js interceptor handles 401 (auto-logout); handle everything else here
    errorMsg.value = err.response?.data?.error ?? 'Failed to load badges.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchBadges()
})
</script>

<template>
  <TopBar />
  <SideBar />

  <main class="profile-main">
    <div class="view-section active">
      <div class="card main-profile-card">

        <!-- Profile Header -->
        <div class="profile-header">
          <div class="profile-avatar">{{ auth.user?.avatar ?? '👨‍💻' }}</div>
          <div class="profile-info">
            <h1>{{ auth.user?.name ?? 'User' }}</h1>
            <p>📍 {{ auth.user?.location ?? 'Johor, Malaysia' }}</p>
          </div>
          <div v-if="activeTab === 'overview'" class="header-action">
            <button v-if="!isEditing" class="edit-profile-btn" @click="startEdit">
              ✏️ Edit Profile
            </button>
          </div>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
          <button
            class="tab-btn"
            :class="{ active: activeTab === 'overview' }"
            @click="activeTab = 'overview'"
          >
            📊 Account Overview
          </button>
          <button
            class="tab-btn"
            :class="{ active: activeTab === 'badges' }"
            @click="activeTab = 'badges'"
          >
            🏆 Badges & Achievements
          </button>
        </div>

        <hr style="border: 1px solid var(--border); margin: 0 0 2rem 0;">

        <!-- ── Overview Tab ── -->
        <div v-if="activeTab === 'overview'">
          <div v-if="profileSuccess" class="profile-alert success">
            ✅ {{ profileSuccess }}
          </div>
          <div v-if="profileError" class="profile-alert error">
            ⚠️ {{ profileError }}
          </div>

          <form @submit.prevent="saveProfile" class="profile-details-grid">
            <div class="card dashed-card">
              <h2 class="card-title">Account Details</h2>

              <div class="detail-group">
                <label>Email Address</label>
                <div class="detail-value readonly">{{ auth.user?.email ?? '—' }}</div>
              </div>

              <div class="detail-group">
                <label>System Role</label>
                <div class="detail-value readonly">{{ auth.user?.role ?? '—' }}</div>
              </div>

              <div class="detail-group" v-if="isEditing">
                <label>Avatar Icon</label>
                <select v-model="editForm.avatar" class="edit-input">
                  <option v-for="emoji in AVATAR_OPTIONS" :key="emoji" :value="emoji">
                    {{ emoji }}
                  </option>
                </select>
              </div>

              <div class="detail-group">
                <label>Joined Date</label>
                <div class="detail-value readonly">
                  {{ formatDate(auth.user?.created_at) ?? 'March 15, 2026' }}
                </div>
              </div>
            </div>

            <div class="card dashed-card">
              <h2 class="card-title">Profile Info</h2>

              <div class="detail-group">
                <label>Full Name</label>
                <input v-if="isEditing" v-model="editForm.name" class="edit-input" type="text" required placeholder="Name" />
                <div v-else class="detail-value">{{ auth.user?.name ?? '—' }}</div>
              </div>

              <div class="detail-group">
                <label>Location</label>
                <input v-if="isEditing" v-model="editForm.location" class="edit-input" type="text" placeholder="Location" />
                <div v-else class="detail-value">{{ auth.user?.location ?? '—' }}</div>
              </div>

              <div class="detail-group">
                <label>Program / Major</label>
                <input v-if="isEditing" v-model="editForm.program" class="edit-input" type="text" placeholder="Program" />
                <div v-else class="detail-value">{{ auth.user?.program ?? '—' }}</div>
              </div>

              <div class="detail-group">
                <label>Carbon Tracking Factor</label>
                <select v-if="isEditing" v-model="editForm.carbon_factor" class="edit-input">
                  <option v-for="cf in CARBON_FACTOR_OPTIONS" :key="cf" :value="cf">
                    {{ cf }}
                  </option>
                </select>
                <div v-else class="detail-value">{{ auth.user?.carbon_factor ?? '—' }}</div>
              </div>
            </div>

            <div v-if="isEditing" class="form-actions-bar">
              <button type="button" class="cancel-btn" @click="cancelEdit" :disabled="isSaving">
                Cancel
              </button>
              <button type="submit" class="submit-btn" :disabled="isSaving">
                {{ isSaving ? 'Saving…' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>

        <!-- ── Badges Tab ── -->
        <div v-if="activeTab === 'badges'" class="badges-tab">

          <!-- Loading -->
          <div v-if="isLoading" class="badges-feedback">
            <span>⏳</span> Loading badges…
          </div>

          <!-- Error -->
          <div v-else-if="errorMsg" class="badges-feedback error">
            ⚠️ {{ errorMsg }}
            <button class="retry-btn" @click="fetchBadges">Retry</button>
          </div>

          <!-- Loaded -->
          <template v-else>
            <div class="badges-stats">
              <div class="stat-badge">
                <span class="stat-num">{{ unlockedCount }}</span>
                <span class="stat-label">Unlocked</span>
              </div>
              <div class="stat-badge">
                <span class="stat-num">{{ totalCount }}</span>
                <span class="stat-label">Total Badges</span>
              </div>
            </div>

            <div v-if="badges.length === 0" class="badges-feedback">
              No badges yet. Start logging activities to earn your first one!
            </div>

            <div v-else class="badges-grid">
              <div
                v-for="badge in badges"
                :key="badge.id"
                class="badge-card"
                :class="{ locked: !badge.unlocked }"
              >
                <div class="badge-icon">{{ badge.icon }}</div>
                <h3 class="badge-name">{{ badge.name }}</h3>
                <p class="badge-desc">{{ badge.description }}</p>

                <div class="badge-footer">
                  <span v-if="badge.unlocked" class="date-unlocked">
                    Unlocked: {{ badge.date }}
                  </span>
                  <span v-else class="locked-text">🔒 Locked</span>
                </div>
              </div>
            </div>
          </template>

        </div>

      </div>
    </div>
  </main>
</template>

<style scoped>
/* --- Main Layout --- */
.profile-main {
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

.main-profile-card {
  padding: 2.5rem;
  max-width: 1000px;
}

/* --- Profile Header --- */
.profile-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.profile-avatar {
  font-size: 4rem;
  background-color: var(--primary-light);
  width: 100px;
  height: 100px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 50%;
  border: 3px solid var(--primary);
}

.profile-info h1 {
  margin: 0 0 0.25rem 0;
  font-size: 2rem;
  color: var(--text-main);
}

.profile-info p {
  margin: 0;
  color: var(--text-muted);
  font-size: 1.05rem;
  font-weight: 500;
}

/* --- Tab Navigation --- */
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

/* --- Overview Tab --- */
.profile-details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 2rem;
}

.dashed-card {
  box-shadow: none;
  border-style: dashed;
  border-width: 2px;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.card-title {
  font-size: 1.35rem;
  margin-top: 0;
  margin-bottom: 1.5rem;
  color: var(--text-main);
}

.detail-group {
  margin-bottom: 1.25rem;
}

.detail-group label {
  display: block;
  font-size: 0.85rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.detail-value {
  font-size: 1.1rem;
  color: var(--text-main);
  font-weight: 500;
}

/* --- Badges Tab --- */
.badges-stats {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-badge {
  background-color: var(--primary-light);
  padding: 1rem 1.5rem;
  border-radius: 10px;
  border: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stat-num {
  font-size: 1.8rem;
  font-weight: 800;
  color: var(--primary);
}

.stat-label {
  font-size: 0.85rem;
  color: var(--text-muted);
  font-weight: 600;
  text-transform: uppercase;
}

.badges-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.badge-card {
  background-color: var(--bg-body);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s;
}

.badge-card:hover {
  transform: translateY(-3px);
}

.badge-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.badge-name {
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
  color: var(--text-main);
}

.badge-desc {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin-bottom: 1.5rem;
  flex-grow: 1;
}

.badge-footer {
  font-size: 0.8rem;
  font-weight: 600;
  padding-top: 1rem;
  border-top: 1px dashed var(--border);
}

.date-unlocked {
  color: var(--primary);
}

.locked-text {
  color: #999;
}

.badge-card.locked {
  background-color: #f9f9f9;
  border-color: #eee;
}

.badge-card.locked .badge-icon {
  filter: grayscale(100%) opacity(50%);
}

.badge-card.locked .badge-name,
.badge-card.locked .badge-desc {
  color: #aaa;
}

/* --- Feedback states --- */
.badges-feedback {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1.5rem;
  border-radius: 10px;
  border: 1px dashed var(--border);
  color: var(--text-muted);
  font-size: 0.95rem;
}

.badges-feedback.error {
  border-color: #f5c6c6;
  background-color: #fff5f5;
  color: #c0392b;
}

.retry-btn {
  margin-left: auto;
  background: transparent;
  border: 1px solid #c0392b;
  color: #c0392b;
  padding: 0.3rem 0.75rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
}

/* --- Responsive --- */
@media (max-width: 1024px) {
  .profile-details-grid { grid-template-columns: 1fr; }
  .badges-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
  .profile-main {
    margin-left: 0;
    padding: 1rem;
    padding-bottom: 90px;
  }
  .main-profile-card { padding: 1.5rem; }
  .badges-grid { grid-template-columns: 1fr; }
  .tab-navigation { flex-direction: column; }
}

/* --- Edit Profile Styling --- */
.profile-header {
  position: relative;
}

.header-action {
  margin-left: auto;
}

.edit-profile-btn {
  background-color: transparent;
  border: 1px solid var(--border);
  color: var(--text-main);
  padding: 0.5rem 1rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.edit-profile-btn:hover {
  background-color: var(--primary-light);
  border-color: var(--primary);
  color: var(--primary);
}

.edit-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border);
  border-radius: 8px;
  background-color: var(--primary-light);
  color: var(--text-main);
  font-size: 1rem;
  font-family: inherit;
  box-sizing: border-box;
  margin-top: 0.25rem;
  outline: none;
  transition: border-color 0.2s;
}

.edit-input:focus {
  border-color: var(--primary);
}

.readonly {
  background-color: rgba(0, 0, 0, 0.02);
  border-radius: 6px;
  padding: 0.25rem 0.5rem;
  display: inline-block;
}

.form-actions-bar {
  grid-column: span 2;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border);
}

.cancel-btn {
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text-muted);
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  transition: background-color 0.2s;
}

.cancel-btn:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.submit-btn {
  background-color: var(--primary);
  border: none;
  color: var(--primary-light);
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  transition: background-color 0.2s;
}

.submit-btn:hover {
  background-color: #465926;
}

.submit-btn:disabled, .cancel-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.profile-alert {
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  font-weight: 600;
  border: 1px solid transparent;
}

.profile-alert.success {
  background-color: #e6f4ea;
  border-color: #b7e1cd;
  color: #137333;
}

.profile-alert.error {
  background-color: #fce8e6;
  border-color: #fad2cf;
  color: #c5221f;
}

@media (max-width: 1024px) {
  .form-actions-bar {
    grid-column: span 1;
  }
}
</style>