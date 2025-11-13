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
  var isProcessingUpdate = false;
  var isInTransition = false;
  var hasInitialized = false;

  // Performance optimization: Cache frequently used elements and data
  var cachedSwatches = null;
  var cachedVariationsMap = null;
  var cachedPriceElements = null;
  var lastSelectionsHash = null;

  /**
   * Build cache for LIGHTNING-FAST performance - matching CommerceKit speed
   */
  function buildCache() {
    if (!cachedSwatches) {
      cachedSwatches = $(".cgkit-attribute-swatches .cgkit-swatch");
      cachedPriceElements = [];

      // Ultra-fast pre-caching - direct DOM access like CommerceKit
      cachedSwatches.each(function (index) {
        var $swatch = $(this);
        var $priceElement = $swatch.find(".tile-price");
        if ($priceElement.length) {
          cachedPriceElements[index] = {
            element: $priceElement[0], // Direct DOM element for instant updates
            swatch: $swatch,
            attribute: $swatch.closest(".cgkit-attribute-swatches").data("attribute"),
            value: $swatch.data("attribute-value"),
          };
        }
      });
    }

    if (!cachedVariationsMap) {
      var variations = $form.data("product_variations");
      if (variations) {
        // Ultra-fast hash map creation - O(1) lookups like CommerceKit
        cachedVariationsMap = {};
        for (var i = 0, len = variations.length; i < len; i++) {
          var variation = variations[i];
          var key = JSON.stringify(variation.attributes);
          cachedVariationsMap[key] = variation;
        }
      }
    }
  }

  /**
   * Clear cache when needed
   */
  function clearCache() {
    cachedSwatches = null;
    cachedVariationsMap = null;
    cachedPriceElements = null;
    lastSelectionsHash = null;
  }

  /**
   * LIGHTNING-FAST price updates - matching CommerceKit image speed
   * Uses the same techniques as cgkitUpdateAttributeSwatchImage
   */
  function updateTilePrices() {
    // Prevent infinite loops
    if (isProcessingUpdate) {
      return;
    }
    isProcessingUpdate = true;

    // Get current selections
    var currentSelections = getCurrentSelections();
    var selectionsHash = JSON.stringify(currentSelections);

    // Skip if selections haven't changed
    if (lastSelectionsHash === selectionsHash) {
      isProcessingUpdate = false;
      return;
    }

    // Skip if selections are empty - prevents "No selection" flash
    if (!currentSelections || Object.keys(currentSelections).length === 0) {
      isProcessingUpdate = false;
      return;
    }

    lastSelectionsHash = selectionsHash;

    // Build cache if needed
    buildCache();

    if (!cachedPriceElements || !cachedVariationsMap) {
      return;
    }

    // Update attribute titles (only if needed)
    updateAttributeTitles(currentSelections);

    // Update ATC button with current variation (including grill-only logic)
    var currentVariation = findMatchingVariation();
    if (currentVariation) {
      updateATCButton(currentVariation);
    } else {
      // Special handling for grill-only - find variation with current controller + grill-only + front-bench=none
      if (currentSelections["attribute_pa_bundles"] === "grill-only" && currentSelections["attribute_pa_controller"]) {
        var grillOnlyVariation = findVariationForSelections($form.data("product_variations"), {
          attribute_pa_controller: currentSelections["attribute_pa_controller"],
          attribute_pa_bundles: "grill-only",
          "attribute_pa_front-bench": "none",
        });
        if (grillOnlyVariation) {
          updateATCButton(grillOnlyVariation);
        }
      }
    }

    // LIGHTNING-FAST: Direct DOM updates like CommerceKit images
    var priceElements = cachedPriceElements;

    // Prevent flash by temporarily hiding price elements during update
    var priceElementsToHide = [];
    for (var i = 0, len = priceElements.length; i < len; i++) {
      var item = priceElements[i];
      if (item && item.element) {
        priceElementsToHide.push(item.element);
        item.element.style.visibility = "hidden";
      }
    }

    // Ultra-fast loop - no array operations, no object creation
    for (var i = 0, len = priceElements.length; i < len; i++) {
      var item = priceElements[i];
      if (!item) continue;

      // Create test selections inline (faster than Object.assign)
      var testSelections = {};
      for (var attr in currentSelections) {
        testSelections[attr] = currentSelections[attr];
      }
      testSelections[item.attribute] = item.value;

      // Special logic for grill-only - use current controller + front-bench=none
      if (item.value === "grill-only" && currentSelections["attribute_pa_controller"]) {
        testSelections["attribute_pa_controller"] = currentSelections["attribute_pa_controller"];
        testSelections["attribute_pa_front-bench"] = "none";
      }

      // Ultra-fast variation lookup using pre-built hash
      var key = JSON.stringify(testSelections);
      var variation = cachedVariationsMap[key];

      if (variation && variation.price_html) {
        // DIRECT DOM UPDATE - same speed as CommerceKit images
        item.element.innerHTML = cleanPriceHtmlForTiles(variation.price_html);
      } else {
        // Fast fallback - direct loop without Object.keys/values
        var variations = Object.values(cachedVariationsMap);
        for (var j = 0, vlen = variations.length; j < vlen; j++) {
          var cachedVariation = variations[j];
          if (cachedVariation.attributes[item.attribute] === item.value) {
            // DIRECT DOM UPDATE - same speed as CommerceKit images
            item.element.innerHTML = cleanPriceHtmlForTiles(cachedVariation.price_html);
            break;
          }
        }
      }
    }

    // Show all price elements after update is complete
    for (var k = 0, hideLen = priceElementsToHide.length; k < hideLen; k++) {
      priceElementsToHide[k].style.visibility = "visible";
    }

    // Reset processing flag
    isProcessingUpdate = false;
  }

  /**
   * Update attribute titles to show current selections instead of "No selection"
   * FIXED to work with CommerceKit's actual structure using .cgkit-chosen-attribute
   * PREVENTS "No selection" flash by not updating when selections are empty
   */
  function updateAttributeTitles(currentSelections) {
    // Don't update if selections are empty - prevents "No selection" flash
    if (!currentSelections || Object.keys(currentSelections).length === 0) {
      return;
    }

    // Update each attribute group title - target the correct CommerceKit elements
    $(".cgkit-attribute-swatches").each(function () {
      var $attributeGroup = $(this);
      var attribute = $attributeGroup.data("attribute");

      // Find the .cgkit-chosen-attribute element (CommerceKit's actual structure)
      var $chosenAttribute = $attributeGroup.closest("tr").find(".cgkit-chosen-attribute");

      if ($chosenAttribute.length) {
        var selectedValue = currentSelections[attribute];

        if (selectedValue && selectedValue !== "") {
          // Find the selected swatch to get its display text
          var $selectedSwatch = $attributeGroup.find('.cgkit-swatch[data-attribute-value="' + selectedValue + '"]');
          var displayText = $selectedSwatch.data("attribute-text") || selectedValue;

          // Clean up the display text
          if (displayText) {
            displayText = displayText.replace(/-/g, " ").replace(/\b\w/g, function (l) {
              return l.toUpperCase();
            });
          }

          // Update the chosen attribute element
          $chosenAttribute.text(displayText);
          $chosenAttribute.removeClass("no-selection");
        }
      } else {
        // Fallback: try .cgkit-swatch-title
        var $title = $attributeGroup.siblings(".cgkit-swatch-title");
        if ($title.length) {
          var selectedValue = currentSelections[attribute];

          if (selectedValue && selectedValue !== "") {
            var $selectedSwatch = $attributeGroup.find('.cgkit-swatch[data-attribute-value="' + selectedValue + '"]');
            var displayText = $selectedSwatch.data("attribute-text") || selectedValue;

            if (displayText) {
              displayText = displayText.replace(/-/g, " ").replace(/\b\w/g, function (l) {
                return l.toUpperCase();
              });
            }

            var attributeName = getAttributeDisplayName(attribute);
            $title.text(attributeName + ": " + displayText);
            $title.removeClass("no-selection");
          }
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
   * Ultra-fast price cleaning with pre-compiled regex for maximum performance
   */
  var priceCleanRegex = [
    [/Total:\s*/g, ""],
    [/&#36;/g, "$"],
    [/&amp;/g, "&"],
    [/&lt;/g, "<"],
    [/&gt;/g, ">"],
    [/&quot;/g, '"'],
    [/&#39;/g, "'"],
  ];

  function cleanPriceHtmlForTiles(priceHtml) {
    if (!priceHtml) return "";

    // Ultra-fast cleaning using pre-compiled regex
    var cleanPrice = priceHtml;
    for (var i = 0; i < priceCleanRegex.length; i++) {
      cleanPrice = cleanPrice.replace(priceCleanRegex[i][0], priceCleanRegex[i][1]);
    }

    return cleanPrice.trim();
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
    // Check if this is the default variation (53829) and grill-only is selected
    var currentSelections = getCurrentSelections();
    var bundlesValue = $form.find("select[name='attribute_pa_bundles']").val();
    var controllerValue = $form.find("select[name='attribute_pa_controller']").val();

    // Block default variation (53829) only during transitions, allow it initially
    if (variation && variation.variation_id === 53829) {
      // Check if we're in a transition (when selections are being updated)
      var isTransitioning =
        !currentSelections ||
        Object.keys(currentSelections).length === 0 ||
        !bundlesValue ||
        bundlesValue === "" ||
        !controllerValue ||
        controllerValue === "";

      if (isTransitioning && hasInitialized) {
        return; // Don't update the button with the default variation during transition
      } else if (!hasInitialized) {
        hasInitialized = true;
      }
    }

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
        var newButtonText = "Add to Cart - " + cleanPrice;
        $buttonText.text(newButtonText);
      }

      // If we were in transition, show the button now
      if (isInTransition) {
        $button.css("opacity", "1");
        isInTransition = false;
      }
    } else {
      // Always keep button hidden instead of showing "Select Options"
      $button.css("opacity", "0");
      isInTransition = true;
    }
  }

  /**
   * Get current variation selections - SIMPLIFIED to use select elements (CommerceKit's actual source)
   */
  function getCurrentSelections() {
    var selections = {};

    // Read from select elements (CommerceKit's actual source of truth)
    $form.find("select").each(function () {
      var $select = $(this);
      var name = $select.attr("name");
      var value = $select.val();

      if (name && value && name.startsWith("attribute_")) {
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
    // Prevent infinite loops
    if (isProcessingUpdate) {
      return null;
    }

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
   * Handle swatch clicks - INSTANT price updates like CommerceKit images
   */
  function handleSwatchClick(e) {
    // Immediately hide all price elements to prevent flash
    var $priceElements = $(".cgkit-swatch .price, .cgkit-swatch .woocommerce-Price-amount");
    var $atcButton = $(".single_add_to_cart_button, .elementor-button-text");
    $priceElements.css("opacity", "0");
    $atcButton.css("opacity", "0");

    // Use double requestAnimationFrame for better synchronization with CommerceKit
    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        updateTilePrices();
        // Show price elements after update
        $priceElements.css("opacity", "1");
        $atcButton.css("opacity", "1");
      });
    });
  }

  // Event handlers
  $(document).on("click", ".cgkit-swatch", handleSwatchClick);

  // Hook into CommerceKit's existing functions for maximum compatibility
  if (typeof window.cgkitUpdateAttributeSwatch === "function") {
    var originalCgkitUpdateSwatch = window.cgkitUpdateAttributeSwatch;
    window.cgkitUpdateAttributeSwatch = function (input) {
      // Immediately hide all price elements to prevent flash
      var $priceElements = $(".cgkit-swatch .price, .cgkit-swatch .woocommerce-Price-amount");
      var $atcButton = $(".single_add_to_cart_button, .elementor-button-text");
      $priceElements.css("opacity", "0");
      $atcButton.css("opacity", "0");

      // Store current attribute titles before CommerceKit updates them
      var currentTitles = {};
      $(".cgkit-chosen-attribute").each(function () {
        var $element = $(this);
        var attribute = $element.closest("tr").find(".cgkit-attribute-swatches").data("attribute");
        if (attribute && $element.text() !== "No selection") {
          currentTitles[attribute] = $element.text();
        }
      });

      var result = originalCgkitUpdateSwatch.call(this, input);

      // Immediately restore titles if CommerceKit set them to "No selection"
      setTimeout(function () {
        $(".cgkit-chosen-attribute").each(function () {
          var $element = $(this);
          var attribute = $element.closest("tr").find(".cgkit-attribute-swatches").data("attribute");
          if (attribute && $element.text() === "No selection" && currentTitles[attribute]) {
            $element.text(currentTitles[attribute]);
            $element.removeClass("no-selection");
          }
        });

        updateTilePrices();
        // Show price elements after update
        $priceElements.css("opacity", "1");
        $atcButton.css("opacity", "1");
      }, 10);

      return result;
    };
  }

  // Hook into CommerceKit's cgkitUpdateAttributeSwatch2 function to prevent "No selection"
  if (typeof window.cgkitUpdateAttributeSwatch2 === "function") {
    var originalCgkitUpdateSwatch2 = window.cgkitUpdateAttributeSwatch2;
    window.cgkitUpdateAttributeSwatch2 = function (input) {
      // Immediately hide all price elements to prevent flash
      var $priceElements = $(".cgkit-swatch .price, .cgkit-swatch .woocommerce-Price-amount");
      var $atcButton = $(".single_add_to_cart_button, .elementor-button-text");
      $priceElements.css("opacity", "0");
      $atcButton.css("opacity", "0");

      // Get current selections before CommerceKit updates
      var currentSelections = getCurrentSelections();

      var result = originalCgkitUpdateSwatch2.call(this, input);

      // Immediately update with correct values to prevent "No selection" flash
      setTimeout(function () {
        updateAttributeTitles(currentSelections);
        updateTilePrices();
        // Show price elements after update
        $priceElements.css("opacity", "1");
        $atcButton.css("opacity", "1");
      }, 5);

      return result;
    };
  }

  // Hook into CommerceKit's events for maximum compatibility
  $(document).on("cgkit:swatch:selected", function () {
    // Immediately hide all price elements to prevent flash
    var $priceElements = $(".cgkit-swatch .price, .cgkit-swatch .woocommerce-Price-amount");
    var $atcButton = $(".single_add_to_cart_button, .elementor-button-text");
    $priceElements.css("opacity", "0");
    $atcButton.css("opacity", "0");

    // Use requestAnimationFrame for synchronized updates with CommerceKit images
    requestAnimationFrame(function () {
      updateTilePrices();
      // Show price elements after update
      $priceElements.css("opacity", "1");
      $atcButton.css("opacity", "1");
    });
  });

  // Listen for changes to select elements (CommerceKit's source of truth)
  $form.on("change", "select[name^='attribute_']", function () {
    // Immediately hide all price elements to prevent flash
    var $priceElements = $(".cgkit-swatch .price, .cgkit-swatch .woocommerce-Price-amount");
    var $atcButton = $(".single_add_to_cart_button, .elementor-button-text");
    $priceElements.css("opacity", "0");
    $atcButton.css("opacity", "0");

    // Update prices when selections change
    requestAnimationFrame(function () {
      updateTilePrices();
      // Show price elements after update
      $priceElements.css("opacity", "1");
      $atcButton.css("opacity", "1");
    });
  });

  // Intercept found_variation event to prevent default variation when grill-only is selected
  $form.on("found_variation", function (event, variation) {
    var currentSelections = getCurrentSelections();
    var bundlesValue = $form.find("select[name='attribute_pa_bundles']").val();
    var controllerValue = $form.find("select[name='attribute_pa_controller']").val();

    // Block default variation (53829) only during transitions, allow it initially
    if (variation && variation.variation_id === 53829) {
      // Check if we're in a transition (when selections are being updated)
      var isTransitioning =
        !currentSelections ||
        Object.keys(currentSelections).length === 0 ||
        !bundlesValue ||
        bundlesValue === "" ||
        !controllerValue ||
        controllerValue === "";

      if (isTransitioning && hasInitialized) {
        event.preventDefault();
        event.stopPropagation();
        return false;
      } else if (!hasInitialized) {
        hasInitialized = true;
      }
    }
  });

  // Special handler for bundle changes to ensure front bench visibility and ATC price updates
  $form.on("change", "select[name='attribute_pa_bundles']", function () {
    var bundleValue = $(this).val();
    var $frontBenchRow = $form.find("tr").filter(function () {
      return $(this).find('label[for="pa_front-bench"]').length > 0;
    });

    // Hide prices during transition to prevent flash
    $(".price, .single_add_to_cart_button, .elementor-button-text").css("opacity", "0");

    if (bundleValue === "grill-only") {
      // Hide front bench row
      $frontBenchRow.fadeOut(200);
      $frontBenchRow.closest("tr").fadeOut(200);

      // Log grill-only selection
    } else {
      // Show front bench row
      $frontBenchRow.fadeIn(200);
      $frontBenchRow.closest("tr").fadeIn(200);

      // CRITICAL: Ensure front-bench is set to a valid value for non-grill-only bundles
      var currentFrontBench = $form.find('select[name="attribute_pa_front-bench"]').val();
      if (currentFrontBench === "none" || !currentFrontBench) {
        // Set to a default valid value
        var newFrontBenchValue = window.lastValidFrontBenchSelection || "stainless-steel";
        $form.find('select[name="attribute_pa_front-bench"]').val(newFrontBenchValue);

        // Update visual state
        $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch').removeClass(
          "cgkit-swatch-selected zg-permanent-selected"
        );
        $(
          '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch[data-attribute-value="' +
            newFrontBenchValue +
            '"]'
        ).addClass("cgkit-swatch-selected zg-permanent-selected");

        // CRITICAL: Trigger change event to activate existing front bench logic
        setTimeout(function () {
          $form.find('select[name="attribute_pa_front-bench"]').trigger("change");
        }, 50);
      }
    }

    // CRITICAL: Trigger price updates after bundle change
    setTimeout(function () {
      updateTilePrices();

      // DETECTION: Check ATC price and active variation after switching from grill-only
      if (bundleValue !== "grill-only") {
        // Add extra delay to ensure all updates are complete
        setTimeout(function () {
          // Get fresh values after all updates
          var actualBundleValue = $form.find("select[name='attribute_pa_bundles']").val();

          // CRITICAL: Fix front-bench value for grill-only bundles
          if (actualBundleValue === "grill-only") {
            var currentFrontBench = $form.find('select[name="attribute_pa_front-bench"]').val();
            if (currentFrontBench !== "none") {
              $form.find('select[name="attribute_pa_front-bench"]').val("none");
              // Update visual state
              $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch').removeClass(
                "cgkit-swatch-selected zg-permanent-selected"
              );
              $(
                '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch[data-attribute-value="none"]'
              ).addClass("cgkit-swatch-selected zg-permanent-selected");
            }
          }

          var currentVariation = findMatchingVariation();

          // If we found the default variation (53829), try to find a better match
          if (currentVariation && currentVariation.variation_id === 53829 && actualBundleValue !== "grill-only") {
            var variations = $form.data("product_variations");
            if (variations) {
              var betterMatch = variations.find(function (v) {
                return (
                  v.attributes.attribute_pa_bundles === actualBundleValue &&
                  v.attributes.attribute_pa_controller === $form.find("select[name='attribute_pa_controller']").val() &&
                  v.attributes["attribute_pa_front-bench"] ===
                    $form.find("select[name='attribute_pa_front-bench']").val()
                );
              });
              if (betterMatch) {
                currentVariation = betterMatch;
              }
            }
          }

          var $atcButton = $(".single_add_to_cart_button");
          var atcPrice = $atcButton.find(".elementor-button-text").text();

          // If we found a better variation, update the ATC button
          if (currentVariation && currentVariation.variation_id !== 53829) {
            updateATCButton(currentVariation);
            atcPrice = $atcButton.find(".elementor-button-text").text();
          }

          // Simple: Just update ATC button with current variation
          var currentVariation = findMatchingVariation();
          if (currentVariation) {
            updateATCButton(currentVariation);
          }

          // Show prices after update
          $(".price, .single_add_to_cart_button, .elementor-button-text").css("opacity", "1");
        }, 200);
      } else {
        // Show prices for grill-only
        $(".price, .single_add_to_cart_button, .elementor-button-text").css("opacity", "1");
      }
    }, 150);
  });

  // Also handle bundle swatch clicks to ensure front bench visibility
  $(document).on(
    "click",
    '.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch',
    function (e) {
      var bundleValue = $(this).data("attribute-value");
      var $frontBenchRow = $form.find("tr").filter(function () {
        return $(this).find('label[for="pa_front-bench"]').length > 0;
      });

      // Hide prices during transition to prevent flash
      $(".price, .single_add_to_cart_button, .elementor-button-text").css("opacity", "0");

      // Use a small delay to ensure the select value is updated
      setTimeout(function () {
        if (bundleValue === "grill-only") {
          // Hide front bench row
          $frontBenchRow.fadeOut(200);
          $frontBenchRow.closest("tr").fadeOut(200);

          // Log grill-only selection
        } else {
          // Show front bench row
          $frontBenchRow.fadeIn(200);
          $frontBenchRow.closest("tr").fadeIn(200);

          // CRITICAL: Ensure front-bench is set to a valid value for non-grill-only bundles
          var currentFrontBench = $form.find('select[name="attribute_pa_front-bench"]').val();
          if (currentFrontBench === "none" || !currentFrontBench) {
            // Set to a default valid value
            var newFrontBenchValue = window.lastValidFrontBenchSelection || "stainless-steel";
            $form.find('select[name="attribute_pa_front-bench"]').val(newFrontBenchValue);

            // Update visual state
            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch').removeClass(
              "cgkit-swatch-selected zg-permanent-selected"
            );
            $(
              '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch[data-attribute-value="' +
                newFrontBenchValue +
                '"]'
            ).addClass("cgkit-swatch-selected zg-permanent-selected");

            // CRITICAL: Trigger change event to activate existing front bench logic
            setTimeout(function () {
              $form.find('select[name="attribute_pa_front-bench"]').trigger("change");
            }, 50);
          }
        }

        // CRITICAL: Trigger price updates after bundle swatch click
        setTimeout(function () {
          updateTilePrices();

          // DETECTION: Check ATC price and active variation after switching from grill-only
          if (bundleValue !== "grill-only") {
            // Add extra delay to ensure all updates are complete
            setTimeout(function () {
              // Get fresh values after all updates
              var actualBundleValue = $form.find("select[name='attribute_pa_bundles']").val();

              // CRITICAL: Fix front-bench value for grill-only bundles
              if (actualBundleValue === "grill-only") {
                var currentFrontBench = $form.find('select[name="attribute_pa_front-bench"]').val();
                if (currentFrontBench !== "none") {
                  $form.find('select[name="attribute_pa_front-bench"]').val("none");
                  // Update visual state
                  $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch').removeClass(
                    "cgkit-swatch-selected zg-permanent-selected"
                  );
                  $(
                    '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch[data-attribute-value="none"]'
                  ).addClass("cgkit-swatch-selected zg-permanent-selected");
                }
              }

              var currentVariation = findMatchingVariation();

              // If we found the default variation (53829), try to find a better match
              if (currentVariation && currentVariation.variation_id === 53829 && actualBundleValue !== "grill-only") {
                var variations = $form.data("product_variations");
                if (variations) {
                  var betterMatch = variations.find(function (v) {
                    return (
                      v.attributes.attribute_pa_bundles === actualBundleValue &&
                      v.attributes.attribute_pa_controller ===
                        $form.find("select[name='attribute_pa_controller']").val() &&
                      v.attributes["attribute_pa_front-bench"] ===
                        $form.find("select[name='attribute_pa_front-bench']").val()
                    );
                  });
                  if (betterMatch) {
                    currentVariation = betterMatch;
                  }
                }
              }

              var $atcButton = $(".single_add_to_cart_button");
              var atcPrice = $atcButton.find(".elementor-button-text").text();

              // If we found a better variation, update the ATC button
              if (currentVariation && currentVariation.variation_id !== 53829) {
                updateATCButton(currentVariation);
                atcPrice = $atcButton.find(".elementor-button-text").text();
              }

              // Simple: Just update ATC button with current variation
              var currentVariation = findMatchingVariation();
              if (currentVariation) {
                updateATCButton(currentVariation);
              }

              // Show prices after update
              $(".price, .single_add_to_cart_button, .elementor-button-text").css("opacity", "1");
            }, 200);
          } else {
            // Show prices for grill-only
            $(".price, .single_add_to_cart_button, .elementor-button-text").css("opacity", "1");
          }
        }, 50);
      }, 100);
    }
  );

  // Special handler for controller changes when grill-only is selected
  $form.on("change", "select[name='attribute_pa_controller']", function () {
    var $this = $(this);
    var newControllerValue = $this.val();
    // Get current selections with a small delay to ensure the select value is updated
    setTimeout(function () {
      var currentSelections = getCurrentSelections();

      if (currentSelections["attribute_pa_bundles"] === "grill-only") {
        // Immediately hide prices to prevent flash during grill-only controller change
        $(".price, .single_add_to_cart_button, .elementor-button-text").css("opacity", "0");

        // Use double requestAnimationFrame for better synchronization
        requestAnimationFrame(function () {
          requestAnimationFrame(function () {
            // Find the correct grill-only variation for the new controller
            var grillOnlyVariation = findVariationForSelections($form.data("product_variations"), {
              attribute_pa_controller: currentSelections["attribute_pa_controller"],
              attribute_pa_bundles: "grill-only",
              "attribute_pa_front-bench": "none",
            });

            if (grillOnlyVariation) {
              updateATCButton(grillOnlyVariation);
            }

            // Show ATC button after update
            $(".price, .single_add_to_cart_button, .elementor-button-text").css("opacity", "1");
          });
        });
      }
    }, 10);
  });

  // Aggressive approach: Monitor DOM changes to prevent "No selection" flash
  var observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.type === "childList" || mutation.type === "characterData") {
        var target = mutation.target;
        if (target.classList && target.classList.contains("cgkit-chosen-attribute")) {
          if (target.textContent === "No selection") {
            // Get current selections and update immediately
            var currentSelections = getCurrentSelections();
            var $target = $(target);
            var attribute = $target.closest("tr").find(".cgkit-attribute-swatches").data("attribute");
            if (attribute && currentSelections[attribute]) {
              var $attributeGroup = $target.closest("tr").find(".cgkit-attribute-swatches");
              var $selectedSwatch = $attributeGroup.find(
                '.cgkit-swatch[data-attribute-value="' + currentSelections[attribute] + '"]'
              );
              var displayText = $selectedSwatch.data("attribute-text") || currentSelections[attribute];
              if (displayText) {
                displayText = displayText.replace(/-/g, " ").replace(/\b\w/g, function (l) {
                  return l.toUpperCase();
                });
                target.textContent = displayText;
                target.classList.remove("no-selection");
              }
            }
          }
        }

        // Monitor ATC button text changes to catch the $2,140 flash
        if (
          target.classList &&
          (target.classList.contains("single_add_to_cart_button") || target.classList.contains("elementor-button-text"))
        ) {
          var buttonText = target.textContent || target.innerText;
          if (buttonText && buttonText.includes("$2,140")) {
            // Hide the button immediately to prevent the flash
            $(target).css("opacity", "0");

            // Set a timeout to show it again after a delay
            setTimeout(function () {
              $(target).css("opacity", "1");
            }, 200);
          }
        }
      }
    });
  });

  // Start observing
  observer.observe(document.body, {
    childList: true,
    subtree: true,
    characterData: true,
  });

  // Handle WooCommerce variation events
  $form.on("woocommerce_variation_select_change", function () {
    clearCache(); // Clear cache when variations change
    handleVariationChange();
  });

  $form.on("found_variation", function (event, variation) {
    if (variation && variation.variation_id !== lastVariationId) {
      // INSTANT price update - same speed as CommerceKit images
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
    clearCache(); // Clear cache when form changes
    handleVariationChange();
  });

  // Initial check on page load - immediate for maximum speed
  setTimeout(function () {
    handleVariationChange();
  }, 50);

  // Periodic check to ensure consistency and update all prices
  setInterval(function () {
    if (!isUpdating) {
      // INSTANT price update - same speed as CommerceKit images
      updateTilePrices();

      var currentVariation = findMatchingVariation();
      if (currentVariation && currentVariation.variation_id !== lastVariationId) {
        // Use a timeout to prevent recursion
        setTimeout(function () {
          handleVariationChange();
        }, 10);
      }
    }
  }, 1000); // Faster interval for maximum responsiveness
});
