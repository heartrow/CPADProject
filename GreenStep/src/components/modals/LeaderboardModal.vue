<script setup>
import { ref, watch } from 'vue'
import api from '@/api/client.js'
import { useAuth } from '@/stores/auth'

const props = defineProps({
  show: Boolean,
  challengeId: [String, Number],
  challengeTitle: String,
  unit: String
})

const emit = defineEmits(['close'])

const auth = useAuth()
const leaderboardData = ref([])
const isLoading = ref(false)
const error = ref(null)

async function fetchLeaderboard() {
  if (!props.challengeId) return
  isLoading.value = true
  error.value = null
  try {
    const res = await api.get(`/api/challenges/${props.challengeId}/leaderboard`)
    leaderboardData.value = res.data
  } catch (err) {
    error.value = 'Failed to load leaderboard. Please try again.'
    leaderboardData.value = []
  } finally {
    isLoading.value = false
  }
}

// Re-fetch whenever the modal is opened for a (possibly different) challenge.
watch(
  () => [props.show, props.challengeId],
  ([show]) => {
    if (show) fetchLeaderboard()
  },
  { immediate: true }
)
</script>

<template>
  <div v-if="show" class="modal-overlay" @click.self="emit('close')">
    <div class="modal-card">

      <div class="modal-header">
        <h3>🏆 Leaderboard</h3>
        <button class="close-btn" @click="emit('close')">✖</button>
      </div>

      <div class="modal-body">
        <p class="modal-subtitle">{{ challengeTitle }}</p>

        <div v-if="isLoading" class="state-message">Loading leaderboard…</div>
        <div v-else-if="error" class="state-message state-error">{{ error }}</div>
        <div v-else-if="leaderboardData.length === 0" class="state-message">
          No contributions yet. Be the first!
        </div>

        <table v-else class="leaderboard-table">
          <thead>
            <tr>
              <th>Rank</th>
              <th>Participant</th>
              <th class="align-right">Contribution</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in leaderboardData" :key="user.rank" :class="{ 'highlight': user.name === auth.user?.name }">
              <td class="rank-col">#{{ user.rank }}</td>
              <td class="name-col">{{ user.name }}</td>
              <td class="score-col align-right">{{ user.contribution }} <span class="unit-text">{{ unit }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  backdrop-filter: blur(4px);
}

.modal-card {
  background-color: var(--bg-card, #ffffff);
  width: 90%;
  max-width: 500px;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

.modal-header {
  background-color: var(--primary, #2d4a22);
  color: white;
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
}

.close-btn {
  background: transparent;
  border: none;
  color: white;
  font-size: 1.25rem;
  cursor: pointer;
  transition: opacity 0.2s;
}

.close-btn:hover {
  opacity: 0.7;
}

.modal-body {
  padding: 1.5rem;
}

.modal-subtitle {
  color: var(--text-muted, #666);
  font-weight: 600;
  margin-top: 0;
  margin-bottom: 1.5rem;
  font-size: 0.95rem;
  text-align: center;
}

.leaderboard-table {
  width: 100%;
  border-collapse: collapse;
}

.leaderboard-table th {
  text-align: left;
  padding: 0.75rem;
  border-bottom: 2px solid var(--border, #eee);
  color: var(--text-muted, #666);
  font-size: 0.85rem;
  text-transform: uppercase;
}

.leaderboard-table td {
  padding: 1rem 0.75rem;
  border-bottom: 1px solid rgba(0,0,0,0.05);
  color: var(--text-main, #333);
  font-weight: 500;
}

.leaderboard-table tr:last-child td {
  border-bottom: none;
}

.leaderboard-table tr.highlight td {
  background-color: var(--primary-light, #eaf2e3);
  font-weight: 700;
  color: var(--primary, #2d4a22);
}

.rank-col {
  width: 60px;
  font-weight: 700;
  color: var(--primary, #2d4a22);
}

.align-right {
  text-align: right !important;
}

.score-col {
  font-family: monospace;
  font-size: 1.05rem;
}

.unit-text {
  font-size: 0.8rem;
  color: var(--text-muted, #666);
  font-weight: normal;
}

.state-message {
  text-align: center;
  padding: 2rem 1rem;
  color: var(--text-muted, #666);
  font-weight: 500;
}

.state-error {
  color: #c0392b;
}
</style>
