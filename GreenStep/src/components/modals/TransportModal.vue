<template>
  <div class="modal-overlay" @click.self="$emit('close')">

    <div class="modal-card">

      <div class="modal-header">
        <h2>🚗 Log Transport</h2>
        <button class="close-btn" @click="$emit('close')">✕</button>
      </div>

      <form @submit.prevent="handleSubmit" class="modal-body">
        <div class="form-group">
          <label>Vehicle Type</label>
          <select v-model="vehicleType">
            <option value="petrol">Private Car (Petrol)</option>
            <option value="ev">Electric Vehicle (EV)</option>
            <option value="bus">Public Bus</option>
            <option value="train">Train / MRT</option>
          </select>
        </div>

        <div class="form-group">
          <label>Distance Traveled (km)</label>
          <input type="number" v-model="distance" placeholder="e.g., 15" required />
        </div>

        <div class="modal-footer">
          <button type="button" class="cancel-btn" @click="$emit('close')">Cancel</button>
          <button type="submit" class="submit-btn">Log Activity</button>
        </div>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Define the events this modal can send back to ActivityView
defineEmits(['close']);

// Form data
const vehicleType = ref('petrol');
const distance = ref('');

const handleSubmit = () => {
  // Here is where you would normally calculate the carbon footprint
  // and send the data to your backend or store.
  console.log(`Logging ${distance.value}km via ${vehicleType.value}`);

  // Close the modal after submitting!
  // In a real app, you might emit the data back to the parent: emit('submit', data)
  // For now, we'll just trigger the close event.
};
</script>

<style scoped>
/* --- The Magic that makes it a Modal --- */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.6); /* Semi-transparent black */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999; /* Ensures it sits on top of TopBar and SideBar */
}

/* --- The actual popup box --- */
.modal-card {
  background-color: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  width: 90%;
  max-width: 450px;
  padding: 1.5rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);

  /* A nice little pop-in animation */
  animation: modalPop 0.3s ease-out;
}

@keyframes modalPop {
  from { opacity: 0; transform: scale(0.95) translateY(-20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

/* --- Interior Styling --- */
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid var(--border);
  padding-bottom: 1rem;
}

.modal-header h2 {
  margin: 0;
  color: var(--text-main);
  font-size: 1.3rem;
}

.close-btn {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  color: var(--text-muted);
  cursor: pointer;
  transition: color 0.2s;
}

.close-btn:hover {
  color: var(--text-main);
}

.form-group {
  margin-bottom: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  color: var(--text-main);
  font-weight: 600;
  font-size: 0.9rem;
}

.form-group input, .form-group select {
  padding: 0.75rem;
  border: 1px solid var(--border);
  border-radius: 6px;
  background-color: var(--primary-light);
  color: var(--text-main);
  font-family: inherit;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.cancel-btn {
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text-muted);
  padding: 0.75rem 1.25rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}

.cancel-btn:hover {
  background: rgba(0,0,0,0.05);
}

.submit-btn {
  background: var(--primary);
  border: none;
  color: var(--primary-light);
  padding: 0.75rem 1.25rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}

.submit-btn:hover {
  background: #465926;
}
</style>
