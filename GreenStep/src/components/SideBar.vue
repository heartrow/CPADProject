<script setup>
import { ref } from 'vue'
</script>

<template>
    <aside class="side-bar">
        <router-link class="button" to="/dashboard">
            <img class="icon" src='../assets/dashboard-icon-black.png'/>
            <span>Dashboard</span>
        </router-link>

        <router-link class="button" to="/activity">
            <img class="icon" src='../assets/activity-icon-black.png'/>
            <span>Log Activity</span>
        </router-link>

        <router-link class="button" to="/challenges">
            <img class="icon" src='../assets/challenges-icon-black.png'/>
            <span>Challenges</span>
        </router-link>

        <router-link class="button" to="/profile">
            <img class="icon" src='../assets/profile-icon-black.png'/>
            <span>Profile</span>
        </router-link>
    </aside>
</template>

<style scoped>
/* Default Desktop Styles */
.side-bar {
  left: 0;
  top: 60px;
  background-color: var(--bg-card);
  position: fixed;
  height: calc(100vh - 60px);
  width: 175px;
  padding: 25px 15px;
  font-size: 16px;
  font-weight: bold;
  border-right: 1px solid var(--border);
  z-index: 20;
}

.side-bar .icon {
  display: inline;
  margin-right: 10px;
  width: 22px;
}

.side-bar .button {
  text-decoration: none;
  color: var(--text-main);
  padding: 10px;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  border-radius: 7px;
  transition: background 0.2s;
}

.side-bar .button:hover,
.side-bar .router-link-active {
  background-color: var(--primary-light);
  color: var(--primary);
}

/* Mobile Styles: Transforms into Bottom Navigation */
@media (max-width: 768px) {
  .side-bar {
    top: auto;
    bottom: 0;
    width: 100%;
    height: 70px;
    padding: 0;
    display: flex;
    justify-content: space-around;
    align-items: center;
    border-right: none;
    border-top: 1px solid #e0e0e0;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
  }

  .side-bar .button {
    flex-direction: column;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
    padding: 5px;
    margin-bottom: 0;
    color: #666;
    flex: 1;
    text-align: center;
  }

  .side-bar .icon {
    margin-right: 0;
    margin-bottom: 4px;
    width: 22px;
    height: 22px;
  }

  .side-bar .router-link-active {
    background-color: transparent;
    color: var(--primary);
  }
}
</style>
