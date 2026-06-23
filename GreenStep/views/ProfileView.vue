<script setup>
import { ref } from 'vue'
import TopBar from '@/components/TopBar.vue'
import SideBar from '@/components/SideBar.vue'

// State for our tabbed navigation
const activeTab = ref('overview')

const badges = ref([
  {
    id: 1,
    icon: '🌱',
    name: 'First Step',
    description: 'Logged your first eco-activity.',
    unlocked: true,
    date: 'March 15, 2026'
  },
  {
    id: 2,
    icon: '🔥',
    name: 'Streak Master',
    description: 'Maintained a 5-day logging streak.',
    unlocked: true,
    date: 'March 20, 2026'
  },
  {
    id: 3,
    icon: '🚴',
    name: 'Zero Emission Commute',
    description: 'Logged 50km of walking or cycling.',
    unlocked: false,
    date: null
  },
  {
    id: 4,
    icon: '🔌',
    name: 'Energy Saver',
    description: 'Reduced electricity usage by 20% this week.',
    unlocked: false,
    date: null
  },
  {
    id: 5,
    icon: '👑',
    name: 'Campus Champion',
    description: 'Ranked Top 10 on the campus leaderboard.',
    unlocked: false,
    date: null
  }
])
</script>

<template>
  <TopBar />
  <SideBar />

  <main class="profile-main">
    <div class="view-section active">
      <div class="card main-profile-card">

        <div class="profile-header">
          <div class="profile-avatar">👨‍💻</div>
          <div class="profile-info">
            <h1>Azri</h1>
            <p>📍 Johor, Malaysia</p>
          </div>
        </div>

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

        <div v-if="activeTab === 'overview'" class="profile-details-grid">
          <div class="card dashed-card">
            <h2 class="card-title">Account Details</h2>

            <div class="detail-group">
              <label>Email Address</label>
              <div class="detail-value">azri@example.com</div>
            </div>

            <div class="detail-group">
              <label>System Role</label>
              <div class="detail-value">End-User</div>
            </div>

            <div class="detail-group">
              <label>Joined Date</label>
              <div class="detail-value">March 15, 2026</div>
            </div>
          </div>

          <div class="card dashed-card">
            <h2 class="card-title">Academic Context</h2>

            <div class="detail-group">
              <label>Program</label>
              <div class="detail-value">Software Engineering Student</div>
            </div>

            <div class="detail-group">
              <label>Carbon Tracking Factor</label>
              <div class="detail-value">Standard MY Baseline</div>
            </div>

          </div>
        </div>

        <div v-if="activeTab === 'badges'" class="badges-tab">
          <div class="badges-stats">
            <div class="stat-badge">
              <span class="stat-num">2</span>
              <span class="stat-label">Unlocked</span>
            </div>
            <div class="stat-badge">
              <span class="stat-num">5</span>
              <span class="stat-label">Total Badges</span>
            </div>
          </div>

          <div class="badges-grid">
            <div
              v-for="badge in badges"
              :key="badge.id"
              class="badge-card"
              :class="{ 'locked': !badge.unlocked }"
            >
              <div class="badge-icon">{{ badge.icon }}</div>
              <h3 class="badge-name">{{ badge.name }}</h3>
              <p class="badge-desc">{{ badge.description }}</p>

              <div class="badge-footer">
                <span v-if="badge.unlocked" class="date-unlocked">Unlocked: {{ badge.date }}</span>
                <span v-else class="locked-text">🔒 Locked</span>
              </div>
            </div>
          </div>
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
  background-color: rgba(0,0,0,0.05);
}

.tab-btn.active {
  background-color: var(--primary-light);
  color: var(--primary);
  border: 1px solid var(--border);
}

/* --- Overview Tab Styles --- */
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

.btn-action.edit-btn {
  margin-top: auto;
  align-self: stretch;
  background-color: var(--primary);
  color: white;
  border: none;
  padding: 0.85rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-action.edit-btn:hover {
  background-color: #4a5e29;
}

/* --- Badges Tab Styles --- */
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

/* Locked state dims the badge */
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

/* --- Responsive Adjustments --- */
@media (max-width: 1024px) {
  .profile-details-grid {
    grid-template-columns: 1fr;
  }
  .badges-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .profile-main {
    margin-left: 0;
    padding: 1rem;
    padding-bottom: 90px;
  }

  .main-profile-card {
    padding: 1.5rem;
  }

  .badges-grid {
    grid-template-columns: 1fr;
  }

  .tab-navigation {
    flex-direction: column;
  }
}
</style>
