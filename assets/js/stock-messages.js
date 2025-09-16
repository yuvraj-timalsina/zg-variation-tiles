/**
 * Stock Messages and Variant Tiles Arrangement JavaScript
 */
(function ($) {
  "use strict";

  // Initialize when DOM is ready
  $(document).ready(function () {
    // Only run on product pages
    if (!$("body").hasClass("single-product") && !$("body").hasClass("woocommerce-page")) {
      return;
    }
    initializeVariantTilesArrangement();
  });

  /**
   * Initialize variant tiles arrangement styling
   */
  function initializeVariantTilesArrangement() {
    var variationsForm = document.querySelector(".variations_form");
    if (variationsForm) {
      variationsForm.classList.add("zg-variant-tiles-arranged");
    }
  }

  /**
   * Show stock message
   * @param {string} type - 'in-stock' or 'low-stock'
   */
  window.showStockMessage = function (type) {
    // Hide all stock messages first
    $("#vt-in-stock-message, #vt-low-stock-message").hide();

    // Show the appropriate message
    if (type === "in-stock") {
      $("#vt-in-stock-message").show();
    } else if (type === "low-stock") {
      $("#vt-low-stock-message").show();
    }
  };

  /**
   * Hide all stock messages
   */
  window.hideStockMessages = function () {
    $("#vt-in-stock-message, #vt-low-stock-message").hide();
  };
})(jQuery);
