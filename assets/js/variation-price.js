/**
 * ZG Variation Tiles - Price and ATC Button Updates
 *
 * Uses CommerceKit's existing data and events for maximum speed
 * and compatibility with the fast image update system.
 */

jQuery(document).ready(function ($) {
  "use strict";

  // Only run on product pages
  if (!$("body").hasClass("single-product") && !$("body").hasClass("woocommerce-page")) {
    return;
  }

  var $form = $(".variations_form");
  if (!$form.length) {
    return;
  }

  var isUpdating = false;
  var lastVariationId = null;

  /**
   * Update tile prices using CommerceKit's data and events
   * This leverages the same system that handles fast image updates
   */
  function updateTilePrices() {
    // Get current selections from CommerceKit
    var currentSelections = getCurrentSelections();

    // Use CommerceKit's variation data if available
    var variations = $form.data("product_variations");
    if (!variations) {
      return;
    }

    // Update attribute titles to show current selections
    updateAttributeTitles(currentSelections);

    // Special handling for grill-only is now handled in the swatch loop below

    // Update prices for each swatch using CommerceKit's approach
    $(".cgkit-attribute-swatches .cgkit-swatch").each(function () {
      var $swatch = $(this);
      var $attributeGroup = $swatch.closest(".cgkit-attribute-swatches");
      var attribute = $attributeGroup.data("attribute");
      var swatchValue = $swatch.data("attribute-value");
      var $priceElement = $swatch.find(".tile-price");

      if ($priceElement.length) {
        // Create test selection with this swatch value
        var testSelections = Object.assign({}, currentSelections);
        testSelections[attribute] = swatchValue;

        // Special logic for grill-only
        if (swatchValue === "grill-only") {
          // For grill-only, use current controller selection
          var controllerValue = currentSelections["attribute_pa_controller"];
          if (controllerValue) {
            testSelections["attribute_pa_controller"] = controllerValue;
            testSelections["attribute_pa_front-bench"] = "none";
          }
        }

        // Find matching variation
        var matchingVariation = findVariationForSelections(variations, testSelections);
        var newPrice = "";

        if (matchingVariation && matchingVariation.price_html) {
          // For tile cards, preserve the full HTML with strikethrough formatting
          newPrice = cleanPriceHtmlForTiles(matchingVariation.price_html);
        } else {
          // Fallback to any variation with this attribute value
          var fallbackVariation = findFallbackVariation(variations, attribute, swatchValue);
          if (fallbackVariation && fallbackVariation.price_html) {
            newPrice = cleanPriceHtmlForTiles(fallbackVariation.price_html);
          }
        }

        // Update price immediately
        $priceElement.html(newPrice);
      }
    });
  }

  /**
   * Update attribute titles to show current selections instead of "No selection"
   */
  function updateAttributeTitles(currentSelections) {
    // Update each attribute group title
    $(".cgkit-attribute-swatches").each(function () {
      var $attributeGroup = $(this);
      var attribute = $attributeGroup.data("attribute");
      var $title = $attributeGroup.siblings(".cgkit-swatch-title");

      if ($title.length && attribute) {
        var selectedValue = currentSelections[attribute];

        if (selectedValue) {
          // Find the selected swatch to get its display text
          var $selectedSwatch = $attributeGroup.find('.cgkit-swatch[data-attribute-value="' + selectedValue + '"]');
          var displayText = $selectedSwatch.data("attribute-text") || selectedValue;

          // Update the title to show the selected value
          var attributeName = getAttributeDisplayName(attribute);
          $title.text(attributeName + ": " + displayText);
        } else {
          // No selection - show "No selection"
          var attributeName = getAttributeDisplayName(attribute);
          $title.text(attributeName + ": No selection");
        }
      }
    });
  }

  /**
   * Get display name for attribute
   */
  function getAttributeDisplayName(attribute) {
    var displayNames = {
      attribute_pa_controller: "CONTROLLER",
      attribute_pa_bundles: "BUNDLES",
      "attribute_pa_front-bench": "FRONT BENCH",
    };

    return displayNames[attribute] || attribute.replace(/attribute_pa_/, "").toUpperCase();
  }

  /**
   * Clean price HTML for tile cards - preserves strikethrough formatting
   */
  function cleanPriceHtmlForTiles(priceHtml) {
    if (!priceHtml) return "";

    // Clean HTML entities but preserve HTML structure for strikethrough
    var cleanPrice = priceHtml
      .replace(/Total:\s*/g, "") // Remove "Total:" prefix
      .replace(/&#36;/g, "$") // Convert HTML entities to actual symbols
      .replace(/&amp;/g, "&") // Convert HTML entities
      .replace(/&lt;/g, "<") // Convert HTML entities
      .replace(/&gt;/g, ">") // Convert HTML entities
      .replace(/&quot;/g, '"') // Convert HTML entities
      .replace(/&#39;/g, "'") // Convert HTML entities
      .trim(); // Remove extra whitespace

    return cleanPrice;
  }

  /**
   * Clean price HTML and extract the current price for ATC button
   */
  function cleanPriceHtml(priceHtml) {
    if (!priceHtml) return "";

    // Clean and format the price properly
    var cleanPrice = priceHtml
      .replace(/Total:\s*/g, "") // Remove "Total:" prefix
      .replace(/<[^>]*>/g, "") // Strip HTML tags
      .replace(/&#36;/g, "$") // Convert HTML entities to actual symbols
      .replace(/&amp;/g, "&") // Convert HTML entities
      .replace(/&lt;/g, "<") // Convert HTML entities
      .replace(/&gt;/g, ">") // Convert HTML entities
      .replace(/&quot;/g, '"') // Convert HTML entities
      .replace(/&#39;/g, "'") // Convert HTML entities
      .trim(); // Remove extra whitespace

    // Extract just the current price (after "Current price is:")
    var currentPriceMatch = cleanPrice.match(/Current price is:\s*([^\.]+)/);
    if (currentPriceMatch) {
      return currentPriceMatch[1].trim();
    } else {
      // If no "Current price is:" found, try to extract the last price
      var prices = cleanPrice.match(/\$[\d,]+\.?\d*/g);
      if (prices && prices.length > 0) {
        return prices[prices.length - 1]; // Get the last (current) price
      }
    }

    return cleanPrice;
  }

  /**
   * Handle grill-only price updates - simplified to avoid recursion
   */
  function handleGrillOnlyPriceUpdate() {
    // Just update the tile prices directly without triggering events
    // This prevents the recursion issue
    updateTilePrices();
  }

  /**
   * Find fallback variation for a specific attribute value
   */
  function findFallbackVariation(variations, attribute, value) {
    for (var i = 0; i < variations.length; i++) {
      var variation = variations[i];
      if (variation.attributes[attribute] === value) {
        return variation;
      }
    }
    return null;
  }

  /**
   * Find variation that matches the given selections
   */
  function findVariationForSelections(variations, selections) {
    for (var i = 0; i < variations.length; i++) {
      var variation = variations[i];
      var matches = true;

      // Check if this variation matches all selections
      for (var attr in selections) {
        if (variation.attributes[attr] !== selections[attr]) {
          matches = false;
          break;
        }
      }

      if (matches) {
        return variation;
      }
    }

    return null;
  }

  /**
   * Update add-to-cart button state and price
   */
  function updateATCButton(variation) {
    var $button = $(".single_add_to_cart_button");
    var $buttonText = $button.find(".elementor-button-text");

    if (variation && variation.price_html) {
      // Enable button
      $button.removeClass("disabled wc-variation-is-unavailable");
      $button.addClass("wc-variation-selected");

      // Update button text with price
      if ($buttonText.length) {
        // Clean and format the price properly
        var cleanPrice = cleanPriceHtml(variation.price_html);
        $buttonText.text("Add to Cart - " + cleanPrice);
      }
    } else {
      // Disable button
      $button.addClass("disabled wc-variation-is-unavailable");
      $button.removeClass("wc-variation-selected");

      if ($buttonText.length) {
        $buttonText.text("Select Options");
      }
    }
  }

  /**
   * Get current variation selections
   */
  function getCurrentSelections() {
    var selections = {};

    $form.find("select").each(function () {
      var $select = $(this);
      var name = $select.attr("name");
      var value = $select.val();

      if (name && value) {
        selections[name] = value;
      }
    });

    return selections;
  }

  /**
   * Check if all required attributes are selected
   */
  function isCompleteVariation() {
    var selections = getCurrentSelections();
    var requiredAttributes = [];

    // Get all required attributes from the form
    $form.find("select[required]").each(function () {
      requiredAttributes.push($(this).attr("name"));
    });

    // If no required attributes found, check if we have selections for all visible attributes
    if (requiredAttributes.length === 0) {
      $form.find("select").each(function () {
        var $select = $(this);
        if ($select.is(":visible") && $select.attr("name")) {
          requiredAttributes.push($select.attr("name"));
        }
      });
    }

    // Check if all required attributes have values
    return requiredAttributes.every(function (attr) {
      return selections[attr] && selections[attr] !== "";
    });
  }

  /**
   * Find matching variation based on current selections
   */
  function findMatchingVariation() {
    var variations = $form.data("product_variations");
    if (!variations) {
      return null;
    }

    var selections = getCurrentSelections();

    for (var i = 0; i < variations.length; i++) {
      var variation = variations[i];
      var matches = true;

      // Check if this variation matches all current selections
      for (var attr in selections) {
        if (variation.attributes[attr] !== selections[attr]) {
          matches = false;
          break;
        }
      }

      if (matches) {
        return variation;
      }
    }

    return null;
  }

  /**
   * Handle variation change
   */
  function handleVariationChange() {
    if (isUpdating) {
      return;
    }

    isUpdating = true;

    // Immediate execution for maximum speed
    var matchingVariation = findMatchingVariation();

    // Ultra-fast immediate price update
    updateTilePrices();

    if (matchingVariation && isCompleteVariation()) {
      // Update ATC button
      updateATCButton(matchingVariation);

      // Only trigger events if variation actually changed to prevent recursion
      if (matchingVariation.variation_id !== lastVariationId) {
        // Trigger WooCommerce found_variation event
        $form.trigger("found_variation", [matchingVariation]);
        lastVariationId = matchingVariation.variation_id;
      }
    } else {
      // No complete variation selected
      updateATCButton(null);

      // Only trigger hide_variation if we had a variation before
      if (lastVariationId !== null) {
        $form.trigger("hide_variation");
        lastVariationId = null;
      }
    }

    isUpdating = false;
  }

  /**
   * Handle swatch clicks - let CommerceKit handle the selection logic
   */
  function handleSwatchClick(e) {
    // Let CommerceKit handle the click, we just update prices
    setTimeout(function () {
      updateTilePrices();
    }, 0); // Use setTimeout(0) to let CommerceKit finish first
  }

  // Event handlers
  $(document).on("click", ".cgkit-swatch", handleSwatchClick);

  // Hook into CommerceKit's existing functions for maximum compatibility
  if (typeof window.cgkitUpdateAttributeSwatch === "function") {
    var originalCgkitUpdateSwatch = window.cgkitUpdateAttributeSwatch;
    window.cgkitUpdateAttributeSwatch = function (input) {
      var result = originalCgkitUpdateSwatch.call(this, input);

      // Update prices immediately after CommerceKit handles the swatch
      setTimeout(function () {
        updateTilePrices();
      }, 0);

      return result;
    };
  }

  // Hook into CommerceKit's events for maximum compatibility
  $(document).on("cgkit:swatch:selected", function () {
    // Update prices when CommerceKit handles swatch selection
    updateTilePrices();
  });

  // Handle WooCommerce variation events
  $form.on("woocommerce_variation_select_change", function () {
    handleVariationChange();
  });

  $form.on("found_variation", function (event, variation) {
    if (variation && variation.variation_id !== lastVariationId) {
      // Update prices and ATC button
      updateTilePrices();
      updateATCButton(variation);
      lastVariationId = variation.variation_id;
    }
  });

  $form.on("hide_variation", function () {
    updateATCButton(null);
  });

  // Handle form changes
  $form.on("change", "select", function () {
    handleVariationChange();
  });

  // Initial check on page load - immediate for maximum speed
  setTimeout(function () {
    handleVariationChange();
  }, 50);

  // Periodic check to ensure consistency and update all prices
  setInterval(function () {
    if (!isUpdating) {
      // Ultra-fast immediate price update
      updateTilePrices();

      var currentVariation = findMatchingVariation();
      if (currentVariation && currentVariation.variation_id !== lastVariationId) {
        // Use a timeout to prevent recursion
        setTimeout(function () {
          handleVariationChange();
        }, 10);
      }
    }
  }, 3000); // Increased interval to reduce frequency
});
