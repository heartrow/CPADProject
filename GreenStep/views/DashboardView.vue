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
const ecoTips = ref([]) 
const libraryTips = ref([]) // Holds persistent randomized library tips selected at mount
const isLoading = ref(true)

// Contextual eco emoji bank
const libraryIcons = ['💡', '🌱', '🚴', '💧', '🔌', '🍃', '🌍', '♻️']

onMounted(async () => {
  isLoading.value = true

  // 1. FETCH ACTIVITY LOGS (Completely Independent)
  try {
    const logsResponse = await api.get('api/activitylogs')
    if (logsResponse.data && logsResponse.data.data) {
      rawLogs.value = logsResponse.data.data
    } else if (Array.isArray(logsResponse.data)) {
      rawLogs.value = logsResponse.data
    }
  } catch (error) {
    console.error('❌ Activity logs failed to load:', error)
    // Non-blocking: chart components handles empty data array gracefully
  }

  // 2. FETCH ECO TIPS (Independent with Fallback safety net)
  try {
    const tipsResponse = await api.get('api/ecotips')
    if (tipsResponse.data && tipsResponse.data.data) {
      ecoTips.value = tipsResponse.data.data
    } else if (Array.isArray(tipsResponse.data)) {
      ecoTips.value = tipsResponse.data
    }
  } catch (error) {
    console.warn('⚠️ Eco-tips failed due to CORS or Network Error. Applying clean UI fallbacks.')
    
    // Fallback records preserve layout continuity if backend connection fails
    ecoTips.value = [
      'Unplug electronics when not in use. Standby power can account for up to 10% of your total household electricity bill!',
      'Wash clothes in cold water. About 75% to 90% of all the energy your washing machine uses goes solely into heating the water.',
      'Swap out your home’s remaining incandescent bulbs for LEDs. They use up to 75% less energy and last 25 times longer.',
      'Cut your shower time down to 5 minutes. This small change can save up to 1,000 gallons of water per person every month.',
      'Compost your fruit peels and vegetable scraps. Composting prevents harmful methane production and creates nutrient-rich soil.'
    ]
  }

  // 3. GENERATE THE ECO-TIP LIBRARY GRID ITEMS
  if (ecoTips.value.length > 0) {
    const shuffled = [...ecoTips.value].sort(() => 0.5 - Math.random())
    libraryTips.value = shuffled.slice(0, 3).map((item, index) => {
      const rawText = typeof item === 'object' && item !== null ? (item.tip_text || item.tip) : item
      
      // Extract first two words to generate structural header tags
      let generatedTitle = 'Eco Action:'
      if (rawText) {
        const words = rawText.split(' ')
        generatedTitle = words.slice(0, 2).join(' ').replace(/[.,\/#!$%\^&\*;:{}=\-_`~()]/g, "") + ':'
      }

      return {
        id: item.id || index,
        icon: libraryIcons[index % libraryIcons.length],
        title: generatedTitle,
        text: rawText
      }
    })
  }

  // Always stop loading phase 
  isLoading.value = false
})

const formatToLocalYMD = (dateObj) => {
  const year = dateObj.getFullYear()
  const month = String(dateObj.getMonth() + 1).padStart(2, '0')
  const day = String(dateObj.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

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
    const dateKey = rawDate.substring(0, 10)
    if (dateKey) {
      const carbonValue = parseFloat(log.co2_emission || 0)
      recordMap[dateKey] = (recordMap[dateKey] || 0) + carbonValue
    }
  })
  return recordMap
})

const todaysFootprint = computed(() => {
  const todayKey = formatToLocalYMD(new Date())
  return (aggregatedDailyData.value[todayKey] || 0).toFixed(1)
})

const weeklyAverage = computed(() => {
  const last7Days = chartTimeline.value
  const total = last7Days.reduce(
    (sum, date) => sum + (aggregatedDailyData.value[date] || 0),
    0
  )
  return (total / last7Days.length).toFixed(1)
})

const trendDirection = computed(() => {
  const days = chartTimeline.value
  const todayValue = aggregatedDailyData.value[days[days.length - 1]] || 0
  const priorDays = days.slice(0, -1)
  const priorValues = priorDays.map(d => aggregatedDailyData.value[d] || 0)
  const priorAvg = priorValues.length
    ? priorValues.reduce((a, b) => a + b, 0) / priorValues.length
    : 0

  if (priorAvg === 0 && todayValue === 0) return { label: 'No data yet', class: 'flat' }

  const diff = todayValue - priorAvg
  const threshold = Math.max(priorAvg * 0.05, 0.1)

  if (diff > threshold) return { label: 'Trending up', class: 'up' }
  if (diff < -threshold) return { label: 'Trending down', class: 'down' }
  return { label: 'Holding steady', class: 'flat' }
})

