<template>
  <Pie
      :width="100"
      :height="100"
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
  props: {
    labels: {
      type: Array,
    },
    data: {
      type: Array,
    },
  },
  created() {
    this.chartData.labels = this.labels
    this.chartData.datasets.push({
      backgroundColor: [
        'rgb(54, 162, 235)',
        'rgb(255, 205, 86)',
        'rgb(12,113,11)',
        'rgb(166,65,19)',
        'rgb(18,70,86)',
        'rgb(124,20,111)',
        'rgb(136,0,2)',
      ],
      data: this.data,
      hoverOffset: 4
    })
  },
  data() {
    return {
      chartData: {
        labels: [],
        datasets: [],
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