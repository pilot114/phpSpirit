<template>
  <Pie
      :width="300"
      :height="300"
      :chart-data="chartData"
      :chart-options="chartOptions"
  />
</template>

<script>
import { Pie } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  CategoryScale,
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale)

export default {
  name: 'PieChart',
  components: { Pie },
  data() {
    return {
      chartData: {
        labels: [ 'January', 'February'],
        datasets: [
          {
            label: 'January',
            backgroundColor: [
              'rgb(54, 162, 235)',
              'rgb(255, 205, 86)',
              'rgb(12,113,11)',
              'rgb(166,65,19)',
              'rgb(18,70,86)',
              'rgb(124,20,111)',
              'rgb(136,0,2)',
            ],
            data: [300, 50, 100],
            hoverOffset: 4
          }
        ],
      },
      chartOptions: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top',
          },
        }
      }
    }
  }
}
</script>