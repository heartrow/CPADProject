<script setup>
import { ref, computed, onMounted } from 'vue'
import TopBar from '@/components/TopBar.vue'
import SideBar from '@/components/SideBar.vue'
import api from '@/api/client.js'

// Chart.js Core Imports
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  PointElement,
  CategoryScale,
  Filler
} from 'chart.js'

// Register Chart.js components
ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  PointElement,
  CategoryScale,
  Filler
)

const rawLogs = ref([])

onMounted(async () => {
  try {
    const response = await api.get('api/activitylogs')
    if (response.data && response.data.data) {
      rawLogs.value = response.data.data
    } else if (Array.isArray(response.data)) {
      rawLogs.value = response.data
    }
  } catch (error) {
    console.error('Error fetching dashboard database records:', error)
  }
})

const generateDateRange = (daysCount) => {
  const dates = []
  for (let i = daysCount - 1; i >= 0; i--) {
    const d = new Date()
    d.setDate(d.getDate() - i)
    dates.push(d.toLocaleDateString('sv')) 
  }
  return dates
}

const chartTimeline = computed(() => generateDateRange(7))

const aggregatedDailyData = computed(() => {
  const recordMap = {}
  rawLogs.value.forEach(log => {
    const rawDate = log.date || log.created_at || ''
    const dateKey = rawDate.split(' ')[0]
    if (dateKey) {
      const carbonValue = parseFloat(log.co2 || log.amount || log.value || 0)
      recordMap[dateKey] = (recordMap[dateKey] || 0) + carbonValue
    }
  })
  return recordMap
})

const todaysFootprint = computed(() => {
  const todayKey = new Date().toLocaleDateString('sv')
  return (aggregatedDailyData.value[todayKey] || 0).toFixed(1)
})

const weeklyAverage = computed(() => {
  const dataValues = Object.values(aggregatedDailyData.value)
  if (dataValues.length === 0) return '0.0'
  const total = dataValues.reduce((sum, val) => sum + val, 0)
  return (total / 7).toFixed(1)
})

const calendarDays = computed(() => {
  const pastThreeWeeks = generateDateRange(21)
  return pastThreeWeeks.map(date => {
    const totalCO2 = aggregatedDailyData.value[date]
    let statusClass = 'empty'

    if (totalCO2 !== undefined && totalCO2 > 0) {
      if (totalCO2 <= 5) statusClass = 'low'
      else if (totalCO2 <= 15) statusClass = 'medium'
      else statusClass = 'high'
    }

    return { date, totalCO2: totalCO2 || 0, statusClass }
  })
})

const chartData = computed(() => ({
  labels: chartTimeline.value.map(date => date.slice(5)), 
  datasets: [
    {
      label: 'CO2 Footprint',
      data: chartTimeline.value.map(date => aggregatedDailyData.value[date] || 0),
      borderColor: '#4E6E52',
      borderWidth: 3,
      tension: 0.4,
      fill: true,
      backgroundColor: 'rgba(78, 110, 82, 0.08)',
      pointRadius: 2,
      pointHoverRadius: 6,
      pointBackgroundColor: '#2E4F32',
      pointBorderColor: '#ffffff',
      pointBorderWidth: 2
    }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#2E4F32',
      titleFont: { family: 'Inter, sans-serif', weight: 'bold' },
      bodyFont: { family: 'Inter, sans-serif' },
      padding: 10,
      cornerRadius: 8,
      displayColors: false
    }
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: '#888888', font: { family: 'Inter, sans-serif', size: 11 } }
    },
    y: {
      beginAtZero: true,
      grid: { display: false }, //  FIX: This completely removes the horizontal lines!
      border: { display: false },
      ticks: { color: '#888888', font: { family: 'Inter, sans-serif', size: 11 }, padding: 8 }
    }
  }
}


