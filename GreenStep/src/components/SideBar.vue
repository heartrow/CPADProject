<template>
    <aside class="side-bar">
        <router-link class="button" to="/">
            <svg class="icon">
                <image src="../../public/layout-dashboard.svg"></image>
            </svg>
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
        background-color: #d7c9b1;
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
    }

   
    .side-bar .button {
        text-decoration: none;
        color: #282014;
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        height: 35px;
        border-radius: 7px;
    }

    .side-bar .button:hover {
        background-color: #556b2f;
        color: white;
        fill: white;
    }

</style>