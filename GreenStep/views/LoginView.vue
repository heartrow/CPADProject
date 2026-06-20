<template>
  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-header">
        <h2>Welcome Back</h2>
        <p>Please log in to continue.</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="input-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" v-model="email" placeholder="you@example.com" required />
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" v-model="password" placeholder="••••••••" required />
        </div>

        <div class="form-actions">
          <label class="remember-me">
            <input type="checkbox" v-model="remember" />
            <span>Remember me</span>
          </label>
          <a href="#" class="forgot-password">Forgot password?</a>
        </div>

        <button type="submit" class="submit-btn">Log In</button>
      </form>

      <div class="login-footer">
        <p>Don't have an account? <a href="#">Sign up here</a></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import router from '../src/router'
import '../src/style.css'


const email = ref('')
const password = ref('')
const remember = ref(false)

const handleLogin = () => {
  console.log('Login attempt:', {
    email: email.value,
    password: password.value,
    remember: remember.value,
  })

  router.push('/home')
}
</script>

<style scoped>
.login-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: var(--bg-body);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  padding: 1rem;
}

.login-card {
  background-color: var(--primary);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 2.5rem;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h2 {
  color: var(--text-main);
  margin: 0 0 0.5rem 0;
  font-size: 1.75rem;
}

.login-header p {
  color: var(--text-muted);
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
  color: var(--text-main);
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
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
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

.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
}

.remember-me {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-muted);
  cursor: pointer;
}

.forgot-password {
  color: var(--accent);
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s;
}

.forgot-password:hover {
  color: var(--primary);
}

.submit-btn {
  background-color: var(--bg-body);
  color: var(--primary-light);
  border: none;
  border-radius: 6px;
  padding: 0.85rem;
  font-size: 1rem;
  font-weight: bold;
  cursor: pointer;
  transition:
    background-color 0.2s,
    transform 0.1s;
  margin-top: 0.5rem;
}

.submit-btn:hover {
  background-color: #1a140d;
}

.submit-btn:active {
  transform: scale(0.98);
}

.login-footer {
  text-align: center;
  margin-top: 2rem;
  font-size: 0.9rem;
  color: var(--text-muted);
}

.login-footer a {
  color: var(--accent);
  text-decoration: none;
  font-weight: 600;
}

.login-footer a:hover {
  color: var(--primary);
  text-decoration: underline;
}
</style>