const calendarDays = computed(() => {
  const pastThreeWeeks = generateDateRange(21)
  const todayKey = formatToLocalYMD(new Date())

  return pastThreeWeeks.map(date => {
    const totalCO2 = aggregatedDailyData.value[date]
    let statusClass = 'empty'

    if (totalCO2 !== undefined && totalCO2 > 0) {
      if (totalCO2 <= 5) statusClass = 'low'
      else if (totalCO2 <= 15) statusClass = 'medium'
      else statusClass = 'high'
    }

    return {
      date,
      totalCO2: totalCO2 || 0,
      statusClass,
      dayLabel: Number(date.slice(8, 10)),
      isToday: date === todayKey
    }
  })
})

const chartData = computed(() => ({
  labels: chartTimeline.value.map(date => date.slice(5)),
  datasets: [
    {
      label: 'CO2 Footprint',
      data: chartTimeline.value.map(date => aggregatedDailyData.value[date] || 0),
      borderColor: '#2E4F32',
      borderWidth: 3,
      tension: 0.35,
      cubicInterpolationMode: 'monotone',
      fill: true,
      backgroundColor: 'rgba(46, 79, 50, 0.12)',
      pointRadius: 3,
      pointHoverRadius: 7,
      pointBackgroundColor: '#ffffff',
      pointBorderColor: '#2E4F32',
      pointBorderWidth: 2,
      pointHoverBackgroundColor: '#2E4F32',
      pointHoverBorderColor: '#ffffff',
      pointHoverBorderWidth: 2
    }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: { mode: 'index', intersect: false },
  animation: { duration: 800, easing: 'easeOutQuart' },
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#2E4F32',
      titleFont: { family: 'Inter, sans-serif', weight: 'bold', size: 13 },
      bodyFont: { family: 'Inter, sans-serif', size: 13 },
      padding: 12,
      cornerRadius: 10,
      displayColors: false,
      caretSize: 6,
      callbacks: { label: (context) => `${context.parsed.y.toFixed(1)} kg CO2` }
    }
  },
  scales: {
    x: {
      grid: { display: false },
      border: { display: false },
      ticks: { color: '#7d8a7f', font: { family: 'Inter, sans-serif', size: 11, weight: '600' }, padding: 6 }
    },
    y: {
      beginAtZero: true,
      grid: { color: 'rgba(46, 79, 50, 0.1)', borderDash: [4, 4], drawTicks: false },
      border: { display: false },
      ticks: { color: '#7d8a7f', font: { family: 'Inter, sans-serif', size: 11 }, padding: 10, maxTicksLimit: 5 }
    }
  }
}

let cachedPrimaryLightColor = null
const getPrimaryLightColor = () => {
  if (cachedPrimaryLightColor) return cachedPrimaryLightColor
  cachedPrimaryLightColor = getComputedStyle(document.documentElement)
    .getPropertyValue('--primary-light').trim() || '#E8EFE9'
  return cachedPrimaryLightColor
}

const canvasBackgroundColorPlugin = {
  id: 'customCanvasBackgroundColor',
  beforeDraw: (chart) => {
    const { ctx, chartArea } = chart
    if (!chartArea) return
    ctx.save()
    ctx.fillStyle = getPrimaryLightColor()
    ctx.fillRect(chartArea.left, chartArea.top, chartArea.width, chartArea.height)
    ctx.restore()
  }
}

const gradientFillPlugin = {
  id: 'gradientFillPlugin',
  beforeDatasetsDraw: (chart) => {
    const { ctx, chartArea } = chart
    if (!chartArea) return
    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom)
    gradient.addColorStop(0, 'rgba(46, 79, 50, 0.28)')
    gradient.addColorStop(1, 'rgba(46, 79, 50, 0)')
    chart.data.datasets.forEach((dataset) => { dataset.backgroundColor = gradient })
  }
}

// 24-hour locked index system pulling array indexes safely
const tipOfTheDay = computed(() => {
  if (!ecoTips.value.length) return 'Loading strategy insights...'
  const today = new Date()
  const uniqueDayIdentifier = today.getFullYear() * 10000 + (today.getMonth() + 1) * 100 + today.getDate()
  const tipIndex = uniqueDayIdentifier % ecoTips.value.length
  const row = ecoTips.value[tipIndex]
  return typeof row === 'object' && row !== null ? (row.tip_text || row.tip) : row
})
</script>

