<script setup>
import { ref, onMounted } from 'vue'
import TopBar from '@/components/TopBar.vue'
import SideBar from '@/components/SideBar.vue'
import LeaderboardModal from '@/components/modals/LeaderboardModal.vue'
import CreateChallengeModal from '@/components/modals/CreateChallengeModal.vue'
import api from '@/api/client.js'
import { useAuth } from '@/stores/auth'

const challenges = ref([])
const isLoading = ref(true)
const errorMessage = ref('')
const authStore = useAuth()

// --- Modal State ---
const isModalOpen = ref(false)
const isCreateModalOpen = ref(false)
const selectedChallengeId = ref(null)
const selectedChallengeTitle = ref('')
const selectedChallengeUnit = ref('')

const calculatePercentage = (current, target) => {
  if (!target) return 0;
  return Math.round((current / target) * 100);
}

const handleChallengeAction = async (challenge, action) => {
  if (action === 'leaderboard') {
    selectedChallengeId.value = challenge.id
    selectedChallengeTitle.value = challenge.title
    selectedChallengeUnit.value = challenge.unit
    isModalOpen.value = true
  }
  else if (action === 'join') {
    try {
      // 2. Use Axios to POST
      await api.post('/api/challenges/join', { challenge_id: challenge.id });
      challenge.hasUserJoined = true;
    } catch (error) {
      console.error("Failed to join:", error);
    }
  }
  else if (action === 'leave') {
    try {
      // 3. Use Axios to POST (leave)
      await api.post('/api/challenges/leave', { challenge_id: challenge.id });
      challenge.hasUserJoined = false;
    } catch (error) {
      console.error("Failed to leave:", error);
    }
  }
}

const closeModal = () => {
  isCreateModalOpen.value = false
  isModalOpen.value = false
  selectedChallengeId.value = null
  selectedChallengeTitle.value = ''
  selectedChallengeUnit.value = ''
}

// --- Fetch Data on Load ---
onMounted(async () => {
  try {
// 4. Use Axios to GET data
    const response = await api.get('/api/challenges')

    // Axios automatically parses JSON into `response.data`
    challenges.value = response.data

  } catch (error) {
    console.error("API Fetch Failed:", error)
    errorMessage.value = "Failed to load challenges from the server. Please check your API connection."
  } finally {
    isLoading.value = false
  }
})

</script>

<template>
  <TopBar />
  <SideBar />

<main class="challenges-main">
    <div class="view-section active">

      <div class="card outer-card">
        <div class="header-section flex-header">
          <div>
            <h2 class="card-title">Active Community Challenges</h2>
            <p class="subtitle">Work together with your group to hit collective reduction targets.</p>
          </div>

          <button
            v-if="authStore.user?.role === 'admin' || authStore.user?.role === 'leader'"
            class="btn-create-challenge"
            @click="isCreateModalOpen = true"
          >
            ➕ New Challenge
          </button>
        </div>

        <div v-if="isLoading" class="loading-state">
          Loading challenges...
        </div>

        <div v-else-if="errorMessage" class="status-state error">
          {{ errorMessage }}
        </div>

        <div v-else class="challenges-grid">

          <div v-for="challenge in challenges" :key="challenge.id" class="card challenge-card">

            <h3 class="challenge-title">{{ challenge.title }}</h3>
            <p class="challenge-desc">{{ challenge.desc }}</p>

            <div class="progress-section">
              <div class="progress-bar-bg">
                <div
                  class="progress-bar-fill"
                  :style="{ width: calculatePercentage(challenge.currentProgress, challenge.targetGoal) + '%' }"
                >
                  {{ calculatePercentage(challenge.currentProgress, challenge.targetGoal) }}%
                </div>
              </div>
              <p class="progress-text">
                {{ challenge.currentProgress }} / {{ challenge.targetGoal }} {{ challenge.unit }}
              </p>
            </div>

            <div class="action-buttons">
              <template v-if="challenge.hasUserJoined">
                <button class="btn-action" @click="handleChallengeAction(challenge, 'leaderboard')">
                  View Leaderboard
                </button>
                <button class="btn-action secondary" @click="handleChallengeAction(challenge, 'leave')">
                  Leave Challenge
                </button>
              </template>

              <template v-else>
                <button class="btn-action primary-join" @click="handleChallengeAction(challenge, 'join')">
                  Join Challenge
                </button>
              </template>
            </div>

          </div>
        </div>
      </div>
    </div>

    <LeaderboardModal
      :show="isModalOpen"
      :challengeId="selectedChallengeId"
      :challengeTitle="selectedChallengeTitle"
      :unit="selectedChallengeUnit"
      @close="closeModal"
    />

    <CreateChallengeModal
      :show="isCreateModalOpen"
      @close="isCreateModalOpen = false"
      @challengeCreated="fetchChallenges"
    />

  </main>
