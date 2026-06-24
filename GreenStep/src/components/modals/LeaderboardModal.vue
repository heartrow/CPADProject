<script setup>
import { ref } from 'vue'

const props = defineProps({
  show: Boolean,
  challengeTitle: String,
  unit: String
})

const emit = defineEmits(['close'])

// Dummy data for the leaderboard participants
const leaderboardData = ref([
  { rank: 1, name: 'Azri', contribution: 120 },
  { rank: 2, name: 'Sarah M.', contribution: 95 },
  { rank: 3, name: 'Ali K.', contribution: 80 },
  { rank: 4, name: 'Mei Ling', contribution: 60 },
  { rank: 5, name: 'Muthu', contribution: 45 },
])
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

        <table class="leaderboard-table">
          <thead>
            <tr>
              <th>Rank</th>
              <th>Participant</th>
              <th class="align-right">Contribution</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in leaderboardData" :key="user.rank" :class="{ 'highlight': user.name === 'Azri' }">
              <td class="rank-col">#{{ user.rank }}</td>
              <td class="name-col">{{ user.name }}</td>
              <!-- Dynamically injecting the unit here -->
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
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 100;
  backdrop-filter: blur(2px);
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
</style>
