(function() {
  "use strict";
  document.write("<script type='text/javascript' src='heatmap.js/build/heatmap.min.js'></script>");
  var legendCanvas = document.createElement('canvas');
  legendCanvas.width = 100;
  legendCanvas.height = 10;
  var min = document.querySelector('#min');
  var max = document.querySelector('#max');
  var gradientImg = document.querySelector('#gradient');
  var legendCtx = legendCanvas.getContext('2d');
  var gradientCfg = {};
  
  /* legend code end */
  var heatmapInstance = h337.create({
    container: document.querySelector('.heatmap'),
    // onExtremaChange will be called whenever there's a new maximum or minimum
    onExtremaChange: function(data) {
      updateLegend(data);
    }
  });
  /* tooltip code start */
  var demoWrapper = document.querySelector('.demo-wrapper');
  var tooltip = document.querySelector('.tooltip');
  
  demoWrapper.onmousemove = function(ev) {
    var x = ev.layerX;
    var y = ev.layerY;
    // getValueAt gives us the value for a point p(x/y)
    var value = heatmapInstance.getValueAt({
      x: x, 
      y: y
    });
    tooltip.style.display = 'block';
    updateTooltip(x, y, value);
  };

  // hide tooltip on mouseout
  demoWrapper.onmouseout = function() {
    tooltip.style.display = 'none';
  };
  /* tooltip code end */

  // generate some data
  var points = [];
  points.push(pt_gen(650, 110, 10));
  points.push(pt_gen(650, 210, 8));
  points.push(pt_gen(650, 310, 6));
  points.push(pt_gen(650, 410, 4));
  points.push(pt_gen(650, 510, 2));
  points.push(pt_gen(850, 110, 9));
  points.push(pt_gen(850, 210, 7));
  points.push(pt_gen(850, 310, 5));
  points.push(pt_gen(850, 410, 3));
  points.push(pt_gen(850, 510, 1));
  var data = {
    max: 10,
    min: 0,
    data: points
  };
  heatmapInstance.setData(data);

  function updateLegend(data) {
    // the onExtremaChange callback gives us min, max, and the gradientConfig
    // so we can update the legend
    var min = document.querySelector('#min');
    var max = document.querySelector('#max');
    var gradientImg = document.querySelector('#gradient');
    min.innerHTML = data.min;
    max.innerHTML = data.max;
    // regenerate gradient image
    if (data.gradient != gradientCfg) {
      gradientCfg = data.gradient;
      var gradient = legendCtx.createLinearGradient(0, 0, 100, 1);
      for (var key in gradientCfg) {
        gradient.addColorStop(key, gradientCfg[key]);
      }
      legendCtx.fillStyle = gradient;
      legendCtx.fillRect(0, 0, 100, 10);
      gradientImg.src = legendCanvas.toDataURL();
    }
  };

  function pt_gen(x, y, val) {
    let pt = {
      x: x,
      y: y,
      value: val
    }
    return pt;   
  }

  function updateTooltip(x, y, value) {
    // + 15 for distance to cursor
    var transl = 'translate(' + (x + 15) + 'px, ' + (y + 15) + 'px)';
    tooltip.style.webkitTransform = transl;
    tooltip.innerHTML = value;
  };

    /* ------------------------------ Helper Functions  ----------------------- */
  // Note: You may use these in your code, but do remember that your code
  // should not have any functions defined that are unused.

  /**
   * Returns the element that has the ID attribute with the specified value.
   * @param {string} ele - element ID
   * @return {object} DOM object associated with id.
   */
  function id(ele) {
    return document.getElementById(ele);
  }

  /**
   * Returns a new element with HTML tag.
   * @param {string} tag - html tag
   * @return {object} a new DOM object associated with tag.
   */
  function newE(tag) {
    return document.createElement(tag);
  }

  /**
   * Returns the first element that matches the given CSS selector.
   * @param {string} query - CSS query selector.
   * @return {object} The first DOM object matching the query.
   */
  function qs(query) {
    return document.querySelector(query);
  }

  /**
   * Returns the array of elements that match the given CSS selector.
   * @param {string} query - CSS query selector
   * @return {object[]} array of DOM objects matching the query.
   */
  function qsa(query) {
    return document.querySelectorAll(query);
  }
})();

