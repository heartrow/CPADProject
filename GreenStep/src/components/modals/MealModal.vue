<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-card">
      <div class="modal-header">
        <h2>🍔 Log a Meal</h2>
        <button class="close-btn" @click="$emit('close')">✕</button>
      </div>

      <form @submit.prevent="handleSubmit" class="modal-body">

        <div class="form-group">
          <label>Meal Time</label>
          <select v-model="mealTime">
            <option value="breakfast">Breakfast</option>
            <option value="lunch">Lunch</option>
            <option value="dinner">Dinner</option>
            <option value="snack">Snack / Coffee</option>
          </select>
        </div>

        <div class="form-group">
          <label>Primary Ingredient (Protein/Base)</label>
          <select v-model="primaryIngredient">
            <option value="beef_lamb">Beef / Lamb (Highest Impact)</option>
            <option value="pork_poultry">Pork / Poultry (Medium Impact)</option>
            <option value="seafood">Fish / Seafood (Medium Impact)</option>
            <option value="vegetarian">Dairy / Eggs / Vegetarian (Low Impact)</option>
            <option value="vegan">Plant-based / Vegan (Lowest Impact)</option>
          </select>
        </div>

        <div class="form-group">
          <label>Food Source</label>
          <select v-model="foodSource">
            <option value="home">Home Cooked</option>
            <option value="restaurant">Restaurant (Dine-in)</option>
            <option value="takeout">Takeout / Delivery (Includes Packaging)</option>
          </select>
        </div>

        <div class="form-group">
          <label>Estimated Weight (kg)</label>
          <input type="number" v-model="weight" placeholder="e.g., 2.5" step="0.1" required />
        </div>

        <div class="modal-footer">
          <button type="button" class="cancel-btn" @click="$emit('close')">Cancel</button>
          <button type="submit" class="submit-btn">Log Meal</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

defineEmits(['close']);

const mealTime = ref('lunch');
const primaryIngredient = ref('pork_poultry');
const foodSource = ref('home');

const handleSubmit = () => {
  console.log('Meal Logged:', {
    time: mealTime.value,
    ingredient: primaryIngredient.value,
    source: foodSource.value
  });

};
</script>

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
  z-index: 999;
}

.modal-card {
  background-color: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  width: 90%;
  max-width: 450px;
  padding: 1.5rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
  animation: modalPop 0.3s ease-out;
}

@keyframes modalPop {
  from { opacity: 0; transform: scale(0.95) translateY(-20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

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
