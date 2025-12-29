// Load and render report data
let report = null;

// Colors for pie charts
const CHART_COLORS = [
  'rgb(54, 162, 235)',
  'rgb(255, 205, 86)',
  'rgb(12, 113, 11)',
  'rgb(166, 65, 19)',
  'rgb(18, 70, 86)',
  'rgb(124, 20, 111)',
  'rgb(136, 0, 2)',
];

// Calculate percentage
function calcPercent(value, total) {
  const percent = value / (total / 100);
  return Math.round(percent);
}

// Create pie chart
function createPieChart(container, labels, data) {
  const canvas = document.createElement('canvas');
  container.appendChild(canvas);

  new Chart(canvas, {
    type: 'pie',
    data: {
      labels: labels,
      datasets: [{
        data: data,
        backgroundColor: CHART_COLORS,
        hoverOffset: 4
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: 'top',
        }
      }
    }
  });
}

// Render a field based on its data type
function renderField(fieldName, field) {
  const container = document.createElement('div');
  container.className = 'field-container';

  // Title
  const title = document.createElement('h3');
  title.textContent = `${fieldName}: ${calcPercent(field.count, report.head.repoCount)}% (${field.count})`;
  container.appendChild(title);

  // Data
  if (typeof field.data !== 'object') {
    // Simple string/number value
    const p = document.createElement('p');
    p.textContent = field.data;
    container.appendChild(p);
  } else {
    // Object data
    const firstValue = field.data[Object.keys(field.data)[0]];

    if (Array.isArray(firstValue)) {
      // Data format: {name1: [], name2: []}
      const chartContainer = document.createElement('div');
      chartContainer.className = 'chart-container';
      container.appendChild(chartContainer);

      const labels = Object.keys(field.data);
      const data = Object.values(field.data).map(x => x.length);
      createPieChart(chartContainer, labels, data);
    } else if (typeof firstValue === 'object') {
      // Data format: {name1: {}, name2: {}}
      const chartContainer = document.createElement('div');
      chartContainer.className = 'chart-container';
      container.appendChild(chartContainer);

      const labels = Object.keys(field.data);
      const data = Object.values(field.data).map(x => Object.keys(x).length);
      createPieChart(chartContainer, labels, data);
    } else {
      // Data format: {value: name, ...} - render as list
      const ul = document.createElement('ul');
      for (const [value, name] of Object.entries(field.data)) {
        const li = document.createElement('li');
        const b = document.createElement('b');
        b.textContent = value;
        li.appendChild(b);
        li.appendChild(document.createTextNode(`: ${name}`));
        ul.appendChild(li);
      }
      container.appendChild(ul);
    }
  }

  return container;
}

// Render all composer fields
function renderComposerFields() {
  const container = document.getElementById('composer-fields');
  container.innerHTML = '';

  if (report.composerFields) {
    for (const [fieldName, field] of Object.entries(report.composerFields)) {
      container.appendChild(renderField(fieldName, field));
    }
  }
}

// Initialize application
async function init() {
  try {
    const response = await fetch('report.json');
    report = await response.json();

    // Render repo count
    document.getElementById('repo-count').textContent = report.head.repoCount;

    // Render composer fields
    renderComposerFields();
  } catch (error) {
    console.error('Error loading report:', error);
    document.getElementById('app').innerHTML = '<p>Error loading report data</p>';
  }
}

// Start when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
