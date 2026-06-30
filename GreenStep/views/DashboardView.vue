<script setup>
import { ref, computed, onMounted } from 'vue'
import TopBar from '@/components/TopBar.vue'
import SideBar from '@/components/SideBar.vue'
import api from '@/api/client.js' // Fixed import path to align with project structure

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
    console.error('Error fetching dashboard records:', error)
  }
})

// Custom helper function to explicitly format Dates into YYYY-MM-DD strings matching Malaysia timezone
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
    dates.push(formatToLocalYMD(d)) 
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
      const carbonValue = parseFloat(log.co2_emission)
      recordMap[dateKey] = (recordMap[dateKey] || 0) + carbonValue
    }
  })
  return recordMap
})

const todaysFootprint = computed(() => {
  const todayKey = new Date().toLocaleDateString()
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
      grid: { display: false }, 
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

const ecoTipsList = [
  "Unplug electronics when not in use. Standby power can account for up to 10% of your total household electricity bill!",
  "Wash clothes in cold water. About 75% to 90% of all the energy your washing machine uses goes solely into heating the water.",
  "Skip the heated dry cycle on your dishwasher. Letting your dishes air-dry can reduce the appliance's energy use by up to 15%.",
  "Swap out your home's remaining incandescent bulbs for LEDs. They use up to 75% less energy and last 25 times longer.",
  "Keep your refrigerator between 35°F and 38°F, and your freezer at 0°F. Keeping them any colder wastes energy unnecessarily.",
  "Clean your dryer’s lint trap before every load. A clogged screen forces the machine to run longer, burning up to 30% more energy.",
  "Lower your thermostat by 7°–10°F for 8 hours a day (like when you are asleep) to save up to 10% a year on heating costs.",
  "Cut your shower time down to 5 minutes. This small change can save up to 1,000 gallons of water per person every month.",
  "Repair leaky faucets promptly. A single faucet dripping at a rate of one drop per second can waste over 3,000 gallons of water a year.",
  "Turn off the tap while brushing your teeth. You can save up to 4 gallons of clean water every single time you brush.",
  "Only run your dishwasher and washing machine when they are completely full. This saves up to 300 to 800 gallons of water per month.",
  "Install low-flow faucet aerators. They cost just a few dollars but cut bathroom sink water consumption by up to 30%.",
  "Skip meat just one day a week. It takes roughly 1,800 gallons of water to produce a pound of beef compared to only 244 gallons for tofu.",
  "Plan your meals before grocery shopping. Around 30% to 40% of the entire food supply in developed countries ends up in landfills.",
  "Swap paper towels for washable cotton cloths. Creating paper towels consumes millions of trees and billions of gallons of water annually.",
  "Keep a reusable shopping bag in your car or backpack. A single plastic bag can take up to 500 years to degrade in a landfill.",
  "Freeze or repurpose leftover meals. Food waste rotting in landfills accounts for roughly 8% of all global greenhouse gas emissions.",
  "Compost your fruit peels and vegetable scraps. Composting prevents harmful methane production and creates nutrient-rich soil.",
  "Keep your car's tires inflated to the recommended pressure. Under-inflated tires drop gas mileage by about 0.2% for every 1 psi drop.",
  "Clear heavy, unnecessary clutter out of your car trunk. An extra 100 pounds in your vehicle can reduce fuel economy by up to 1%.",
  "Plan and combine multiple short errands into one single trip. Cold engine starts can use twice as much fuel as a warm, continuous drive.",
  "Remove empty roof racks or cargo boxes when not in use. They create aerodynamic drag that can lower fuel efficiency by up to 20%.",
  "Avoid aggressive acceleration and hard braking. Safe, smooth driving can improve your highway gas mileage by 15% to 30%.",
  "Delete old emails and unsubscribe from unwanted newsletters. Storing useless data in cloud server farms consumes continuous cooling energy.",
  "Switch your phone, computer, and dashboard interfaces to Dark Mode. On OLED screens, this reduces battery power usage by up to 30%.",
  "Think twice before printing a document. The pulp and paper industry is one of the largest industrial energy consumers worldwide.",
  "Plug your home office setups into a smart power strip. It automatically cuts power to accessories when your computer goes to sleep.",
  "Purchase locally grown produce when possible. This eliminates the massive 'food miles' and carbon emissions required to transport items.",
  "Switch from bottled body wash to traditional bar soap. Bar soaps require less energy to manufacture and eliminate plastic waste entirely.",
  "Choose durable, high-quality items over fast-fashion. Extending a garment's life by just 9 months reduces its carbon footprint by 20%."
]

// Pure 24-hour deterministic selection index calculation
const tipOfTheDay = computed(() => {
  const today = new Date()
  const uniqueDayIdentifier = today.getFullYear() + today.getMonth() + today.getDate()
  const tipIndex = uniqueDayIdentifier % ecoTipsList.length
  return ecoTipsList[tipIndex]
})
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
              <div class="heatmap-grid">
                <div
                  v-for="day in calendarDays"
                  :key="day.date"
                  class="heatmap-day"
                  :class="day.statusClass"
                  :title="`${day.date}: ${day.totalCO2.toFixed(2)} kg CO2`"
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