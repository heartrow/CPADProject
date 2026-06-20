<script setup>
    import { ref } from 'vue';

    const isHovered = ref({
        dashboard: false,
        activity: false,
        challenges: false,
        badges: false,
        profile: false,
    })
</script>

<template>
    <aside class="side-bar">
        <router-link class="button" to="/">
            <img class="icon" src='/dashboard-icon-black.png'/>
            <span>Dashboard</span>
        </router-link>
        <router-link class="button" to="/">
            <img class="icon" src='/activity-icon-black.png'/>
            <span>Log Activity</span>
        </router-link>
        <router-link class="button" to="/">
            <img class="icon" src='/challenges-icon-black.png'/>
            <span>Challenges</span>
        </router-link>
        <router-link class="button" to="/">
            <img class="icon" src='/badges-icon-black.png'/>
            <span>Badges</span>
        </router-link>
        <router-link class="button" to="/">
            <img class="icon" src='/profile-icon-black.png'/>
            <span>Profile</span>
        </router-link>
    </aside>
</template>

<style>
    .side-bar {
        left: 0;
        background-color: var(--bg-card);
        position: fixed;
        height: 100%;
        width: 175px;
        padding: 25px;
        font-size: 17px;
        font-family: Arial, Helvetica, sans-serif;
        font-weight: bold;
    }

     .side-bar .icon {
        display: inline;
        padding: 0 10px 0 10px;
        width: 22px;
    }


    .side-bar .button {
        text-decoration: none;
        color: black;
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        height: 35px;
        border-radius: 7px;
    }

    .side-bar .button:hover {
        background-color: var(--primary-light);
    }

</style>
