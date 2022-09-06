class Chart {
  constructor(selector, data) {
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

    this.getText = function ({ node, index }) {
      const el = document.createElementNS(this.xmlns, "text");
      this.setAttributes(el, {
        x: 0,
        y: 0,
        fontSize: "0.4em"
      });
      el.innerHTML = node.title;
      return el;
    };

    this.renderSectors = function () {
      return this.data.map((d, i) => {
        const sectorGroupElement = document.createElementNS(this.xmlns, "g");
        const center = this.center;
        const isLarge = (d.value || 0) / this.total > 0.5;
        const angle = (360 * (d.value || 0)) / this.total;
        const radius =
          (center +
            (i === this.expandedIndex ? this.expandSize : 0) -
            this.strokeWidth) *
          0.5;
        this.angleStart = this.angleEnd;
        this.angleMargin = this.angleMargin > angle ? angle : this.angleMargin;
        this.angleEnd = this.angleStart + angle - this.angleMargin;
        const x1 =
          center + radius * Math.cos((Math.PI * this.angleStart) / 180);
        const y1 =
          center + radius * Math.sin((Math.PI * this.angleStart) / 180);
        const x2 = center + radius * Math.cos((Math.PI * this.angleEnd) / 180);
        const y2 = center + radius * Math.sin((Math.PI * this.angleEnd) / 180);
        const path = `
            M${center},${center}
            L${x1},${y1}
            A${radius},${radius}
            0 ${isLarge ? 1 : 0},1
            ${x2},${y2}
            z
          `;
        const $sector = this.renderSector({
          fill: d.color,
          path
        });

        const angleMid = (this.angleEnd - this.angleStart) / 2;
        const r2 = radius * 1.2 + 5;
        const a =
          angleMid + this.anglesMap.slice(0, i).reduce((a, b) => a + b, 0);
        const xMid = center + r2 * Math.cos((Math.PI * a) / 180);
        const yMid = center + r2 * Math.sin((Math.PI * a) / 180);
        const xEnd = xMid > center ? xMid + 20 : xMid - 20;
        const sectorLabelLinePoints = `
          ${center},${center} 
          ${xMid} ${yMid} 
          ${xEnd} ${yMid}
        `;
        const $polyline = document.createElementNS(this.xmlns, "polyline");
        this.setAttributes($polyline, {
          points: sectorLabelLinePoints,
          fill: "none",
          stroke: d.color,
          strokeWidth: this.strokeWidth
        });
        // text

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

        const $textBox = document.createElementNS(this.xmlns, "foreignObject");
        this.setAttributes($textBox, {
          x: xMid > center ? xEnd : xEnd - 100,
          y: yMid - 20,
          width: 100,
          height: 200
        });
        const $text = document.createElementNS(this.xmlnshtml, "div");
        $text.style.background = "white";
        $text.style.padding = "4px";
        $text.style.color = "black";
        $text.style.textAlign = xMid <= center ? "right" : "left";
        $text.innerHTML = d.title;
        $textBox.appendChild($text);

        // end of text
        sectorGroupElement.appendChild($sector);
        sectorGroupElement.appendChild($polyline);
        sectorGroupElement.appendChild($textBox);
        return sectorGroupElement;
      });
    };

    this.renderSingleData = function () {
      const [d] = this.data;
      this.setAttributes(this.el.circle, {
        cx: this.center,
        cy: this.center,
        fill: d?.color || "#ddd",
        rx: this.center / 2,
        ry: this.center / 2
      });
      this.el.sectors.appendChild(this.el.circle);
    };

    this.renderMultipleData = function () {
      const sectors = this.renderSectors();
      sectors.forEach((sector) => this.el.sectors.appendChild(sector));
    };

    this.renderSector = function (props) {
      const { fill, path: d } = props;
      const path = document.createElementNS(this.xmlns, "path");
      this.setAttributes(path, {
        d,
        fill,
        stroke: this.strokeColor,
        strokeWidth: this.strokeWidth,
        strokeLinejoin: this.strokeLinejoin
      });
      return path;
    };

    this.init = function () {
      this.setAttributes(this.el.svg, {
        viewBox: `0 0 ${this.viewBoxSize} ${this.viewBoxSize}`,
        width: this.viewBoxSize,
        height: this.viewBoxSize,
        style: {
          display: "block"
        }
      });
      this.el.svg.appendChild(this.el.sectors);
      data.forEach((d) => d.value > 0 && this.data.push(d));
      this.data.forEach((d) => (this.total += parseInt(d.value || 0, 10)));
      this.data.forEach((d) =>
        this.anglesMap.push((360 * d.value) / this.total)
      );
    };

    this.deploy = function () {
      this.el.selector.innerHTML = "";
      this.el.selector.appendChild(this.el.svg);
    };

    this.render = function () {
      this.init();
      this.data.length > 1
        ? this.renderMultipleData()
        : this.renderSingleData();
      this.deploy();
    };
  }
}

export default Chart;
