(function (window, document) {

  "use strict";

  var defaults = {
    vertical_align: "top",
    columns: []
  };

  function Rowspanizer(element, options) {
    this.element = element;
    this.settings = Object.assign({}, defaults, options);
    this.init();
  }

  Rowspanizer.prototype = {
    init: function () {
      var _this = this;
      var table = this.element;
      var arr = [];

      function processElements(tagName) {
        var rows = table.querySelectorAll('tr');
        rows.forEach(function (tr) {
          var elements = tr.querySelectorAll(tagName);
          elements.forEach(function (el, d) {
            if (_this.settings.columns.length === 0 || _this.settings.columns.indexOf(d) !== -1) {
              var v_dato = el.innerHTML;
              if (typeof arr[d] !== 'undefined' && 'dato' in arr[d] && arr[d].dato === v_dato) {
                var rs = arr[d].elem.getAttribute('rowspan');
                if (rs === null || isNaN(rs)) rs = 1;
                arr[d].elem.setAttribute('rowspan', parseInt(rs) + 1);
                arr[d].elem.classList.add('rowspan-combine');
                el.classList.add('rowspan-remove');
              } else {
                arr[d] = { dato: v_dato, elem: el };
              }
            }
          });
        });

        var combinedElements = table.querySelectorAll('.rowspan-combine');
        combinedElements.forEach(function (el) {
          el.style.verticalAlign = _this.settings.vertical_align;
        });

        var removeElements = table.querySelectorAll('.rowspan-remove');
        removeElements.forEach(function (el) {
          el.remove();
        });
      }

      processElements('td');
      processElements('th');
    }
  };

  // Attach to window object
  window.rowspanizer = function (selector, options) {
    var tables = document.querySelectorAll(selector);
    tables.forEach(function (table) {
      if (!table.rowspanizer) {
        table.rowspanizer = new Rowspanizer(table, options);
      }
    });
  };

})(window, document);