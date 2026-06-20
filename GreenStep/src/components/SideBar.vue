<template>
    <aside class="side-bar">
        <router-link class="button" to="/">
            <img class="icon" src="../../public/dashboard-icon-black.png"/>
            <span>Dashboard</span>
        </router-link>
        <router-link class="button" to="/">
            <span>Log Activity</span>
        </router-link>
        <router-link class="button" to="/">
            <span>Challenges</span>
        </router-link>
        <router-link class="button" to="/">
            <span>Badges</span>
        </router-link>
        <router-link class="button" to="/">
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
        fill: #556b2f;
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
        background-color: var(--primary);
        color: white;
        fill: white;
    }

</style>
