<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-card">
      <div class="modal-header">
        <h2>🍔 Log a Meal</h2>
        <button class="close-btn" @click="$emit('close')">✕</button>
      </div>

      <form @submit.prevent="submitLog" class="modal-body">

        <div class="form-group">
          <label>Main Ingredient</label>
          <select v-model="selected" placeholder="Select main ingredient" @change="updateForm">
            <option value="" disabled>Select main ingredient</option>
            <option v-for="m in meals" :key="m.id" :value="m">{{ m.name }}</option>
          </select>
        </div>

        <div class="form-group">
          <label>Estimated Weight (kg)</label>
          <input type="number" v-model="weight" placeholder="e.g., 2.5" step="0.1" required @change="updateForm"/>
        </div>

        <div class="modal-footer">
          <button type="button" class="cancel-btn" @click="$emit('close')">Cancel</button>
          <button type="submit" class="submit-btn" :disabled="busy">Log Meal</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script async setup>
  import { ref, onMounted } from 'vue';
  import api from '../../api/client';
  import { useAuth } from '../../stores/auth';
import router from '@/router';

  defineEmits(['close']);

  const selected = ref(null);
  const weight = ref(Number());
  const form = ref({ 
    id: 0, 
    category: 'meal', 
    name: '', 
    amount: 0, 
  });
  const meals   = ref([]);
  const q       = ref('meal');
  const error   = ref('');
  const ok      = ref('');  
  const loading = ref(false);
  const busy = ref(false);

  async function submitLog() {
    error.value = ''; 
    ok.value = '';
    busy.value = true;

    try {
      const payload = {
        activity_type_id: Number(form.value.id), 
        amount: Number(form.value.amount)
      };

      const { data } = await api.post('/api/activitylogs', payload);

      ok.value = 'Activity logged successfully!';

      // Optional: Auto-wipe the quantity input so they can log another one
      form.value.amount = ''; 

      // 3. Change 'dashboard' to whatever your main History/Ledger route is named
      router.push("/activity");

    } catch (e) {
      const d = e.response?.data;
      error.value = d?.errors ? Object.values(d.errors).join(' • ') : (d?.error || e.message);
    } finally {
      busy.value = false;
    }
  }

  async function updateForm() {
    form.value.amount = weight.value;

    if (!selected.value)
      console.log('No selected option');

    form.value.id = selected.value.id;
  }

  async function load() {
    error.value = '';
    loading.value = true;
    try {
      const { data } = await api.get('/api/activitytypes', { params: { q: q.value || undefined } });
      meals.value = data.data;
    } catch (e) {
      error.value = e.response?.data?.error || e.message;
    } finally {
      loading.value = false;
    }
  }

  onMounted(load);
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
