<template>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <h2>Create Account</h2>
        <p>Join GreenStep and start tracking your carbon footprint.</p>
      </div>

      <form @submit.prevent="submit" class="login-form">
        <p v-if="error" class="alert error">{{ error }}</p>

        <div class="input-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" v-model="name" placeholder="Ahmad bin Ali" autocomplete="name" required />
        </div>

        <div class="input-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" v-model="email" placeholder="you@example.com" autocomplete="email" required />
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" v-model="password" placeholder="••••••••" autocomplete="new-password" required />
        </div>

        <div class="input-group">
          <label for="confirmPassword">Confirm Password</label>
          <input type="password" id="confirmPassword" v-model="confirmPassword" placeholder="••••••••" autocomplete="new-password" required />
        </div>

        <button type="submit" class="submit-btn" :disabled="busy">
          {{ busy ? 'Creating account...' : 'Create Account' }}
        </button>
      </form>

      <div class="login-footer">
        <p>Already have an account? <router-link to="/">Log in here</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/stores/auth';

const auth = useAuth();
const router = useRouter();

const name            = ref('');
const email           = ref('');
const password        = ref('');
const confirmPassword = ref('');
const error           = ref('');
const busy            = ref(false);

async function submit() {
  error.value = '';

  if (password.value !== confirmPassword.value) {
    error.value = 'Passwords do not match.';
    return;
  }

  if (password.value.length < 6) {
    error.value = 'Password must be at least 6 characters.';
    return;
  }

  busy.value = true;
  try {
    await auth.register(name.value, email.value, password.value)
    router.push('/');
  } catch (e) {
    const d = e.response?.data;
    error.value = d?.errors
      ? Object.values(d.errors).join(' • ')
      : (d?.error || e.message);
  } finally {
    busy.value = false;
  }
}
</script>

<style scoped>
.login-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: var(--bg-body);
  padding: 1rem;
}

.login-card {
  background-color: var(--primary);
  --border: 2px solid rgba(from var(--primary-light) r g b / .2);
  border-radius: 12px;
  padding: 2.5rem;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h2 {
  color: whitesmoke;
  margin: 0 0 0.5rem 0;
  font-size: 1.75rem;
}

.login-header p {
  color: whitesmoke;
  margin: 0;
  font-size: 0.95rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.input-group label {
  color: whitesmoke;
  font-weight: 600;
  font-size: 0.9rem;
}

.input-group input {
  padding: 0.75rem;
  border: 1px solid var(--border);
  border-radius: 6px;
  background-color: var(--primary-light);
  color: var(--text-main);
  font-size: 1rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.input-group input::placeholder {
  color: var(--text-muted);
  opacity: 0.7;
}

.input-group input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(85, 107, 47, 0.2);
}

.submit-btn {
  background-color: var(--bg-body);
  color: whitesmoke;
  border: none;
  border-radius: 6px;
  padding: 0.85rem;
  font-size: 1rem;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.2s, transform 0.1s;
  margin-top: 0.5rem;
}

.submit-btn:hover {
  background-color: #1a140d;
}

.submit-btn:active {
  transform: scale(0.98);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.login-footer {
  text-align: center;
  margin-top: 2rem;
  font-size: 0.9rem;
  color: whitesmoke;
}

.login-footer a {
  color: whitesmoke;
  text-decoration: none;
  font-weight: 600;
}

.login-footer a:hover {
  text-decoration: underline;
}

.alert.error {
  background: #fde8e8;
  color: #ff0000;
  padding: 10px 14px;
  border-radius: 6px;
  border: 1px solid #ff0000;
  margin-bottom: 12px;
}
</style>