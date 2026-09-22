(function ($) {
  $.fn.contentZoomSlider = function (options) {
    let $this = $(this),
    r=".ranger";
    // Default options
    let settings = $.extend(
      {
        toolContainer: "#tool-container",
        setp: 5,
        min: 50,
        max: 200,
        zoom: 100,
      },
      options
    );

    init();
    
    function init() {
      addToolBar();
      registerEvents();
      setZoomValue(settings.zoom/100);
    }

    function registerEvents() {
      $(document)
        .off("click", `${settings.toolContainer} .zoom-out`)
        .on("click", `${settings.toolContainer} .zoom-out`, function () {
          zoomOut();
        });

      $(document)
        .off("click", `${settings.toolContainer} .zoom-in`)
        .on("click", `${settings.toolContainer} .zoom-in`, function () {
          zoomIn();
        });

      $(document)
        .off("input change", `${settings.toolContainer} ${r}`)
        .on("input change", `${settings.toolContainer} ${r}`, function () {
          zoomScroll();
        });
    }

    function addToolBar() {
      $(settings.toolContainer).html(`<div class="row">
            <div class="col-sm-12 p-1 text-center zoominout d-flex flex-row align-items-center justify-content-center">
            <span class="mr-2">Zoom</span>
                <button class="zoom-out btn"><i class="fa fa-minus m-1 text-primary"></i></button>
                <input class="ranger" type="range" value="${settings.zoom / 100}" step="${settings.setp / 100}"
                    min="${settings.min / 100}"
                    max="${settings.max / 100}" />
                <button class="zoom-in btn"><i class="fa fa-plus m-1 text-primary"></i></button>
                <span class="zoom-value ml-2">${settings.zoom}%</span>
            </div>
        </div>`);
    }

    function zoomIn() {
      let zoom = getZoomValue();
      if (zoom < settings.max/100) {
        setZoomValue(zoom + settings.setp/100);
      }
    }

    function zoomOut() {
      let zoom = getZoomValue();
      if (zoom > settings.min/100) {
        setZoomValue(zoom - settings.setp/100);
      }
    }

    function zoomScroll() {
      let zoom = parseFloat($(`${settings.toolContainer} ${r}`).val());
      setZoomValue(zoom);
    }

    function getZoomValue() {
      let zoom = parseFloat($this.css("zoom"));
      if (!zoom) {
        zoom = 1;
      }
      return zoom;
    }

    function setZoomValue(zoom) {
      zoom = parseFloat(zoom);
      if (!zoom || zoom < settings.min / 100) zoom = settings.min / 100;
      if (zoom > settings.max / 100) zoom = settings.max / 100;

      // Simpan nilai zoom secara eksplisit agar plugin menjodohkan bisa
      // menghitung ulang koordinat garis dengan angka yang sama persis.
      $this
        .attr('data-content-zoom', zoom)
        .data('contentZoom', zoom)
        .css({
          zoom: zoom,
          transform: '',
          '-moz-transform': '',
          'transform-origin': ''
        });

      window.garudaContentZoom = zoom;

      $(`${settings.toolContainer} ${r}`).val(zoom);
      $(`${settings.toolContainer} .zoom-value`).text((zoom * 100).toFixed(0) + "%");

      // Trigger beberapa kali untuk mengejar reflow mobile browser.
      $(document).trigger('content-zoom-change', [zoom, $this]);
      setTimeout(function () {
        $(document).trigger('content-zoom-change', [zoom, $this]);
      }, 20);
      setTimeout(function () {
        $(document).trigger('content-zoom-change', [zoom, $this]);
      }, 120);
      setTimeout(function () {
        $(document).trigger('content-zoom-change', [zoom, $this]);
      }, 300);
    }
  };
})(jQuery);
