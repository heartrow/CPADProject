<script setup>
import { useRouter } from 'vue-router'
import { useAuth } from '../stores/auth';

const router = useRouter()
const auth = useAuth()

const handleLogout = () => {
  auth.logout()
  router.push('/')
}
</script>

<template>
    <div class="page-header">
        <h1>GreenStep</h1>
        <button class="logout-btn" @click="handleLogout">
           Log Out
        </button>
    </div>
</template>

<style scoped>
    .page-header {
        background: var(--primary);
        height: 60px;
        color: white;
        padding: 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 10;
        position: sticky;
        top: 0;
        z-index: 30;
    }

    .logout-btn {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.5);
        padding: 0.4rem 1rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    h1 {
        font-size: 20px;
        margin: 0;
        font-weight: bold;
    }

</style>
