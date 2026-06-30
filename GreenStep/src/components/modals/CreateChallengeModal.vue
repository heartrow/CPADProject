<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/client.js'

const props = defineProps({
  show: Boolean
})

const emit = defineEmits(['close', 'challengeCreated'])

const title = ref('')
const description = ref('')
const targetGoal = ref('')
const activityTypeId = ref('')
const unit = ref('')
const startDate = ref('')
const endDate = ref('')

const activityTypes = ref([])
const isLoadingTypes = ref(false)
const typesError = ref('')

const isSubmitting = ref(false)
const modalError = ref('')

const fetchActivityTypes = async () => {
  isLoadingTypes.value = true
  typesError.value = ''
  try {
    // Assumes GET /api/activitytypes exists, mirroring the /api/challenges pattern.
    // Update this path if the real route differs.
    const response = await api.get('/api/activitytypes')
    activityTypes.value = response.data?.data ?? response.data ?? []
  } catch (error) {
    console.error('Failed to load activity types:', error)
    typesError.value = 'Could not load activity types.'
  } finally {
    isLoadingTypes.value = false
  }
}

onMounted(fetchActivityTypes)

const handleActivityTypeChange = () => {
  const selected = activityTypes.value.find(
    (t) => String(t.id) === String(activityTypeId.value)
  )
  unit.value = selected ? selected.unit : ''
}

const handleSubmit = async () => {
  if (!title.value || !targetGoal.value || !activityTypeId.value || !startDate.value || !endDate.value) {
    modalError.value = 'Please fill in all required fields.'
    return
  }

  if (new Date(endDate.value) < new Date(startDate.value)) {
    modalError.value = 'End date must be on or after the start date.'
    return
  }

  isSubmitting.value = true
  modalError.value = ''

  try {
    const response = await api.post('/api/challenges', {
      title: title.value,
      description: description.value,
      targetGoal: Number(targetGoal.value),
      unit: unit.value,
      activity_type_id: Number(activityTypeId.value),
      start_date: startDate.value,
      end_date: endDate.value
    })

    if (response.data.success) {
      title.value = ''
      description.value = ''
      targetGoal.value = ''
      activityTypeId.value = ''
      unit.value = ''
      startDate.value = ''
      endDate.value = ''

      emit('challengeCreated')
      emit('close')
    }
  } catch (error) {
    console.error("Failed to create challenge:", error)
    modalError.value = error.response?.data?.error || 'Failed to create challenge. Please try again.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div v-if="show" class="modal-overlay" @click.self="emit('close')">
    <div class="modal-card">
      <div class="modal-header">
        <h3>⚡ Create New Challenge</h3>
        <button class="close-btn" @click="emit('close')">✖</button>
      </div>

      <form @submit.prevent="handleSubmit" class="modal-body">
        <div v-if="modalError" class="error-msg">{{ modalError }}</div>

        <div class="form-group">
          <label for="title">Challenge Title *</label>
          <input id="title" v-model="title" type="text" placeholder="e.g., Campus Carpool Initiative" required />
        </div>

        <div class="form-group">
          <label for="desc">Description</label>
          <textarea id="desc" v-model="description" placeholder="Describe the community goal..." rows="3"></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="goal">Target Goal *</label>
            <input id="goal" v-model="targetGoal" type="number" min="1" placeholder="1000" required />
          </div>

          <div class="form-group">
            <label for="activityType">Activity Type *</label>
            <select
              id="activityType"
              v-model="activityTypeId"
              @change="handleActivityTypeChange"
              required
            >
              <option value="" disabled>
                {{ isLoadingTypes ? 'Loading...' : 'Select an activity type' }}
              </option>
              <option
                v-for="type in activityTypes"
                :key="type.id"
                :value="type.id"
              >
                {{ type.name }} ({{ type.unit }})
              </option>
            </select>
            <span v-if="typesError" class="field-error">{{ typesError }}</span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="startDate">Start Date *</label>
            <input id="startDate" v-model="startDate" type="date" required />
          </div>

          <div class="form-group">
            <label for="endDate">End Date *</label>
            <input id="endDate" v-model="endDate" type="date" required />
          </div>
        </div>

        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="emit('close')">Cancel</button>
          <button type="submit" class="btn-submit" :disabled="isSubmitting">
            {{ isSubmitting ? 'Creating...' : 'Create Challenge' }}
          </button>
        </div>
      </form>
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
  /* 🔴 CRITICAL: Forces modal above EVERYTHING else */
  backdrop-filter: blur(4px);
}

.modal-card {
  background-color: var(--bg-card, #ffffff);
  width: 90%;
  max-width: 550px;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  animation: modalFadeIn 0.2s ease-out;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
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
}

.modal-body {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.error-msg {
  background-color: #ffeaea;
  color: #d9534f;
  padding: 0.75rem;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.9rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

label {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text-main);
}

input,
textarea,
select {
  padding: 0.75rem;
  border: 1px solid var(--border, #eee);
  background-color: var(--bg-body, #fafafa);
  color: var(--text-main);
  border-radius: 6px;
  font-size: 0.95rem;
}

input:focus,
textarea:focus,
select:focus {
  border-color: var(--primary);
  outline: none;
}

.field-error {
  color: #d9534f;
  font-size: 0.8rem;
  font-weight: 600;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.btn-cancel {
  background: transparent;
  border: 1px solid var(--border);
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  cursor: pointer;
  color: var(--text-muted);
  font-weight: 600;
}

.btn-submit {
  background-color: var(--primary);
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
