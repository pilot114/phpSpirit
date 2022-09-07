<template>
  <h1>PHP Spirit report</h1>
  <i>
    requests for improvements are accepted in the form of an
    <a href="https://github.com/pilot114/phpSpirit/issues">issue</a>
  </i>

  <h2>Common</h2>
  <p>Repositories analyzed: {{ report.head.repoCount }}</p>
  <p>
    Total size: {{ report.head.totalSize }}  |
    .git files size: {{ report.head.totalGitSize }} |
    php files size: {{ report.head.totalPhpSize }}
  </p>

  <h2>Composer statistic</h2>
  <template v-for="(field) in report.composerFields">
    <h3 >{{ field.name }}: {{ calcPersent(field.count) }}% ({{ field.count }})</h3>
    <b>{{ field.desc }}</b>

    <p v-if="typeof field.data !== 'object'">
      {{ field.data }}
    </p>
    <template v-else>
      <!-- {name1: [], name2: []} -->
      <div v-if="Array.isArray(field.data[Object.keys(field.data)[0]])">
        <PieChart
            :labels="Object.keys(field.data)"
            :data="Object.values(field.data).map(x => x.length)"
        />
      </div>
      <template v-else>
        <div v-if="typeof Object.values(field.data)[0] === 'object'">
          <PieChart
              :labels="Object.keys(field.data)"
              :data="Object.values(field.data).map(x => Object.keys(x).length)"
          />
        </div>
        <div v-else>
          <ul>
            <li v-for="(name, value) in field.data">
              <b>{{ value }}</b>: {{ name }}
            </li>
          </ul>
        </div>
      </template>
    </template>
  </template>
</template>

<script setup>
import PieChart from './components/PieChart.vue'
</script>

<script>
import report from './report.json'

export default {
  name: 'App',
  data() {
    return {
      report: report
    }
  },
  methods: {
    calcPersent(value) {
      let percent = value / (this.report.head.repoCount / 100)
      // return Math.round((percent + Number.EPSILON) * 100) / 100
      return Math.round((percent + Number.EPSILON))
    },
  }
}
</script>

