<template>
  <div class="modal-overlay" @click.self="$emit('closeModal')">
    <div class="modal-card">
      <div class="modal-header">
        <h2>🚗 Log Transport</h2>
        <button class="close-btn" @click="$emit('closeModal')">✕</button>
      </div>

      <form @submit.prevent="submitLog" class="modal-body">

        <div class="form-group">
          <label>Title</label>
          <input v-model='form.title' placeholder="Commuting to work" type="string" required>
        </div>

        <div class="form-group">
          <label>Vehicle Type</label>
          <select v-model="form.type_id">
            <option v-for="o in options" :key="o.id" :value="o.id">{{ o.name }}</option>
          </select>
        </div>

        <div class="form-group">
          <label>Distance Traveled (km)</label>
          <input type="number" v-model="form.amount" placeholder="e.g., 15" required @change="updateForm"/>
        </div>

        <div class="modal-footer">
          <button type="button" class="cancel-btn" @click="$emit('closeModal')">Cancel</button>
          <button type="submit" class="submit-btn" :disabled="busy">{{ busy ? 'Logging...' : editLog ? 'Update Log' : 'Log'  }}</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script async setup>
  import { ref, onMounted, defineEmits } from 'vue';
  import api from '../../api/client';

  const emit = defineEmits(['closeModal', 'logSubmitted'])
  const props = defineProps(['editLog', 'options'])
  const activityType = 'transport';

  const selectedOption = ref(null);
  const form = ref({ 
    id: 0,
    type_id: 0, 
    category: activityType, 
    title: '',
    amount: '', 
  });
  const error   = ref('');
  const busy = ref(false);

  async function submitLog() {
    error.value = '';
    busy.value = true;

    try {
      const payload = {
        activity_type_id: Number(form.value.type_id),
        title: form.value.title,
        amount: Number(form.value.amount)
      };

      if (props.editLog) {
        console.log(payload);
        await api.put(`/api/activitylogs/${props.editLog.id}`, payload);
      } else {
        await api.post('/api/activitylogs', payload);
      }

      selectedOption.value = null;

      form.value.id = 0;
      form.value.type_id = 0;
      form.value.amount = '';
      form.value.title = '';

      emit('logSubmitted');
      emit('closeModal');

    } catch (e) {
      const d = e.response?.data;
      error.value = d?.errors ? Object.values(d.errors).join(' • ') : (d?.error || e.message);
    } finally {
      busy.value = false;
    }
  }

  
  onMounted(() => {
    if (props.editLog) {
      form.value.id = props.editLog.id;
      form.value.type_id = props.editLog.activity_type_id;
      form.value.title = props.editLog.title;
      form.value.amount = props.editLog.amount;
    }
  })
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