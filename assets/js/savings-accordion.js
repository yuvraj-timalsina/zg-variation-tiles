/**
 * ZG Product Savings and Accordion JavaScript
 * Handles accordion functionality for included items
 */

(function ($) {
  "use strict";

  $(document).ready(function () {
    // Only run on product pages
    if (!$("body").hasClass("single-product") && !$("body").hasClass("woocommerce-page")) {
      return;
    }
    // Initialize accordion functionality
    initAccordion();

    // Handle variation changes for variable products
    handleVariationChanges();
  });

  /**
   * Initialize accordion functionality
   */
  function initAccordion() {
    $(".zg-accordion-header").on("click", function (e) {
      e.preventDefault();

      var $header = $(this);
      var $content = $header.next(".zg-accordion-content");
      var $icon = $header.find(".zg-accordion-icon");

      // Toggle active state
      $header.toggleClass("active");
      $content.toggleClass("active");

      // Animate icon rotation for dashicons
      if ($header.hasClass("active")) {
        $icon.css("transform", "rotate(180deg)");
      } else {
        $icon.css("transform", "rotate(0deg)");
      }

      // Set max-height for smooth animation
      if ($content.hasClass("active")) {
        var contentHeight = $content.find(".zg-included-items-list").outerHeight();
        $content.css("max-height", contentHeight + "px");
      } else {
        $content.css("max-height", "0px");
      }
    });

    // Keyboard accessibility
    $(".zg-accordion-header").on("keydown", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        $(this).trigger("click");
      }
    });
  }

  /**
   * Handle variation changes for variable products
   */
  function handleVariationChanges() {
    // Listen for WooCommerce variation changes
    $(document.body).on("found_variation", function (event, variation) {
      updateContentForVariation(variation);
    });

    // Listen for variation reset
    $(document.body).on("reset_data", function () {
      resetToDefaultContent();
    });
  }

  /**
   * Update content for selected variation
   */
  function updateContentForVariation(variation) {
    if (!variation) {
      return;
    }

    var $section = $(".zg-product-savings-section");
    if (!$section.length) {
      return;
    }

    // Get variation-specific data
    var variationData = variation.variation_data || {};

    // Update accordion title and content
    var $accordionTitle = $section.find(".zg-accordion-title");
    var $accordionContent = $section.find(".zg-accordion-content-inner");
    var $accordionPreview = $section.find(".zg-accordion-preview");

    // Update accordion content if variation has dropdown data
    if (variationData._vt_dd_text) {
      $accordionTitle.text("What's included?");
      $accordionContent.find(".zg-accordion-text").html(variationData._vt_dd_text);
      $section.find(".zg-included-items-accordion").show();
    } else {
      $section.find(".zg-included-items-accordion").hide();
    }

    // Update preview text if available with character limit
    if (variationData._vt_dd_preview) {
      var previewText = variationData._vt_dd_preview.trim();

      // Apply character limit if available
      if (typeof zgAccordionSettings !== "undefined" && zgAccordionSettings.previewTextLimit > 0) {
        if (previewText.length > zgAccordionSettings.previewTextLimit) {
          previewText = previewText.substring(0, zgAccordionSettings.previewTextLimit) + "...";
        }
      }

      $accordionPreview.text(previewText).show();
    } else {
      $accordionPreview.hide();
    }

    // Update savings
    var regularPrice = parseFloat(variation.display_regular_price);
    var salePrice = parseFloat(variation.display_price);

    if (regularPrice && salePrice && regularPrice > salePrice) {
      var savings = regularPrice - salePrice;
      updateSavingsDisplay(savings);
      $section.find(".zg-total-savings").show();
    } else {
      $section.find(".zg-total-savings").hide();
    }
  }

  /**
   * Reset to default content
   */
  function resetToDefaultContent() {
    var $section = $(".zg-product-savings-section");
    if (!$section.length) {
      return;
    }

    // Hide savings section
    $section.find(".zg-total-savings").hide();

    // Reset accordion to default state
    $section.find(".zg-accordion-content").removeClass("active");
    $section.find(".zg-accordion-header").removeClass("active");
    $section.find(".zg-accordion-icon").css("transform", "rotate(0deg)");
  }

  /**
   * Update the savings display with new amount
   */
  function updateSavingsDisplay(savings) {
    var $savingsAmount = $(".zg-savings-amount");
    if ($savingsAmount.length) {
      // Format the savings amount
      var formattedSavings = formatCurrency(savings);
      $savingsAmount.html(formattedSavings);
      $(".zg-total-savings").show();
    }
  }

  /**
   * Format currency amount
   */
  function formatCurrency(amount) {
    // Get WooCommerce currency settings
    var currencySymbol = wc_add_to_cart_params.currency_format_symbol || "$";
    var decimalSeparator = wc_add_to_cart_params.currency_format_decimal_sep || ".";
    var thousandSeparator = wc_add_to_cart_params.currency_format_thousand_sep || ",";
    var decimals = 0; // Force 0 decimals for savings display

    // Format the number
    var formatted = parseFloat(amount).toFixed(decimals);

    // Add thousand separators
    var parts = formatted.split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);

    return currencySymbol + parts.join(decimalSeparator);
  }

  // Expose functions globally for potential external use
  window.ZGSavingsAccordion = {
    initAccordion: initAccordion,
    updateSavingsDisplay: updateSavingsDisplay,
  };
})(jQuery);
