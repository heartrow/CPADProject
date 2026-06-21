<script async setup>
import TopBar from '@/components/TopBar.vue'
import SideBar from '@/components/SideBar.vue'
</script>

<template>
  <TopBar />
  <SideBar />
  <main class="home-main">
    <div id="view-dashboard" class="view-section active">
      <div class="dashboard-grid">
        <div class="col-left">
          <div class="card" style="flex: 2">
            <h2 class="card-title">Carbon Footprint Performance Trend</h2>
            <div class="chart-render-space">[ Chart.js Rendering Space ]</div>
          </div>

          <div class="card" style="flex: 0.8; justify-content: center">
            <h2 class="card-title">Eco-Tip of the Day</h2>
            <div class="tip-box">
              Unplug electronics when not in use. Standby power can account for up to 10% of your
              total household electricity bill!
            </div>
          </div>
        </div>

        <div class="col-right">
          <div class="data-row">
            <div class="card" style="flex: 1">
              <h2 class="card-title">Weekly Data</h2>
              <span class="metric-label">Avg CO2 Logged</span>
              <div class="metric-value">42.5 <span style="font-size: 1.4rem">kg</span></div>
            </div>

            <div class="card" style="flex: 1">
              <h2 class="card-title">Daily Data</h2>
              <span class="metric-label">Today's Footprint</span>
              <div class="metric-value">6.1 <span style="font-size: 1.4rem">kg</span></div>
            </div>
          </div>

          <div class="card" style="flex: 2">
            <h2 class="card-title">Logging Consistency Calendar</h2>
            <p class="calendar-desc">
              Track continuous logging frequencies. Darker green blocks map smaller carbon impact
              targets met.
            </p>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<style>
.home-main {
  color: var(--text-main); /* Using your global variables */
  background-color: var(--bg-body);
  min-height: 100vh;
  padding: 2rem;
  /* Add a left margin here if your SideBar is fixed to the left of the screen */
  margin-left: 225px;
}

/* Base Card Styling (Derived from your color palette) */
.card {
  background-color: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.card-title {
  margin-top: 0;
  margin-bottom: 1rem;
  font-size: 1.2rem;
  color: var(--text-main);
}

/* --- 5. DASHBOARD VIEW STYLES --- */
.dashboard-grid {
  display: flex;
  gap: 2.5rem;
}

.col-left {
  flex: 1.25;
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
}

.col-right {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
}

.chart-render-space {
  background: rgba(255, 255, 255, 0.35);
  border: 2px dashed var(--border);
  border-radius: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
  color: var(--text-muted);
  font-weight: 600;
  font-size: 1.1rem;
  flex-grow: 1;
  min-height: 200px;
}

.tip-box {
  background-color: var(--primary-light);
  border-left: 4px solid var(--primary);
  padding: 1.25rem;
  border-radius: 0 10px 10px 0;
  font-size: 1.05rem;
  line-height: 1.6;
  font-weight: 500;
  color: var(--text-main);
}

.data-row {
  display: flex;
  gap: 2.5rem;
}

.metric-value {
  font-size: 2rem;
  font-weight: 800;
  letter-spacing: -1px;
  color: var(--primary);
  margin-top: 0.25rem;
}

.metric-label {
  font-size: 0.95rem;
  color: var(--text-muted);
  font-weight: 500;
}

.calendar-desc {
  font-size: 0.95rem;
  color: var(--text-muted);
  margin-bottom: 1.25rem;
  background: transparent;
}

@media (max-width: 768px) {
  .home-main {
    margin-left: 0;
    padding: 1rem;
    padding-bottom: 90px;
    background-color: var(--bg-body)
  }

  .dashboard-grid,
  .col-left,
  .col-right,
  .data-row {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .card {
    background-color: var(--bg-card);
    border: 1px solid #e0e0e0;
    box-shadow: none;
    padding: 1.25rem;
    flex: auto !important;
  }

  .card-title {
    font-size: 1rem;
    color: var(--text-main);
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.5px;
  }
}
</style>
