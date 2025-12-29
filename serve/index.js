// Load and render report data
let report = null;

// Important fields to display
const IMPORTANT_FIELDS = ['name', 'license', 'type', 'require'];

// Field display names and descriptions
const FIELD_INFO = {
  'name': { title: 'Popular Vendors', desc: 'Most used package vendors in PHP ecosystem' },
  'license': { title: 'Licenses', desc: 'Distribution of software licenses' },
  'type': { title: 'Package Types', desc: 'Types of composer packages' },
  'require': { title: 'Dependencies', desc: 'Most required packages' }
};

// Colors for pie charts
const CHART_COLORS = [
  'rgb(54, 162, 235)',
  'rgb(255, 205, 86)',
  'rgb(12, 113, 11)',
  'rgb(166, 65, 19)',
  'rgb(18, 70, 86)',
  'rgb(124, 20, 111)',
  'rgb(136, 0, 2)',
  'rgb(255, 99, 132)',
  'rgb(75, 192, 192)',
  'rgb(153, 102, 255)',
  'rgb(201, 203, 207)'
];

// Calculate percentage
function calcPercent(value, total) {
  const percent = value / (total / 100);
  return Math.round(percent);
}

// Get top N items from data, group rest as "Others"
function getTopItems(data, maxItems = 10) {
  let items = [];

  // Convert data to array of [label, value]
  if (Array.isArray(Object.values(data)[0])) {
    items = Object.entries(data).map(([label, arr]) => [label, arr.length]);
  } else if (typeof Object.values(data)[0] === 'object') {
    items = Object.entries(data).map(([label, obj]) => [label, Object.keys(obj).length]);
  } else {
    items = Object.entries(data).map(([label, val]) => [label, val]);
  }

  // Sort by value descending
  items.sort((a, b) => b[1] - a[1]);

  // Take top N and group rest
  if (items.length > maxItems) {
    const topItems = items.slice(0, maxItems);
    const others = items.slice(maxItems);
    const othersSum = others.reduce((sum, item) => sum + item[1], 0);

    if (othersSum > 0) {
      topItems.push(['Others', othersSum]);
    }

    return {
      labels: topItems.map(item => item[0]),
      data: topItems.map(item => item[1])
    };
  }

  return {
    labels: items.map(item => item[0]),
    data: items.map(item => item[1])
  };
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

  const info = FIELD_INFO[fieldName] || { title: fieldName, desc: '' };

  // Title
  const title = document.createElement('h3');
  title.textContent = info.title;
  container.appendChild(title);

  // Description
  if (info.desc) {
    const desc = document.createElement('p');
    desc.className = 'field-description';
    desc.textContent = info.desc;
    container.appendChild(desc);
  }

  // Stats
  const stats = document.createElement('p');
  stats.className = 'field-stats';
  stats.textContent = `Coverage: ${calcPercent(field.count, report.head.repoCount)}% (${field.count} repositories)`;
  container.appendChild(stats);

  // Data
  if (typeof field.data !== 'object' || field.data === null) {
    // Simple string/number value
    const p = document.createElement('p');
    p.textContent = field.data;
    container.appendChild(p);
  } else {
    // Get top items
    const topData = getTopItems(field.data, 10);

    // Create chart
    const chartContainer = document.createElement('div');
    chartContainer.className = 'chart-container';
    container.appendChild(chartContainer);

    createPieChart(chartContainer, topData.labels, topData.data);
  }

  return container;
}

// Render summary statistics
function renderSummary() {
  const container = document.getElementById('summary');
  container.innerHTML = '';

  // Count total packages
  let totalPackages = 0;
  if (report.composerFields.name && report.composerFields.name.data) {
    totalPackages = Object.values(report.composerFields.name.data).reduce((sum, arr) => sum + arr.length, 0);
  }

  // Count total vendors
  let totalVendors = 0;
  if (report.composerFields.name && report.composerFields.name.data) {
    totalVendors = Object.keys(report.composerFields.name.data).length;
  }

  // Count total licenses
  let totalLicenses = 0;
  if (report.composerFields.license && report.composerFields.license.data) {
    totalLicenses = Object.keys(report.composerFields.license.data).length;
  }

  const summaryData = [
    { title: 'Repositories', value: report.head.repoCount },
    { title: 'Packages', value: totalPackages },
    { title: 'Vendors', value: totalVendors },
    { title: 'Licenses', value: totalLicenses }
  ];

  summaryData.forEach(item => {
    const card = document.createElement('div');
    card.className = 'summary-card';

    const title = document.createElement('h3');
    title.textContent = item.title;

    const number = document.createElement('div');
    number.className = 'number';
    number.textContent = item.value.toLocaleString();

    card.appendChild(title);
    card.appendChild(number);
    container.appendChild(card);
  });
}

// Render all composer fields
function renderComposerFields() {
  const container = document.getElementById('composer-fields');
  container.innerHTML = '';

  if (report.composerFields) {
    // Render only important fields
    for (const fieldName of IMPORTANT_FIELDS) {
      if (report.composerFields[fieldName]) {
        container.appendChild(renderField(fieldName, report.composerFields[fieldName]));
      }
    }
  }
}

// Initialize application
async function init() {
  try {
    const response = await fetch('report.json');
    report = await response.json();

    // Render repo count
    document.getElementById('repo-count').textContent = report.head.repoCount.toLocaleString();

    // Render summary
    renderSummary();

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