const canvasBackgroundColorPlugin = {
  id: 'customCanvasBackgroundColor',
  beforeDraw: (chart) => {
    const { ctx, chartArea } = chart
    if (!chartArea) return

    ctx.save()
    // Resolves and reads var(--primary-light) directly from your live app theme
    const primaryLightColor = getComputedStyle(document.documentElement)
      .getPropertyValue('--primary-light').trim() || '#E8EFE9'

    ctx.fillStyle = primaryLightColor
    ctx.fillRect(
      chartArea.left,
      chartArea.top,
      chartArea.width,
      chartArea.height
    )
    ctx.restore()
  }
}
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
            <div class="chart-container">
              <Line :data="chartData" :options="chartOptions" :plugins="[canvasBackgroundColorPlugin]" />
            </div>
          </div>

          <div class="card" style="flex: 0.8; justify-content: center">
            <h2 class="card-title">Eco-Tip of the Day</h2>
            <div class="tip-box">
              Unplug electronics when not in use. Standby power can account for up to 10% of your total household
              electricity bill!
            </div>
          </div>
        </div>

        <div class="col-right">
          <div class="data-row">
            <div class="card" style="flex: 1">
              <h2 class="card-title">Weekly Data</h2>
              <span class="metric-label">Avg CO2 Logged</span>
              <div class="metric-value">{{ weeklyAverage }} <span style="font-size: 1.4rem">kg</span></div>
            </div>

            <div class="card" style="flex: 1">
              <h2 class="card-title">Daily Data</h2>
              <span class="metric-label">Today's Footprint</span>
              <div class="metric-value">{{ todaysFootprint }} <span style="font-size: 1.4rem">kg</span></div>
            </div>
          </div>

          <div class="card" style="flex: 2">
            <h2 class="card-title">Logging Consistency Calendar</h2>
            <p class="calendar-desc">
              Track continuous logging frequencies. Darker green blocks map smaller carbon impact targets met.
            </p>

            <div class="calendar-matrix-wrapper">
              <div class="heatmap-grid">
                <div
                  v-for="day in calendarDays"
                  :key="day.date"
                  class="heatmap-day"
                  :class="day.statusClass"
                  :title="`${day.date}: ${day.totalCO2.toFixed(1)} kg CO2`"
                ></div>
              </div>
              <div class="matrix-legend">
                <span>No Logs</span>
                <div class="legend-block empty"></div>
                <div class="legend-block high"></div>
                <div class="legend-block medium"></div>
                <div class="legend-block low"></div>
                <span>Ideal Target</span>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="card tip-library">
        <h2 class="card-title">Eco-Tip Library</h2>
        <div class="tip-library-grid">
          <div class="tip-item">
            <div class="tip-icon">💡</div>
            <p>
              <strong>Cold Water Laundry:</strong> Wash clothes in cold water to save the energy used to heat it, keeping
              your garments looking fresh longer.
            </p>
          </div>
          <div class="tip-item">
            <div class="tip-icon">🌱</div>
            <p>
              <strong>Plant-Based Meals:</strong> Swap one meat-based meal a week for a plant-based alternative to
              significantly lower your carbon footprint.
            </p>
          </div>
          <div class="tip-item">
            <div class="tip-icon">🚴</div>
            <p>
              <strong>Active Transport:</strong> Try cycling or walking for short trips instead of driving to reduce emissions
              and improve health.
            </p>
          </div>
        </div>
      </div>

    </div>
  </main>
</template>

<style>
.home-main {
  color: var(--text-main);
  background-color: var(--bg-body);
  min-height: 100vh;
  padding: 2rem;
  margin-left: 225px;
}

.card {
  background-color: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.03);
}

.card-title {
  margin-top: 0;
  margin-bottom: 1rem;
  font-size: 1.2rem;
  color: var(--text-main);
}

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

.chart-container {
  position: relative;
  width: 100%;
  height: 240px;
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
}

.calendar-matrix-wrapper {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: auto;
}

.heatmap-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.5rem;
  max-width: 280px;
}

.heatmap-day {
  aspect-ratio: 1;
  border-radius: 4px;
  transition: transform 0.1s ease;
}

.heatmap-day:hover {
  transform: scale(1.15);
  cursor: pointer;
}

.heatmap-day.empty { background-color: rgba(0, 0, 0, 0.05); }
.heatmap-day.high { background-color: #c2d5bb; }
.heatmap-day.medium { background-color: #7a9a7e; }
.heatmap-day.low { background-color: #2e4f32; }

.matrix-legend {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  color: var(--text-muted);
}

.legend-block {
  width: 12px;
  height: 12px;
  border-radius: 2px;
}
.legend-block.empty { background-color: rgba(0, 0, 0, 0.05); }
.legend-block.high { background-color: #c2d5bb; }
.legend-block.medium { background-color: #7a9a7e; }
.legend-block.low { background-color: #2e4f32; }

.tip-library {
  margin-top: 2.5rem;
}
.tip-library-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}
.tip-item {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  background-color: var(--primary-light);
  padding: 1.25rem;
  border-radius: 10px;
  border-left: 4px solid var(--primary);
}
.tip-icon {
  font-size: 1.5rem;
  line-height: 1;
}
.tip-item p {
  margin: 0;
  font-size: 0.95rem;
  line-height: 1.5;
  color: var(--text-main);
}
.tip-item strong {
  display: block;
  margin-bottom: 0.25rem;
  color: var(--primary);
}

@media (max-width: 768px) {
  .home-main { margin-left: 0; padding: 1rem; padding-bottom: 90px; }
  .dashboard-grid, .col-left, .col-right, .data-row { display: flex; flex-direction: column; gap: 1rem; }
  .card { padding: 1.25rem; flex: auto !important; }
  .card-title { font-size: 1rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; }
  .tip-library { margin-top: 1rem; }
  .tip-library-grid { grid-template-columns: 1fr; gap: 1rem; }
  .chart-container { position: relative; width: 100%; height: 220px; }
}
</style>