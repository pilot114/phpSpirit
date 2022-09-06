import Chart from "./chart";

let count = 0;
let total = 0;
let data = [];
const colorsArr = [
  "#cb3a1b",
  "#d14e31",
  "#d55d43",
  "#d96e58",
  "#db755f",
  "#d66148",
  "#de806c",
  "#e7a395",
  "#e59d8d",
  "#e08976",
  "#e39483",
  "#ecb5a9",
  "#ebb0a4",
  "#f0c4bb",
  "#f5d8d2"
];

init();

function addCalculateEvent(el) {
  el.addEventListener("blur", calculate);
  el.addEventListener("keyup", calculate);
  el.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      calculate();
      e.target.blur();
    }
  });
}

function getTitleField() {
  const el = document.createElement("input");
  el.classList.add("title");
  el.setAttribute("placeholder", "Title");
  addCalculateEvent(el);
  return el;
}

function getValueField() {
  const el = document.createElement("input");
  el.classList.add("value");
  el.setAttribute("type", "number");
  el.setAttribute("placeholder", "Value");
  addCalculateEvent(el);
  return el;
}

function getColorHiddenField() {
  const el = document.createElement("input");
  el.classList.add("color");
  el.setAttribute("type", "hidden");
  el.value = colorsArr[Math.floor(Math.random() * colorsArr.length)];
  return el;
}

function getRemoveButton() {
  const el = document.createElement("button");
  el.textContent = "x";
  el.addEventListener("click", remove);
  return el;
}

function add() {
  const formItemWrapper = document.createElement("div");
  formItemWrapper.classList.add("form-item");
  formItemWrapper.appendChild(getTitleField());
  formItemWrapper.appendChild(getValueField());
  formItemWrapper.appendChild(getColorHiddenField());
  document.getElementById("form-list").appendChild(formItemWrapper);
  count++;
  watchCount();
  calculate();
}

function remove(e) {
  document.getElementById("form-list").removeChild(e.target.parentNode);
  count--;
  watchCount();
  calculate();
}

function appendRemoveButton() {
  document.querySelectorAll(".form-item").forEach((item) => {
    if (!item.querySelector("button")) {
      item.appendChild(getRemoveButton());
    }
  });
}

function clearRemoveButton() {
  const formList = document.getElementById("form-list");
  const removeBtn = formList.querySelector("button");
  removeBtn.parentNode.removeChild(formList.querySelector("button"));
}

function calculate() {
  resetCount();
  const values = document.querySelectorAll(".value");
  const titles = document.querySelectorAll(".title");
  const colors = document.querySelectorAll(".color");
  values.forEach((v) => (total += parseInt(v.value || 0, 10)));
  Array.from({ length: count }).forEach((node, idx) => {
    const value = parseInt(values[idx].value, 10) || 0;
    const title = titles[idx].value || "N/A";
    const color = colors[idx].value;
    data.push({ title, value, color });
  });
  renderChart();
}

function watchCount() {
  count > 0 && appendRemoveButton();
  count === 1 && clearRemoveButton();
}

function renderChart() {
  const chart = new Chart("chart-area", data);
  chart.render();
}

function resetCount() {
  data = [];
  total = 0;
}

function init() {
  document.getElementById("add").addEventListener("click", add);
  resetCount();
  // renderChart();
  add();
  add();
  add();
}
