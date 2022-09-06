// modules are defined as an array
// [ module function, map of requires ]
//
// map of requires is short require name -> numeric require
//
// anything defined in a previous bundle is accessed via the
// orig method which is the require for previous bundles
parcelRequire = (function (modules, cache, entry, globalName) {
  // Save the require from previous bundle to this closure if any
  var previousRequire = typeof parcelRequire === 'function' && parcelRequire;
  var nodeRequire = typeof require === 'function' && require;

  function newRequire(name, jumped) {
    if (!cache[name]) {
      if (!modules[name]) {
        // if we cannot find the module within our internal map or
        // cache jump to the current global require ie. the last bundle
        // that was added to the page.
        var currentRequire = typeof parcelRequire === 'function' && parcelRequire;
        if (!jumped && currentRequire) {
          return currentRequire(name, true);
        }

        // If there are other bundles on this page the require from the
        // previous one is saved to 'previousRequire'. Repeat this as
        // many times as there are bundles until the module is found or
        // we exhaust the require chain.
        if (previousRequire) {
          return previousRequire(name, true);
        }

        // Try the node require function if it exists.
        if (nodeRequire && typeof name === 'string') {
          return nodeRequire(name);
        }

        var err = new Error('Cannot find module \'' + name + '\'');
        err.code = 'MODULE_NOT_FOUND';
        throw err;
      }

      localRequire.resolve = resolve;
      localRequire.cache = {};

      var module = cache[name] = new newRequire.Module(name);

      modules[name][0].call(module.exports, localRequire, module, module.exports, this);
    }

    return cache[name].exports;

    function localRequire(x){
      return newRequire(localRequire.resolve(x));
    }

    function resolve(x){
      return modules[name][1][x] || x;
    }
  }

  function Module(moduleName) {
    this.id = moduleName;
    this.bundle = newRequire;
    this.exports = {};
  }

  newRequire.isParcelRequire = true;
  newRequire.Module = Module;
  newRequire.modules = modules;
  newRequire.cache = cache;
  newRequire.parent = previousRequire;
  newRequire.register = function (id, exports) {
    modules[id] = [function (require, module) {
      module.exports = exports;
    }, {}];
  };

  var error;
  for (var i = 0; i < entry.length; i++) {
    try {
      newRequire(entry[i]);
    } catch (e) {
      // Save first error but execute all entries
      if (!error) {
        error = e;
      }
    }
  }

  if (entry.length) {
    // Expose entry point to Node, AMD or browser globals
    // Based on https://github.com/ForbesLindesay/umd/blob/master/template.js
    var mainExports = newRequire(entry[entry.length - 1]);

    // CommonJS
    if (typeof exports === "object" && typeof module !== "undefined") {
      module.exports = mainExports;

    // RequireJS
    } else if (typeof define === "function" && define.amd) {
     define(function () {
       return mainExports;
     });

    // <script>
    } else if (globalName) {
      this[globalName] = mainExports;
    }
  }

  // Override the current require with this new one
  parcelRequire = newRequire;

  if (error) {
    // throw error from earlier, _after updating parcelRequire_
    throw error;
  }

  return newRequire;
})({"src/chart.js":[function(require,module,exports) {
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var Chart = /*#__PURE__*/_createClass(function Chart(selector, data) {
  _classCallCheck(this, Chart);

  this.xmlns = "http://www.w3.org/2000/svg";
  this.xmlnshtml = "http://www.w3.org/1999/xhtml";
  this.viewBoxSize = 500;
  this.center = this.viewBoxSize / 2;
  this.expandSize = 10;
  this.startAngle = 0;
  this.angleStart = 0;
  this.angleEnd = 0;
  this.angleMargin = 0;
  this.expandedIndex = -1;
  this.total = 0;
  this.anglesMap = [];
  this.strokeColor = "#FFFFFF";
  this.strokeWidth = 1;
  this.strokeLinejoin = "round";
  this.transitionDuration = "0.3s";
  this.transitionTimingFunction = "ease-in-out";
  this.el = {
    selector: document.getElementById(selector),
    svg: document.createElementNS(this.xmlns, "svg"),
    circle: document.createElementNS(this.xmlns, "ellipse"),
    sectors: document.createElementNS(this.xmlns, "g")
  };
  this.data = [];

  this.setAttributes = function (el, attrs) {
    for (var key in attrs) {
      el.setAttributeNS(null, key, attrs[key]);
    }
  };

  this.getText = function (_ref) {
    var node = _ref.node,
        index = _ref.index;
    var el = document.createElementNS(this.xmlns, "text");
    this.setAttributes(el, {
      x: 0,
      y: 0,
      fontSize: "0.4em"
    });
    el.innerHTML = node.title;
    return el;
  };

  this.renderSectors = function () {
    var _this = this;

    return this.data.map(function (d, i) {
      var sectorGroupElement = document.createElementNS(_this.xmlns, "g");
      var center = _this.center;
      var isLarge = (d.value || 0) / _this.total > 0.5;
      var angle = 360 * (d.value || 0) / _this.total;
      var radius = (center + (i === _this.expandedIndex ? _this.expandSize : 0) - _this.strokeWidth) * 0.5;
      _this.angleStart = _this.angleEnd;
      _this.angleMargin = _this.angleMargin > angle ? angle : _this.angleMargin;
      _this.angleEnd = _this.angleStart + angle - _this.angleMargin;
      var x1 = center + radius * Math.cos(Math.PI * _this.angleStart / 180);
      var y1 = center + radius * Math.sin(Math.PI * _this.angleStart / 180);
      var x2 = center + radius * Math.cos(Math.PI * _this.angleEnd / 180);
      var y2 = center + radius * Math.sin(Math.PI * _this.angleEnd / 180);
      var path = "\n            M".concat(center, ",").concat(center, "\n            L").concat(x1, ",").concat(y1, "\n            A").concat(radius, ",").concat(radius, "\n            0 ").concat(isLarge ? 1 : 0, ",1\n            ").concat(x2, ",").concat(y2, "\n            z\n          ");

      var $sector = _this.renderSector({
        fill: d.color,
        path: path
      });

      var angleMid = (_this.angleEnd - _this.angleStart) / 2;
      var r2 = radius * 1.2 + 5;

      var a = angleMid + _this.anglesMap.slice(0, i).reduce(function (a, b) {
        return a + b;
      }, 0);

      var xMid = center + r2 * Math.cos(Math.PI * a / 180);
      var yMid = center + r2 * Math.sin(Math.PI * a / 180);
      var xEnd = xMid > center ? xMid + 20 : xMid - 20;
      var sectorLabelLinePoints = "\n          ".concat(center, ",").concat(center, " \n          ").concat(xMid, " ").concat(yMid, " \n          ").concat(xEnd, " ").concat(yMid, "\n        ");
      var $polyline = document.createElementNS(_this.xmlns, "polyline");

      _this.setAttributes($polyline, {
        points: sectorLabelLinePoints,
        fill: "none",
        stroke: d.color,
        strokeWidth: _this.strokeWidth
      }); // text
      // const $text = document.createElementNS(this.xmlns, "text");
      // this.setAttributes($text, {
      //   x: xMid - 3,
      //   y: yMid - 3,
      //   "font-size": "0.7em",
      //   "text-anchor": xMid < center || xEnd < yMid ? "end" : "start"
      // });
      // $text.innerHTML = d.title;
      // <foreignObject x="20" y="90" width="150" height="200">
      //   <p xmlns="http://www.w3.org/1999/xhtml">Text goes here</p>
      // </foreignObject>


      var $textBox = document.createElementNS(_this.xmlns, "foreignObject");

      _this.setAttributes($textBox, {
        x: xMid > center ? xEnd : xEnd - 100,
        y: yMid - 20,
        width: 100,
        height: 200
      });

      var $text = document.createElementNS(_this.xmlnshtml, "div");
      $text.style.background = "white";
      $text.style.padding = "4px";
      $text.style.color = "black";
      $text.style.textAlign = xMid <= center ? "right" : "left";
      $text.innerHTML = d.title;
      $textBox.appendChild($text); // end of text

      sectorGroupElement.appendChild($sector);
      sectorGroupElement.appendChild($polyline);
      sectorGroupElement.appendChild($textBox);
      return sectorGroupElement;
    });
  };

  this.renderSingleData = function () {
    var _this$data = _slicedToArray(this.data, 1),
        d = _this$data[0];

    this.setAttributes(this.el.circle, {
      cx: this.center,
      cy: this.center,
      fill: (d === null || d === void 0 ? void 0 : d.color) || "#ddd",
      rx: this.center / 2,
      ry: this.center / 2
    });
    this.el.sectors.appendChild(this.el.circle);
  };

  this.renderMultipleData = function () {
    var _this2 = this;

    var sectors = this.renderSectors();
    sectors.forEach(function (sector) {
      return _this2.el.sectors.appendChild(sector);
    });
  };

  this.renderSector = function (props) {
    var fill = props.fill,
        d = props.path;
    var path = document.createElementNS(this.xmlns, "path");
    this.setAttributes(path, {
      d: d,
      fill: fill,
      stroke: this.strokeColor,
      strokeWidth: this.strokeWidth,
      strokeLinejoin: this.strokeLinejoin
    });
    return path;
  };

  this.init = function () {
    var _this3 = this;

    this.setAttributes(this.el.svg, {
      viewBox: "0 0 ".concat(this.viewBoxSize, " ").concat(this.viewBoxSize),
      width: this.viewBoxSize,
      height: this.viewBoxSize,
      style: {
        display: "block"
      }
    });
    this.el.svg.appendChild(this.el.sectors);
    data.forEach(function (d) {
      return d.value > 0 && _this3.data.push(d);
    });
    this.data.forEach(function (d) {
      return _this3.total += parseInt(d.value || 0, 10);
    });
    this.data.forEach(function (d) {
      return _this3.anglesMap.push(360 * d.value / _this3.total);
    });
  };

  this.deploy = function () {
    this.el.selector.innerHTML = "";
    this.el.selector.appendChild(this.el.svg);
  };

  this.render = function () {
    this.init();
    this.data.length > 1 ? this.renderMultipleData() : this.renderSingleData();
    this.deploy();
  };
});

var _default = Chart;
exports.default = _default;
},{}],"src/index.js":[function(require,module,exports) {
"use strict";

var _chart = _interopRequireDefault(require("./chart"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var count = 0;
var total = 0;
var data = [];
var colorsArr = ["#cb3a1b", "#d14e31", "#d55d43", "#d96e58", "#db755f", "#d66148", "#de806c", "#e7a395", "#e59d8d", "#e08976", "#e39483", "#ecb5a9", "#ebb0a4", "#f0c4bb", "#f5d8d2"];
init();

function addCalculateEvent(el) {
  el.addEventListener("blur", calculate);
  el.addEventListener("keyup", calculate);
  el.addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      calculate();
      e.target.blur();
    }
  });
}

function getTitleField() {
  var el = document.createElement("input");
  el.classList.add("title");
  el.setAttribute("placeholder", "Title");
  addCalculateEvent(el);
  return el;
}

function getValueField() {
  var el = document.createElement("input");
  el.classList.add("value");
  el.setAttribute("type", "number");
  el.setAttribute("placeholder", "Value");
  addCalculateEvent(el);
  return el;
}

function getColorHiddenField() {
  var el = document.createElement("input");
  el.classList.add("color");
  el.setAttribute("type", "hidden");
  el.value = colorsArr[Math.floor(Math.random() * colorsArr.length)];
  return el;
}

function getRemoveButton() {
  var el = document.createElement("button");
  el.textContent = "x";
  el.addEventListener("click", remove);
  return el;
}

function add() {
  var formItemWrapper = document.createElement("div");
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
  document.querySelectorAll(".form-item").forEach(function (item) {
    if (!item.querySelector("button")) {
      item.appendChild(getRemoveButton());
    }
  });
}

function clearRemoveButton() {
  var formList = document.getElementById("form-list");
  var removeBtn = formList.querySelector("button");
  removeBtn.parentNode.removeChild(formList.querySelector("button"));
}

function calculate() {
  resetCount();
  var values = document.querySelectorAll(".value");
  var titles = document.querySelectorAll(".title");
  var colors = document.querySelectorAll(".color");
  values.forEach(function (v) {
    return total += parseInt(v.value || 0, 10);
  });
  Array.from({
    length: count
  }).forEach(function (node, idx) {
    var value = parseInt(values[idx].value, 10) || 0;
    var title = titles[idx].value || "N/A";
    var color = colors[idx].value;
    data.push({
      title: title,
      value: value,
      color: color
    });
  });
  renderChart();
}

function watchCount() {
  count > 0 && appendRemoveButton();
  count === 1 && clearRemoveButton();
}

function renderChart() {
  var chart = new _chart.default("chart-area", data);
  chart.render();
}

function resetCount() {
  data = [];
  total = 0;
}

function init() {
  document.getElementById("add").addEventListener("click", add);
  resetCount(); // renderChart();

  add();
  add();
  add();
}
},{"./chart":"src/chart.js"}],"node_modules/parcel-bundler/src/builtins/hmr-runtime.js":[function(require,module,exports) {
var global = arguments[3];
var OVERLAY_ID = '__parcel__error__overlay__';
var OldModule = module.bundle.Module;

function Module(moduleName) {
  OldModule.call(this, moduleName);
  this.hot = {
    data: module.bundle.hotData,
    _acceptCallbacks: [],
    _disposeCallbacks: [],
    accept: function (fn) {
      this._acceptCallbacks.push(fn || function () {});
    },
    dispose: function (fn) {
      this._disposeCallbacks.push(fn);
    }
  };
  module.bundle.hotData = null;
}

module.bundle.Module = Module;
var checkedAssets, assetsToAccept;
var parent = module.bundle.parent;

if ((!parent || !parent.isParcelRequire) && typeof WebSocket !== 'undefined') {
  var hostname = "" || location.hostname;
  var protocol = location.protocol === 'https:' ? 'wss' : 'ws';
  var ws = new WebSocket(protocol + '://' + hostname + ':' + "46075" + '/');

  ws.onmessage = function (event) {
    checkedAssets = {};
    assetsToAccept = [];
    var data = JSON.parse(event.data);

    if (data.type === 'update') {
      var handled = false;
      data.assets.forEach(function (asset) {
        if (!asset.isNew) {
          var didAccept = hmrAcceptCheck(global.parcelRequire, asset.id);

          if (didAccept) {
            handled = true;
          }
        }
      }); // Enable HMR for CSS by default.

      handled = handled || data.assets.every(function (asset) {
        return asset.type === 'css' && asset.generated.js;
      });

      if (handled) {
        console.clear();
        data.assets.forEach(function (asset) {
          hmrApply(global.parcelRequire, asset);
        });
        assetsToAccept.forEach(function (v) {
          hmrAcceptRun(v[0], v[1]);
        });
      } else if (location.reload) {
        // `location` global exists in a web worker context but lacks `.reload()` function.
        location.reload();
      }
    }

    if (data.type === 'reload') {
      ws.close();

      ws.onclose = function () {
        location.reload();
      };
    }

    if (data.type === 'error-resolved') {
      console.log('[parcel] ✨ Error resolved');
      removeErrorOverlay();
    }

    if (data.type === 'error') {
      console.error('[parcel] 🚨  ' + data.error.message + '\n' + data.error.stack);
      removeErrorOverlay();
      var overlay = createErrorOverlay(data);
      document.body.appendChild(overlay);
    }
  };
}

function removeErrorOverlay() {
  var overlay = document.getElementById(OVERLAY_ID);

  if (overlay) {
    overlay.remove();
  }
}

function createErrorOverlay(data) {
  var overlay = document.createElement('div');
  overlay.id = OVERLAY_ID; // html encode message and stack trace

  var message = document.createElement('div');
  var stackTrace = document.createElement('pre');
  message.innerText = data.error.message;
  stackTrace.innerText = data.error.stack;
  overlay.innerHTML = '<div style="background: black; font-size: 16px; color: white; position: fixed; height: 100%; width: 100%; top: 0px; left: 0px; padding: 30px; opacity: 0.85; font-family: Menlo, Consolas, monospace; z-index: 9999;">' + '<span style="background: red; padding: 2px 4px; border-radius: 2px;">ERROR</span>' + '<span style="top: 2px; margin-left: 5px; position: relative;">🚨</span>' + '<div style="font-size: 18px; font-weight: bold; margin-top: 20px;">' + message.innerHTML + '</div>' + '<pre>' + stackTrace.innerHTML + '</pre>' + '</div>';
  return overlay;
}

function getParents(bundle, id) {
  var modules = bundle.modules;

  if (!modules) {
    return [];
  }

  var parents = [];
  var k, d, dep;

  for (k in modules) {
    for (d in modules[k][1]) {
      dep = modules[k][1][d];

      if (dep === id || Array.isArray(dep) && dep[dep.length - 1] === id) {
        parents.push(k);
      }
    }
  }

  if (bundle.parent) {
    parents = parents.concat(getParents(bundle.parent, id));
  }

  return parents;
}

function hmrApply(bundle, asset) {
  var modules = bundle.modules;

  if (!modules) {
    return;
  }

  if (modules[asset.id] || !bundle.parent) {
    var fn = new Function('require', 'module', 'exports', asset.generated.js);
    asset.isNew = !modules[asset.id];
    modules[asset.id] = [fn, asset.deps];
  } else if (bundle.parent) {
    hmrApply(bundle.parent, asset);
  }
}

function hmrAcceptCheck(bundle, id) {
  var modules = bundle.modules;

  if (!modules) {
    return;
  }

  if (!modules[id] && bundle.parent) {
    return hmrAcceptCheck(bundle.parent, id);
  }

  if (checkedAssets[id]) {
    return;
  }

  checkedAssets[id] = true;
  var cached = bundle.cache[id];
  assetsToAccept.push([bundle, id]);

  if (cached && cached.hot && cached.hot._acceptCallbacks.length) {
    return true;
  }

  return getParents(global.parcelRequire, id).some(function (id) {
    return hmrAcceptCheck(global.parcelRequire, id);
  });
}

function hmrAcceptRun(bundle, id) {
  var cached = bundle.cache[id];
  bundle.hotData = {};

  if (cached) {
    cached.hot.data = bundle.hotData;
  }

  if (cached && cached.hot && cached.hot._disposeCallbacks.length) {
    cached.hot._disposeCallbacks.forEach(function (cb) {
      cb(bundle.hotData);
    });
  }

  delete bundle.cache[id];
  bundle(id);
  cached = bundle.cache[id];

  if (cached && cached.hot && cached.hot._acceptCallbacks.length) {
    cached.hot._acceptCallbacks.forEach(function (cb) {
      cb();
    });

    return true;
  }
}
},{}]},{},["node_modules/parcel-bundler/src/builtins/hmr-runtime.js","src/index.js"], null)
//# sourceMappingURL=/src.a2b27638.js.map