</template>

<style scoped>
/* Main Layout */
.challenges-main {
  padding: 2rem;
  min-height: 100vh;
  background-color: var(--bg-body);
  margin-left: 225px;
}

/* Base Card Styling */
.card {
  background-color: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 2.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.header-section {
  margin-bottom: 2.5rem;
}

.card-title {
  font-size: 1.75rem;
  margin-top: 0;
  margin-bottom: 0.5rem;
  color: var(--text-main);
  font-weight: 700;
}

.subtitle {
  color: var(--text-muted);
  font-size: 1.05rem;
  margin: 0;
}

/* --- Challenges Grid --- */
.challenges-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr); /* 2 columns side-by-side */
  gap: 2rem;
}

/* Individual Challenge Cards */
.challenge-card {
  padding: 2rem;
  box-shadow: none;
  border: 2px dashed var(--border); /* Matches your dashed border design */
  display: flex;
  flex-direction: column;
  transition: border-color 0.2s ease, transform 0.2s ease;
}

.challenge-card:hover {
  border-color: var(--primary);
  transform: translateY(-2px);
}

.challenge-title {
  color: var(--primary);
  font-size: 1.25rem;
  margin-top: 0;
  margin-bottom: 1rem;
  font-weight: 700;
}

.challenge-desc {
  font-size: 0.95rem;
  color: var(--text-main);
  line-height: 1.5;
  margin-bottom: 2rem;
  flex-grow: 1;
}

/* --- Progress Bar --- */
.progress-section {
  margin-bottom: 1.5rem;
}

.progress-bar-bg {
  width: 100%;
  background-color: var(--primary-light);
  border-radius: 20px;
  height: 24px;
  overflow: hidden;
  border: 1px solid rgba(0,0,0,0.05);
}

.progress-bar-fill {
  background-color: var(--primary);
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.8rem;
  font-weight: bold;
  border-radius: 20px;
  transition: width 0.8s cubic-bezier(0.22, 1, 0.36, 1);
  min-width: 30px;
}

.progress-text {
  font-size: 0.85rem;
  margin-top: 0.5rem;
  margin-bottom: 0;
  color: var(--text-muted);
  text-align: right;
  font-weight: 600;
}

/* --- Buttons --- */
.btn-action {
  background-color: var(--primary);
  color: white;
  border: none;
  padding: 0.85rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: background-color 0.2s;
  width: 100%;
}

.btn-action:hover {
  background-color: #4a5e29;
}

/* Secondary Button State (For the "Leave Challenge" button) */
.btn-action.secondary {
  background-color: transparent;
  color: var(--text-muted);
  border: 1px solid var(--border);
}

.btn-action.secondary:hover {
  background-color: #ffeaea;
  color: #d9534f;
  border-color: #d9534f;
}

.flex-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.btn-create-challenge {
  background-color: var(--primary);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.2s;
  white-space: nowrap;
}

.btn-create-challenge:hover {
  background-color: #4a5e29;
}

/* --- Responsive Layout --- */
@media (max-width: 1024px) {
  .challenges-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .challenges-main {
    margin-left: 0;
    padding: 1rem;
    padding-bottom: 90px;
  }

  .card {
    padding: 1.5rem;
  }
}
</style>