<template>
  <TopBar />
  <SideBar />
  <main class="home-main">
    <div id="view-dashboard" class="view-section active">

      <div v-if="isLoading" class="loading-state">
        Loading dashboard metrics...
      </div>

      <template v-else>
        <div class="dashboard-grid">

          <div class="col-left">
            <div class="card" style="flex: 2">
              <div class="chart-header">
                <h2 class="card-title">Carbon Footprint Performance Trend</h2>
                <span class="trend-badge" :class="trendDirection.class">
                  <svg v-if="trendDirection.class === 'up'" class="trend-icon" viewBox="0 0 16 16" fill="none">
                    <path d="M2 11L6.5 6.5L9.5 9.5L14 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 4H14V8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <svg v-else-if="trendDirection.class === 'down'" class="trend-icon" viewBox="0 0 16 16" fill="none">
                    <path d="M2 5L6.5 9.5L9.5 6.5L14 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 12H14V8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                  <svg v-else class="trend-icon" viewBox="0 0 16 16" fill="none">
                    <path d="M2 8H14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                  </svg>
                  {{ trendDirection.label }}
                </span>
              </div>
              <div class="chart-container">
                <Line :data="chartData" :options="chartOptions" :plugins="[canvasBackgroundColorPlugin, gradientFillPlugin]" />
              </div>
            </div>

            <div class="card" style="flex: 0.8; justify-content: center">
              <h2 class="card-title">Eco-Tip of the Day</h2>
              <div class="tip-box">
                {{ tipOfTheDay }}
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
                <div class="heatmap-weekdays">
                  <span v-for="d in ['S','M','T','W','T','F','S']" :key="d">{{ d }}</span>
                </div>
                <div class="heatmap-grid">
                  <div
                    v-for="day in calendarDays"
                    :key="day.date"
                    class="heatmap-day"
                    :class="[day.statusClass, { 'is-today': day.isToday }]"
                    :title="`${day.date}: ${day.totalCO2.toFixed(1)} kg CO2`"
                  >
                    <span class="heatmap-day-label">{{ day.dayLabel }}</span>
                  </div>
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
            <div v-for="tip in libraryTips" :key="tip.id" class="tip-item">
              <div class="tip-icon">{{ tip.icon }}</div>
              <p>
                {{ tip.text }}
              </p>
            </div>
          </div>
        </div>
      </template>

    </div>
  </main>
</template>

<style scoped>
.home-main {
  color: var(--text-main);
  background-color: var(--bg-body);
  min-height: 100vh;
  padding: 2rem;
  margin-left: 225px;
}

.loading-state {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 50vh;
  color: var(--text-muted);
  font-size: 1.1rem;
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

.chart-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.chart-header .card-title {
  margin-bottom: 0;
}

.trend-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
  white-space: nowrap;
}

.trend-icon {
  width: 13px;
  height: 13px;
  flex-shrink: 0;
}

.trend-badge.up {
  background-color: rgba(194, 92, 60, 0.12);
  color: #b1532f;
}

.trend-badge.down {
  background-color: rgba(46, 79, 50, 0.12);
  color: #2e4f32;
}

.trend-badge.flat {
  background-color: rgba(0, 0, 0, 0.06);
  color: var(--text-muted);
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
  gap: 0.6rem;
  margin-top: auto;
}

.heatmap-weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.6rem;
  width: 100%;
}

.heatmap-weekdays span {
  text-align: center;
  font-size: 0.72rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  color: var(--text-muted);
  text-transform: uppercase;
}

.heatmap-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.6rem;
  width: 100%;
}

.heatmap-day {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  display: flex;
  align-items: flex-end;
  justify-content: flex-end;
  padding: 0.3rem;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.heatmap-day-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: rgba(0, 0, 0, 0.32);
  line-height: 1;
}

.heatmap-day.medium .heatmap-day-label,
.heatmap-day.low .heatmap-day-label {
  color: rgba(255, 255, 255, 0.85);
}

.heatmap-day:hover {
  transform: scale(1.12);
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  z-index: 1;
}

.heatmap-day.is-today {
  box-shadow: 0 0 0 2px var(--bg-card), 0 0 0 4px #2e4f32;
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
  margin-top: 0.2rem;
}

.legend-block {
  width: 13px;
  height: 13px;
  border-radius: 3px;
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
  display: inline-block;
  margin-right: 0.25rem;
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
