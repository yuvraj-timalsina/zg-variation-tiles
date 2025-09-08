jQuery(document).ready(function ($) {
  // Global variable declarations to prevent undefined errors
  if (typeof window.lastBundleValue === "undefined") {
    window.lastBundleValue = null;
  }

  function updateATCButtonPrice(variation) {
    // Update button text (Elementor button structure)
    var $buttonText = $(".single_add_to_cart_button .elementor-button-text");
    if ($buttonText.length) {
      $buttonText.text("Add to Cart - $" + parseFloat(variation.display_price).toFixed(2));
    }
  }

  // Track last badge state to prevent unnecessary updates
  var lastBadgeState = {
    controller: null,
    frontBench: null,
  };

  // Optimized badge update that only runs when state actually changes
  function updateBestValueBadgeOptimized() {
    var $form = $(".variations_form");
    var currentController = $form.find('select[name="attribute_pa_controller"]').val();
    var currentFrontBench = $form.find('select[name="attribute_pa_front-bench"]').val();

    // Check if state actually changed
    if (lastBadgeState.controller === currentController && lastBadgeState.frontBench === currentFrontBench) {
      return; // No change, skip update
    }

    lastBadgeState.controller = currentController;
    lastBadgeState.frontBench = currentFrontBench;

    updateBestValueBadge();
  }

  // Function to show "Best Value" badge only for specific combination
  function updateBestValueBadge() {
    // Get current selections first
    var $form = $(".variations_form");
    var currentSelections = {};

    $form.find("select").each(function () {
      var $select = $(this);
      var attrName = $select.attr("name");
      var attrValue = $select.val();
      if (attrName && attrValue) {
        currentSelections[attrName] = attrValue;
      }
    });

    // Check if controller is Wireless Enabled (ignore bundle and front bench)
    var isWirelessEnabled = currentSelections["attribute_pa_controller"] === "wireless-enabled";

    // Check if we have both controller and front bench selected
    var currentFrontBench = currentSelections["attribute_pa_front-bench"];
    var hasValidCombination = isWirelessEnabled && currentFrontBench;

    // Always remove existing badges first to ensure clean update
    window.isRemovingBadges = true;
    var existingBadges = $(".tile-offer").length;
    $(".tile-offer").remove();
    window.isRemovingBadges = false;

    // Exit early if we don't have a valid combination
    if (!hasValidCombination) {
      return;
    }

    // Show badge if we have both controller and front bench
    if (hasValidCombination) {
      // Get variations data to find variations with offer labels
      var variations = $form.data("product_variations");
      if (!variations) {
        return;
      }

      // Find all variations with current controller and front bench that have offer labels
      var variationsWithOffers = [];

      for (var i = 0; i < variations.length; i++) {
        var variation = variations[i];
        if (
          variation.attributes &&
          variation.attributes["attribute_pa_controller"] === "wireless-enabled" &&
          variation.attributes["attribute_pa_front-bench"] === currentFrontBench &&
          variation._vt_offer_label &&
          variation._vt_offer_label.trim() !== ""
        ) {
          variationsWithOffers.push(variation);
        }
      }

      // Update badge state

      // Add badges for each variation that has an offer label
      variationsWithOffers.forEach(function (variation) {
        var bundleValue = variation.attributes["attribute_pa_bundles"];

        var $bundleCard = $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"]').find(
          '.cgkit-swatch[data-attribute-value="' + bundleValue + '"]'
        );

        // Only add badge if it doesn't already exist
        if ($bundleCard.length && $bundleCard.find(".tile-offer").length === 0) {
          var $badge = $(
            '<span class="tile-offer" style="position: absolute !important; left: 0 !important; right: 0 !important; max-width: max-content !important; margin: 0 auto !important; top: -12px !important; background: var(--vt-accent) !important; color: white !important; font-weight: bold !important; border-radius: 9999px !important; padding: 4px 12px !important; font-size: 12px !important; text-align: center !important; border: 2px solid var(--vt-accent) !important; display: block !important; visibility: visible !important; opacity: 1 !important; white-space: nowrap !important; line-height: 1 !important;">' +
              variation._vt_offer_label +
              "</span>"
          );

          // Append to the li container instead of the button
          var $liContainer = $bundleCard.closest("li.cgkit-attribute-swatch");
          $liContainer.append($badge);

          // Add a class to the badge to make it easier to target

          // Check the parent container's positioning
          var $parentContainer = $liContainer;

          // Check if badge is visible after appending
          setTimeout(function () {
            var $appendedBadge = $liContainer.find(".tile-offer");

            if ($appendedBadge.length) {
              var $currentParent = $appendedBadge.parent();
              var level = 1;
              while ($currentParent.length && level <= 10) {
                var parentTop = $currentParent.offset().top;
                var badgeTop = $appendedBadge.offset().top;
                if (badgeTop < parentTop) {
                }

                $currentParent = $currentParent.parent();
                level++;
              }
            }
          }, 100);
        } else if ($bundleCard.find(".tile-offer").length > 0) {
        }
      });

      if (variationsWithOffers.length === 0) {
      }
    }
  }

  function captureCurrentSelections() {
    var selections = {};
    $(".cgkit-attribute-swatches").each(function () {
      var attribute = $(this).data("attribute");
      var $selected = $(this).find(".cgkit-swatch-selected");
      if ($selected.length) {
        selections[attribute] = $selected.data("attribute-value");
      }
    });
    return selections;
  }

  function ensureAllVariationsEnabled() {
    $(".cgkit-swatch").removeClass("cgkit-disabled");

    var $bundleSelect = $('select[name="attribute_pa_bundles"]');
    if ($bundleSelect.length) {
      var requiredOptions = ["grill-only", "basic-bundle", "pro-bundle"];
      requiredOptions.forEach(function (option) {
        if (!$bundleSelect.find('option[value="' + option + '"]').length) {
          $bundleSelect.append(
            '<option value="' +
              option +
              '">' +
              option.replace("-", " ").replace(/\b\w/g, (l) => l.toUpperCase()) +
              "</option>"
          );
        }
      });
    }

    var $controllerSelect = $('select[name="attribute_pa_controller"]');
    if ($controllerSelect.length) {
      var controllerOptions = ["wireless-enabled", "non-wireless"];
      controllerOptions.forEach(function (option) {
        if (!$controllerSelect.find('option[value="' + option + '"]').length) {
          $controllerSelect.append(
            '<option value="' +
              option +
              '">' +
              option.replace("-", " ").replace(/\b\w/g, (l) => l.toUpperCase()) +
              "</option>"
          );
        }
      });
    }

    var $frontBenchSelect = $('select[name="attribute_pa_front-bench"]');
    if ($frontBenchSelect.length) {
      var frontBenchOptions = ["stainless-steel", "wood", "none"];
      frontBenchOptions.forEach(function (option) {
        if (!$frontBenchSelect.find('option[value="' + option + '"]').length) {
          $frontBenchSelect.append(
            '<option value="' +
              option +
              '">' +
              option.replace("-", " ").replace(/\b\w/g, (l) => l.toUpperCase()) +
              "</option>"
          );
        }
      });
    }
  }

  function handleGrillOnlySelection(beforeSelections) {
    // Hide front bench and set to none
    $("#pa_front-bench").val("none");
    $('ul[data-attribute="attribute_pa_front-bench"]').parents("tr").hide();

    // Preserve controller selection
    var preservedController = beforeSelections["attribute_pa_controller"];
    if (preservedController) {
      $("#pa_controller").val(preservedController);
      $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch').removeClass(
        "cgkit-swatch-selected"
      );
      $(
        '.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch[data-attribute-value="' +
          preservedController +
          '"]'
      ).addClass("cgkit-swatch-selected");
    }

    // Trigger variation lookup
    setTimeout(function () {
      var $form = $(".variations_form");
      var currentController = $("#pa_controller").val();
      if (!currentController && preservedController) {
        $("#pa_controller").val(preservedController);
      }

      $form.trigger("woocommerce_variation_select_change");
      $form.trigger("check_variations");

      // Find and trigger correct variation
      var variations = $form.data("product_variations");
      var finalController = $("#pa_controller").val();

      if (variations && finalController) {
        var targetAttributes = {
          attribute_pa_bundles: "grill-only",
          attribute_pa_controller: finalController,
          "attribute_pa_front-bench": "none",
        };

        var correctVariation = variations.find(function (variation) {
          return Object.keys(targetAttributes).every(function (attr) {
            return variation.attributes[attr] === targetAttributes[attr];
          });
        });

        if (correctVariation) {
          setTimeout(function () {
            $form.trigger("found_variation", [correctVariation]);
          }, 50);
        }

        updateBundleCardPrices(variations, finalController);
      }
    }, 100);
  }

  function handleBundleSelection(attr_value) {
    // Show front bench for non-grill-only bundles
    $('ul[data-attribute="attribute_pa_front-bench"]').parents("tr").show();

    var currentFrontBench = $("#pa_front-bench").val();
    var selectedSwatch = $(
      '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch-selected'
    );
    var visuallySelected = selectedSwatch.length ? selectedSwatch.data("attribute-value") : null;
    var shouldUseDefault =
      (!currentFrontBench || currentFrontBench === "none") && (!visuallySelected || visuallySelected === "none");

    if (shouldUseDefault) {
      // Set default to stainless-steel
      $("#pa_front-bench").val("stainless-steel");
      $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch').removeClass(
        "cgkit-swatch-selected"
      );
      $(
        '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch[data-attribute-value="stainless-steel"]'
      ).addClass("cgkit-swatch-selected");
    } else {
      // Preserve user selection
      var valueToPreserve = visuallySelected || currentFrontBench;
      $("#pa_front-bench").val(valueToPreserve);
      $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch').removeClass(
        "cgkit-swatch-selected"
      );
      $(
        '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch[data-attribute-value="' +
          valueToPreserve +
          '"]'
      ).addClass("cgkit-swatch-selected");
    }

    // Trigger variation lookup
    setTimeout(function () {
      var $form = $(".variations_form");
      $form.trigger("woocommerce_variation_select_change");
      $form.trigger("check_variations");
    }, 100);
  }

  function restorePreservedSelections(beforeSelections, attr_name) {
    Object.keys(beforeSelections).forEach(function (preservedAttr) {
      if (preservedAttr !== attr_name && preservedAttr !== "attribute_pa_front-bench") {
        var preservedValue = beforeSelections[preservedAttr];
        var $preservedSwatch = $(
          '.cgkit-attribute-swatches[data-attribute="' +
            preservedAttr +
            '"] .cgkit-swatch[data-attribute-value="' +
            preservedValue +
            '"]'
        );

        if ($preservedSwatch.length && !$preservedSwatch.hasClass("cgkit-swatch-selected")) {
          var selectName = preservedAttr.replace("attribute_", "");
          $("#" + selectName).val(preservedValue);
          $('.cgkit-attribute-swatches[data-attribute="' + preservedAttr + '"] .cgkit-swatch').removeClass(
            "cgkit-swatch-selected"
          );
          $preservedSwatch.addClass("cgkit-swatch-selected");
        }
      }
    });
  }

  // DEEP INTEGRATION: Hook into CommerceKit's core update function
  if (typeof window.cgkitUpdateAttributeSwatch === "function") {
    var originalCgkitUpdateSwatch = window.cgkitUpdateAttributeSwatch;
    window.cgkitUpdateAttributeSwatch = function (input) {
      var beforeSelections = captureCurrentSelections();
      var parent = input.closest(".cgkit-attribute-swatches");
      var attr_name = parent.getAttribute("data-attribute");
      var attr_value = input.getAttribute("data-attribute-value");

      ensureAllVariationsEnabled();
      var result = originalCgkitUpdateSwatch.call(this, input);

      // Update badges and savings
      setTimeout(function () {
        updateBestValueBadgeOptimized();
        enforceSelectionStates();
      }, 100);

      // Handle business logic
      setTimeout(function () {
        if (attr_name === "attribute_pa_bundles") {
          if (attr_value === "grill-only") {
            handleGrillOnlySelection(beforeSelections);
          } else if (attr_value !== "") {
            handleBundleSelection(attr_value);
          }
        }

        restorePreservedSelections(beforeSelections, attr_name);
        ensureAllVariationsEnabled();
      }, 50);

      return result;
    };
  }

  function restoreLostSelections(currentSelections, form) {
    Object.keys(currentSelections).forEach(function (attr) {
      var value = currentSelections[attr];
      var $dropdown = form.querySelector('[name="' + attr.replace("attribute_", "") + '"]');
      var $swatch = form.querySelector(
        '.cgkit-attribute-swatches[data-attribute="' + attr + '"] .cgkit-swatch[data-attribute-value="' + value + '"]'
      );

      // Check if selection was lost
      if ($dropdown && $dropdown.value !== value) {
        // Restore dropdown value
        $dropdown.value = value;

        // Restore visual swatch
        if ($swatch) {
          // Remove all selections from this attribute
          var $allSwatches = form.querySelectorAll(
            '.cgkit-attribute-swatches[data-attribute="' + attr + '"] .cgkit-swatch'
          );
          $allSwatches.forEach(function (swatch) {
            swatch.classList.remove("cgkit-swatch-selected");
          });

          // Add selection to correct swatch
          $swatch.classList.add("cgkit-swatch-selected");
        }
      }
    });
  }

  // CRITICAL: Hook into cgkitUpdateAvailableAttributes - this is what clears selections!
  if (typeof window.cgkitUpdateAvailableAttributes === "function") {
    var originalCgkitUpdate = window.cgkitUpdateAvailableAttributes;
    window.cgkitUpdateAvailableAttributes = function (form) {
      var currentSelections = captureCurrentSelections();
      ensureAllVariationsEnabled();
      var result = originalCgkitUpdate.call(this, form);

      // Restore selections that CommerceKit might have cleared
      setTimeout(function () {
        restoreLostSelections(currentSelections, form);
        ensureAllVariationsEnabled();
      }, 5);

      return result;
    };
  }

  // Intercept variation updates to preserve selections
  $(document).on("woocommerce_update_variation_values", function () {
    setTimeout(function () {
      ensureAllVariationsEnabled();
    }, 50);
  });

  // Also handle other events that might disable variations and update prices
  $(document).on("woocommerce_variation_select_change", function () {
    setTimeout(function () {
      ensureAllVariationsEnabled();

      // Trigger price updates if grill-only is selected
      if ($("#pa_bundles").val() === "grill-only") {
        handleGrillOnlyPriceUpdate();
      }
    }, 50);
  });

  function handleGrillOnlyPriceUpdate() {
    var $form = $(".variations_form");
    var variations = $form.data("product_variations");
    if (!variations) return;

    var currentSelection = {
      attribute_pa_bundles: "grill-only",
      "attribute_pa_front-bench": "none",
    };

    // Get all current selections
    $form.find("select").each(function () {
      var attrName = $(this).attr("name");
      var attrValue = $(this).val();
      if (attrValue && attrName !== "attribute_pa_bundles" && attrName !== "attribute_pa_front-bench") {
        currentSelection[attrName] = attrValue;
      }
    });

    // Find matching variation
    var matchingVariation = variations.find(function (variation) {
      return Object.keys(currentSelection).every(function (attr) {
        return variation.attributes[attr] === currentSelection[attr];
      });
    });

    // Trigger found_variation event to update prices
    if (matchingVariation) {
      $form.trigger("found_variation", [matchingVariation]);
    }
  }

  // Function to update bundle card prices based on current controller and front bench selection
  function updateBundleCardPrices(variations, controllerValue, currentFrontBench) {
    if (!variations || !controllerValue) {
      return;
    }

    var lastAttribute = "attribute_pa_bundles";
    var availableBundles = {};

    // First pass: get exact matches for current front bench (except grill-only)
    variations.forEach(function (variation) {
      if (variation.attributes && variation.attributes[lastAttribute]) {
        var bundle = variation.attributes[lastAttribute];
        var isMatching = variation.attributes["attribute_pa_controller"] === controllerValue;

        if (isMatching) {
          // For all bundles, prioritize current front bench selection
          if (bundle && !availableBundles[bundle]) {
            // If we have a current front bench selection, try to match it first
            if (currentFrontBench && variation.attributes["attribute_pa_front-bench"] === currentFrontBench) {
              availableBundles[bundle] = variation.price_html;
            }
            // For grill-only, always accept variations with front-bench as "none" (regardless of currentFrontBench)
            else if (bundle === "grill-only" && variation.attributes["attribute_pa_front-bench"] === "none") {
              availableBundles[bundle] = variation.price_html;
            }
          }
        }
      }
    });

    // Second pass: get fallback prices for missing bundles (except grill-only if front bench changed)
    variations.forEach(function (variation) {
      if (variation.attributes && variation.attributes[lastAttribute]) {
        var bundle = variation.attributes[lastAttribute];
        var isMatching = variation.attributes["attribute_pa_controller"] === controllerValue;

        if (isMatching) {
          // For all bundles, accept any front-bench value as fallback
          if (bundle && !availableBundles[bundle]) {
            // For grill-only, only accept variations with front-bench as "none"
            if (bundle === "grill-only" && variation.attributes["attribute_pa_front-bench"] === "none") {
              availableBundles[bundle] = variation.price_html;
            }
            // For other bundles, accept any front-bench value
            else if (bundle !== "grill-only") {
              availableBundles[bundle] = variation.price_html;
            }
          }
        }
      }
    });

    // Update prices for each bundle
    Object.keys(availableBundles).forEach(function (bundle) {
      var priceHtml = availableBundles[bundle];
      var cleanPriceHtml = "";

      if (priceHtml) {
        // Remove "Total:" text and colon, keep everything else
        cleanPriceHtml = priceHtml.replace(/Total:\s*/g, "");
      }

      var $priceElement = $('.cgkit-attribute-swatches[data-attribute="' + lastAttribute + '"]')
        .find('.cgkit-swatch[data-attribute-value="' + bundle + '"]')
        .find(".tile-price");

      if ($priceElement.length && cleanPriceHtml) {
        // Simple update without complex comparison (following old plugin approach)
        $priceElement.html(cleanPriceHtml);
      }
    });

    // Simplified approach - let CommerceKit handle price display naturally
  }

  // Initial setup
  setTimeout(function () {
    ensureAllVariationsEnabled();
  }, 1000);

  $(document).on("found_variation", "form.cart", function (event, variation) {
    // Log current form selections for comparison
    var currentSelections = {};
    $("form.cart select").each(function () {
      currentSelections[$(this).attr("name")] = $(this).val();
    });

    // Clear any existing savings display
    $("#vt-total-savings").remove();

    // Update ATC button price using central function
    updateATCButtonPrice(variation);

    // Clear any existing badge display
    $("#vt-offer-badge").remove();
    $("#vt-accordion-container").remove();

    // Add badge and accordion if present in variation data
    if (variation && variation.vt_offer_label) {
      var badgeHtml = '<div id="vt-offer-badge" class="vt-offer-badge">' + variation.vt_offer_label + "</div>";
      $(".single_add_to_cart_button").before(badgeHtml);
    }

    // Old accordion code removed - now handled by Elementor widget
  });

  // Hide None Buttons
  $('button.swatch.cgkit-swatch[data-attribute-value="none"]').parent("li").hide();
  var isChangedtoNone = false;

  $(".variations_form").on("woocommerce_variation_select_change", function (e, variation) {
    var data = $(this).data("product_variations");
    var $form = $(this);
    // Get all selected attributes
    var selectedAttributes = {};
    $form.find(".cgkit-attribute-swatches .cgkit-swatch-selected").each(function () {
      var attributeName = $(this).closest(".cgkit-attribute-swatches").data("attribute");
      var attributeValue = $(this).data("attribute-value");
      selectedAttributes[attributeName] = attributeValue;
    });
    // Get the attribute names
    var attributeKeys = Object.keys(selectedAttributes);
    // Check if at least one attribute is selected
    if (attributeKeys.length < 2) {
      return; // Exit if not enough attributes are selected
    }
    // Determine the bundle attribute specifically
    var bundleAttribute = null;
    if (selectedAttributes["attribute_pa_bundles"]) {
      bundleAttribute = "attribute_pa_bundles";
    } else if (selectedAttributes["attribute_pa_bundle"]) {
      bundleAttribute = "attribute_pa_bundle";
    }

    if (!bundleAttribute) {
      return; // Exit if no bundle attribute found
    }

    var availableBundles = {};
    var availableBadgeBundles = {};
    // Filter variations that match selected attributes (excluding the bundle attribute)
    data.forEach(function (variation) {
      var isMatching = Object.keys(selectedAttributes).every(function (attr) {
        if (attr === bundleAttribute) return true; // Skip bundle attribute in matching
        return variation.attributes[attr] === selectedAttributes[attr];
      });
      if (isMatching) {
        var bundle = variation.attributes[bundleAttribute];
        var attr = variation.attributes["attribute_pa_front-bench"];
        if (bundle && !availableBundles[bundle]) {
          availableBundles[bundle] = variation.price_html;
        }
        if (variation.price_difference_badge && !availableBadgeBundles[attr]) {
          availableBadgeBundles[attr] = variation.price_difference_badge;
        }
      }
    });
    // Update with new prices (following old plugin's smooth approach)
    Object.keys(availableBundles).forEach(function (bundle) {
      var priceHtml = availableBundles[bundle];
      var cleanPriceHtml = "";

      if (priceHtml) {
        cleanPriceHtml = priceHtml.replace(/Total:\s*/g, "");
      }

      var $target = $('.cgkit-attribute-swatches[data-attribute="' + bundleAttribute + '"]')
        .find('.cgkit-swatch[data-attribute-value="' + bundle + '"]')
        .find(".tile-price");

      // Update content directly without clearing first (prevents layout shifts)
      if (cleanPriceHtml) {
        $target.html(cleanPriceHtml);
      }
    });
    Object.keys(availableBadgeBundles).forEach(function (bundle) {
      var badgeHtml = availableBadgeBundles[bundle];
      if (!badgeHtml) return;
      var $target = $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"]').find(
        '.cgkit-swatch[data-attribute-value="' + bundle + '"]'
      );
      if ($target.find(".raw-badge").length < 0) {
        $target.append(badgeHtml);
      } else {
        $target.find(".raw-badge").replaceWith(badgeHtml);
      }
    });

    // Don't update bundle swatch images when switching bundles - keep term images as they are
    // Images are related to other variation selections (controller, front bench, etc.)

    // Handle None Terms
    if ($("#pa_bundles").val() !== "") {
      $('button[data-attribute-value="none"]').each(function (i, btn) {
        $(btn).parent("li").hide();
        $parentUl = $(btn).parents("ul.cgkit-attribute-swatches");
        setTimeout(function () {
          $disLiCount = $parentUl.find("li").length - $parentUl.find(".cgkit-disabled").length;
          if ($disLiCount == 1 && !isChangedtoNone) {
            isChangedtoNone = true;
          }
        }, 300);
      });
    }
  });

  // Also trigger image updates when any swatch is clicked
  $(document).on("click", ".cgkit-attribute-swatches .cgkit-swatch", function () {
    var $form = $(".variations_form");
    if ($form.length) {
      // Get current selected attributes
      var selectedAttributes = {};
      $form.find(".cgkit-attribute-swatches .cgkit-swatch-selected").each(function () {
        var attributeName = $(this).closest(".cgkit-attribute-swatches").data("attribute");
        var attributeValue = $(this).data("attribute-value");
        selectedAttributes[attributeName] = attributeValue;
      });

      // Find the bundle attribute
      var bundleAttribute = null;
      if (selectedAttributes["attribute_pa_bundles"]) {
        bundleAttribute = "attribute_pa_bundles";
      } else if (selectedAttributes["attribute_pa_bundle"]) {
        bundleAttribute = "attribute_pa_bundle";
      }

      // Don't update bundle swatch images when switching bundles - keep term images as they are
      // Images are related to other variation selections (controller, front bench, etc.)
    }
  });

  // Initialize previous values and trigger initial image sync when page loads
  $(document).ready(function () {
    // Initialize previous values to track changes
    var $form = $(".variations_form");
    if ($form.length) {
      window.previousControllerValue = $form.find('select[name="attribute_pa_controller"]').val();
      window.previousFrontBenchValue = $form.find('select[name="attribute_pa_front-bench"]').val();
    }

    setTimeout(function () {
      var $form = $(".variations_form");
      if ($form.length) {
        var selectedAttributes = {};
        $form.find(".cgkit-attribute-swatches .cgkit-swatch-selected").each(function () {
          var attributeName = $(this).closest(".cgkit-attribute-swatches").data("attribute");
          var attributeValue = $(this).data("attribute-value");
          selectedAttributes[attributeName] = attributeValue;
        });

        var bundleAttribute = null;
        if (selectedAttributes["attribute_pa_bundles"]) {
          bundleAttribute = "attribute_pa_bundles";
        } else if (selectedAttributes["attribute_pa_bundle"]) {
          bundleAttribute = "attribute_pa_bundle";
        }

        // Update images on initial load to show correct variation images
        if (bundleAttribute && selectedAttributes && typeof selectedAttributes === "object") {
          debouncedUpdateBundleSwatchImages($form, selectedAttributes, bundleAttribute);
        }
      }
    }, 500);
  });

  // Debounce function to prevent rapid-fire updates
  function debounce(func, wait) {
    var timeout;
    return function executedFunction() {
      var later = function () {
        clearTimeout(timeout);
        func();
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // Track current update state to prevent overlapping updates
  var imageUpdateInProgress = false;
  var pendingImageUpdate = null;

  // Function to update bundle swatch images (improved to prevent flickering during fast switching)
  function updateBundleSwatchImages($form, selectedAttributes, bundleAttribute) {
    // Validate parameters
    if (!selectedAttributes || typeof selectedAttributes !== "object") {
      return;
    }

    // Only allow updates for bundle attributes, not controller or front bench
    if (bundleAttribute !== "attribute_pa_bundles" && bundleAttribute !== "attribute_pa_bundle") {
      return;
    }

    // Check global flag to prevent variation searching
    if (window.preventVariationSearch) {
      return;
    }

    // Prevent infinite loops
    if (window.isProcessingVariationChange) {
      return;
    }

    // Prevent image updates when grill-only is selected with non-wireless controller
    if (
      selectedAttributes["attribute_pa_bundles"] === "grill-only" &&
      selectedAttributes["attribute_pa_controller"] === "non-wireless"
    ) {
      return;
    }

    // If an update is already in progress, queue this one
    if (imageUpdateInProgress) {
      pendingImageUpdate = {
        $form: $form,
        selectedAttributes: selectedAttributes,
        bundleAttribute: bundleAttribute,
      };
      return;
    }

    imageUpdateInProgress = true;

    var variations = $form.data("product_variations");
    if (!variations) {
      imageUpdateInProgress = false;
      return;
    }

    // Get only image-type bundle swatches
    var $bundleSwatches = $(
      '.cgkit-attribute-swatches[data-attribute="' +
        bundleAttribute +
        '"] .cgkit-attribute-swatch.cgkit-image .cgkit-swatch'
    );

    var updatePromises = [];

    $bundleSwatches.each(function () {
      var $swatch = $(this);
      var bundleValue = $swatch.data("attribute-value");

      // Only process bundle swatches, not controller or front bench swatches
      if (bundleAttribute !== "attribute_pa_bundles" && bundleAttribute !== "attribute_pa_bundle") {
        return;
      }

      // Create the combination we want to match
      var targetCombination = {};

      // Copy all selected attributes except the bundle attribute
      for (var attr in selectedAttributes) {
        if (attr !== bundleAttribute) {
          targetCombination[attr] = selectedAttributes[attr];
        }
      }

      // Add the specific bundle value we're looking for
      targetCombination[bundleAttribute] = bundleValue;

      // Special handling for Grill Only - find the best matching variation
      if (bundleValue === "grill-only") {
        // For grill-only, we need to find a variation that matches the controller
        // but we don't force front-bench to 'none' as it might not exist in variations
        var controllerValue = selectedAttributes["attribute_pa_controller"];
        if (controllerValue) {
          // For grill-only, we ONLY care about the controller, not other attributes
          // This prevents issues when switching FROM other bundles TO grill-only
          var controllerMatch = null;

          for (var i = 0; i < variations.length; i++) {
            var variation = variations[i];

            // Check for controller match (grill-only + controller) - this is the primary match
            if (
              variation.attributes["attribute_pa_bundles"] === "grill-only" &&
              variation.attributes["attribute_pa_controller"] === controllerValue
            ) {
              controllerMatch = variation;
              break; // Use the first match we find
            }
          }

          // Use controller match for grill-only
          if (controllerMatch) {
            matchingVariation = controllerMatch;
          }
        } else {
        }
      } else {
        // For non-grill-only bundles, use normal matching logic
        // Find the matching variation for this bundle with current attributes
        for (var i = 0; i < variations.length; i++) {
          var variation = variations[i];
          var isMatch = true;

          // Check if this variation matches our target combination exactly
          for (var attr in targetCombination) {
            if (variation.attributes[attr] !== targetCombination[attr]) {
              isMatch = false;
              break;
            }
          }

          if (isMatch) {
            matchingVariation = variation;
            break;
          }
        }
      }

      // Update the swatch image if we found a matching variation
      if (matchingVariation && matchingVariation.image) {
        var $swatchImg = $swatch.find("img");
        if ($swatchImg.length) {
          // Create a promise for this image update
          var updatePromise = new Promise(function (resolve) {
            // Check if this is still the current selection before updating
            var currentBundleValue = $form.find('select[name="' + bundleAttribute + '"]').val();
            if (currentBundleValue !== bundleValue) {
              resolve(); // Skip update if selection changed
              return;
            }

            // Preload the new image to prevent flickering
            var newImg = new Image();
            newImg.onload = function () {
              // Double-check selection hasn't changed during load
              var currentBundleValue = $form.find('select[name="' + bundleAttribute + '"]').val();
              if (currentBundleValue === bundleValue) {
                $swatchImg.attr("src", matchingVariation.image.src);
                $swatchImg.attr("srcset", matchingVariation.image.srcset || "");
                $swatchImg.attr("sizes", matchingVariation.image.sizes || "");
                $swatchImg.attr("alt", matchingVariation.image.alt || "");
              }
              resolve();
            };
            newImg.onerror = function () {
              resolve(); // Resolve even on error to continue
            };
            newImg.src = matchingVariation.image.src;
          });

          updatePromises.push(updatePromise);
        } else {
          // If no image exists, create one
          var $newImg = $(
            '<img src="' + matchingVariation.image.src + '" alt="' + (matchingVariation.image.alt || "") + '" />'
          );
          $swatch.prepend($newImg);
        }
      } else {
      }
    });

    // Wait for all image updates to complete
    Promise.all(updatePromises).then(function () {
      imageUpdateInProgress = false;

      // Process any pending update
      if (pendingImageUpdate) {
        var pending = pendingImageUpdate;
        pendingImageUpdate = null;
        setTimeout(function () {
          updateBundleSwatchImages(pending.$form, pending.selectedAttributes, pending.bundleAttribute);
        }, 50);
      }
    });
  }

  // Debounced version for rapid switching
  var debouncedUpdateBundleSwatchImages = debounce(function ($form, selectedAttributes, bundleAttribute) {
    // Check if selectedAttributes is defined
    if (!selectedAttributes || typeof selectedAttributes !== "object") {
      return;
    }

    // Check global flag to prevent variation searching
    if (window.preventVariationSearch) {
      return;
    }

    // Prevent infinite loops
    if (window.isProcessingVariationChange) {
      return;
    }

    // Prevent image updates when grill-only is selected with non-wireless controller
    if (
      selectedAttributes["attribute_pa_bundles"] === "grill-only" &&
      selectedAttributes["attribute_pa_controller"] === "non-wireless"
    ) {
      return;
    }
    updateBundleSwatchImages($form, selectedAttributes, bundleAttribute);
  }, 150);

  $(".variations_form").on("woocommerce_variation_select_change", function (e, v) {});
  $(document).on("show_variation", function (event, variation) {});

  $(document.body).on("woocommerce_variation_has_changed", function (variant, obj) {
    isChangedtoNone = false;
  });

  // Handle variation resets
  $(document).on("woocommerce_reset_variations", function () {
    setTimeout(function () {
      ensureAllVariationsEnabled();
      // Hide stock messages when variations are reset
      $("#vt-in-stock-message").hide();
      $("#vt-low-stock-message").hide();
    }, 100);
  });

  // Additional logic to maintain front bench hiding for grill-only
  $(document).on("woocommerce_update_variation_values woocommerce_variation_select_change", function () {
    // If grill-only is selected, ensure front bench stays hidden
    if ($("#pa_bundles").val() === "grill-only") {
      setTimeout(function () {
        $('ul[data-attribute="attribute_pa_front-bench"]').parents("tr").hide();

        // Update bundle images when controller changes and grill-only is selected
        var currentController = $("#pa_controller").val();
        if (currentController) {
          updateAllBundleImagesForGrillOnly(currentController);
        }
      }, 50);
    }
  });

  $(document).on("click", '.cgkit-swatch[data-attribute-value="grill-only"]', function (ele) {
    // Set global flag to prevent variation searching only for image updates
    window.preventVariationSearch = true;

    var $clickedSwatch = $(ele.target);
    var $form = $(".variations_form");

    // Log controller swatch state before changes
    var $controllerSwatches = $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch');
    $controllerSwatches.each(function (index) {
      var $swatch = $(this);
    });

    // Preserve ALL current selections using CommerceKit-aware system
    var allCurrentSelections = captureCurrentSelections();

    // PREVENT controller selection loss by immediately preserving it
    var $currentControllerSwatch = $(
      '.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch.cgkit-swatch-selected'
    );
    var currentControllerValue = $currentControllerSwatch.data("attribute-value");

    // If no visual selection, check the actual form value
    if (!currentControllerValue) {
      currentControllerValue = $("#pa_controller").val();
    }

    // If still no controller value, check if there's a previous selection stored
    if (!currentControllerValue && window.previousControllerValue) {
      currentControllerValue = window.previousControllerValue;
    }

    // Store the current controller value for future reference
    if (currentControllerValue) {
      window.previousControllerValue = currentControllerValue;
    }

    // Handle grill-only selection WITHOUT affecting controller
    if (!$clickedSwatch.hasClass("cgkit-swatch-selected")) {
      // Remove selection from other bundle swatches ONLY
      $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch').removeClass(
        "cgkit-swatch-selected"
      );
      // Add selection to grill-only
      $clickedSwatch.addClass("cgkit-swatch-selected");
      $("#pa_bundles").val("grill-only");
    } else {
      // If already selected, ensure it stays selected
      $clickedSwatch.addClass("cgkit-swatch-selected");
      $("#pa_bundles").val("grill-only");
    }

    // Ensure the dropdown value is properly set and trigger change event
    var $bundleDropdown = $("#pa_bundles");
    if ($bundleDropdown.val() !== "grill-only") {
      $bundleDropdown.val("grill-only").trigger("change");
    }

    // Set front bench to "none" and hide the front bench selection for grill-only
    $("#pa_front-bench").val("none");
    $('ul[data-attribute="attribute_pa_front-bench"]').parents("tr").hide();
    $(".single_add_to_cart_button").removeClass("disabled");

    // Trigger change event to ensure proper state management
    $("#pa_front-bench").trigger("change");

    // IMMEDIATELY ensure controller selection is maintained (prevent loss)
    if (currentControllerValue && currentControllerValue !== "") {
      // Find the target controller swatch
      var $targetControllerSwatch = $(
        '.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch[data-attribute-value="' +
          currentControllerValue +
          '"]'
      ).filter(function () {
        return $(this).width() > 0 && $(this).height() > 0;
      });

      if ($targetControllerSwatch.length) {
        // Ensure the controller swatch is selected (don't remove others first to prevent flicker)
        if (!$targetControllerSwatch.hasClass("cgkit-swatch-selected")) {
          // Only remove selection from other controller swatches if this one isn't already selected
          $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch').removeClass(
            "cgkit-swatch-selected"
          );
          $targetControllerSwatch.addClass("cgkit-swatch-selected");
        }
        // Ensure form value is set
        $("#pa_controller").val(currentControllerValue);
      }
    } else {
    }

    // Reset the preventVariationSearch flag after a shorter delay
    setTimeout(function () {
      window.preventVariationSearch = false;

      // Ensure grill-only selection is maintained
      var $grillOnlySwatch = $('.cgkit-swatch[data-attribute-value="grill-only"]');
      if ($grillOnlySwatch.length && !$grillOnlySwatch.hasClass("cgkit-swatch-selected")) {
        $grillOnlySwatch.addClass("cgkit-swatch-selected");
        $("#pa_bundles").val("grill-only");
      }

      // Ensure front bench is properly set for grill-only
      if ($("#pa_bundles").val() === "grill-only") {
        $("#pa_front-bench").val("none");
        $('ul[data-attribute="attribute_pa_front-bench"]').parents("tr").hide();
      }

      // Update all bundle swatch images to match current variation when grill-only is selected
      if (currentControllerValue && currentControllerValue !== "") {
        updateAllBundleImagesForGrillOnly(currentControllerValue);
      }

      // CRITICAL: Trigger variation check to ensure cart button is enabled
      var $form = $(".variations_form");
      if ($form.length) {
        // Force variation check
        $form.trigger("check_variations");

        // Also trigger variation change to update prices
        $form.trigger("woocommerce_variation_select_change");

        // Ensure cart button is enabled for grill-only
        setTimeout(function () {
          $(".single_add_to_cart_button").removeClass("disabled");
          // Call the grill-only variation handler
          ensureGrillOnlyVariation();
        }, 100);
      }
    }, 300);
  });

  // Function to update all bundle swatch images when grill-only is selected
  function updateAllBundleImagesForGrillOnly(controllerValue) {
    var $form = $(".variations_form");
    var variations = $form.data("product_variations");

    if (!variations) {
      return;
    }

    // Get all bundle swatches (Basic Bundle, Pro Bundle, Grill Only)
    var $allBundleSwatches = $(
      '.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-attribute-swatch.cgkit-image .cgkit-swatch'
    );

    $allBundleSwatches.each(function () {
      var $swatch = $(this);
      var bundleValue = $swatch.data("attribute-value");
      var $swatchImg = $swatch.find("img");

      if (!$swatchImg.length) {
        return;
      }

      // Find the best matching variation for this bundle with current controller
      var matchingVariation = null;

      for (var i = 0; i < variations.length; i++) {
        var variation = variations[i];

        // For each bundle, find a variation that matches the current controller
        if (variation.attributes["attribute_pa_bundles"] === bundleValue) {
          // For grill-only, we only care about controller
          if (bundleValue === "grill-only") {
            if (variation.attributes["attribute_pa_controller"] === controllerValue) {
              matchingVariation = variation;
              break;
            }
          } else {
            // For other bundles, try to find a variation with current controller
            // If no exact match, use the first available variation for this bundle
            if (variation.attributes["attribute_pa_controller"] === controllerValue) {
              matchingVariation = variation;
              break;
            } else if (!matchingVariation) {
              // Fallback to first variation for this bundle
              matchingVariation = variation;
            }
          }
        }
      }

      // Update the image if we found a matching variation
      if (matchingVariation && matchingVariation.image) {
        // Preload the image to prevent flickering
        var newImg = new Image();
        newImg.onload = function () {
          $swatchImg.attr("src", matchingVariation.image.src);
          $swatchImg.attr("srcset", matchingVariation.image.srcset || "");
          $swatchImg.attr("sizes", matchingVariation.image.sizes || "");
          $swatchImg.attr("alt", matchingVariation.image.alt || "");
        };
        newImg.onerror = function () {};
        newImg.src = matchingVariation.image.src;
      } else {
      }
    });
  }

  $('.swatch[data-clicker="cgkit-swatch-selected"]').each(function (i, ele) {
    if (!$(ele).hasClass("cgkit-swatch-selected")) {
      $(ele).click();
    }
  });
  $(document).on(
    "click",
    'ul[data-attribute="attribute_pa_bundles"] .cgkit-swatch:not(.cgkit-swatch[data-attribute-value="grill-only"])',
    function (ele) {
      var $clickedBundle = $(ele.target);
      var bundleValue = $clickedBundle.data("attribute-value");
      var $form = $(".variations_form");

      // Preserve ALL current selections before making changes
      var allCurrentSelections = captureCurrentSelections();

      // Handle bundle selection
      if (!$clickedBundle.hasClass("cgkit-swatch-selected")) {
        // Remove selection from other bundle swatches
        $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch').removeClass(
          "cgkit-swatch-selected"
        );
        // Add selection to clicked bundle
        $clickedBundle.addClass("cgkit-swatch-selected");
        $("#pa_bundles").val(bundleValue);
      }

      // Show front bench options for non-grill-only bundles
      $('ul[data-attribute="attribute_pa_front-bench"]').parents("tr").show();

      // Get the CURRENT front bench value (not any preserved one)
      var currentFrontBench = $("#pa_front-bench").val();

      // Also check what the visual swatch shows
      var selectedSwatch = $(
        '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch-selected'
      );
      var visuallySelected = selectedSwatch.length ? selectedSwatch.data("attribute-value") : null;

      // CRITICAL FIX: Check visual swatch selection, not just dropdown value
      var shouldUseDefault =
        (!currentFrontBench || currentFrontBench === "none") && (!visuallySelected || visuallySelected === "none");

      if (shouldUseDefault) {
        $("#pa_front-bench").val("stainless-steel");
        $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch').removeClass(
          "cgkit-swatch-selected"
        );
        $(
          '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch[data-attribute-value="stainless-steel"]'
        ).addClass("cgkit-swatch-selected");
      } else {
        // User has a valid selection (wood, stainless-steel, etc.) - preserve it
        var valueToPreserve = visuallySelected || currentFrontBench;

        // Update the dropdown to match the visual selection
        $("#pa_front-bench").val(valueToPreserve);

        // Ensure visual swatch is correctly selected
        $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch').removeClass(
          "cgkit-swatch-selected"
        );
        $(
          '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch[data-attribute-value="' +
            valueToPreserve +
            '"]'
        ).addClass("cgkit-swatch-selected");
      }

      // Restore all other selections (especially controller)
      setTimeout(function () {
        // Ensure bundle stays selected
        if (!$clickedBundle.hasClass("cgkit-swatch-selected")) {
          $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch').removeClass(
            "cgkit-swatch-selected"
          );
          $clickedBundle.addClass("cgkit-swatch-selected");
        }

        // Restore all other preserved selections
        Object.keys(allCurrentSelections).forEach(function (attribute) {
          // Always exclude the bundle we just changed
          // Only exclude front-bench if we just changed it above (either set to stainless-steel or preserved current)
          var shouldExcludeFrontBench = attribute === "attribute_pa_front-bench";

          if (attribute !== "attribute_pa_bundles" && !shouldExcludeFrontBench) {
            var value = allCurrentSelections[attribute];
            var $targetSwatch = $(
              '.cgkit-attribute-swatches[data-attribute="' +
                attribute +
                '"] .cgkit-swatch[data-attribute-value="' +
                value +
                '"]'
            );
            if ($targetSwatch.length && !$targetSwatch.hasClass("cgkit-swatch-selected")) {
              // Remove selection from other swatches in this attribute
              $targetSwatch
                .closest(".cgkit-attribute-swatches")
                .find(".cgkit-swatch")
                .removeClass("cgkit-swatch-selected");
              $targetSwatch.addClass("cgkit-swatch-selected");
            }
          }
        });

        // Ensure all variations stay enabled
        ensureAllVariationsEnabled();

        // Trigger variation events
        $form.trigger("woocommerce_variation_select_change");
        $form.trigger("check_variations");

        // Final check and manual variation trigger for price updates
        setTimeout(function () {
          // Final restoration to ensure nothing was lost
          Object.keys(allCurrentSelections).forEach(function (attribute) {
            if (attribute !== "attribute_pa_bundles" && attribute !== "attribute_pa_front-bench") {
              var value = allCurrentSelections[attribute];
              var $targetSwatch = $(
                '.cgkit-attribute-swatches[data-attribute="' +
                  attribute +
                  '"] .cgkit-swatch[data-attribute-value="' +
                  value +
                  '"]'
              );
              if ($targetSwatch.length && !$targetSwatch.hasClass("cgkit-swatch-selected")) {
                $('.cgkit-attribute-swatches[data-attribute="' + attribute + '"] .cgkit-swatch').removeClass(
                  "cgkit-swatch-selected"
                );
                $targetSwatch.addClass("cgkit-swatch-selected");
                var selectName = attribute.replace("attribute_", "");
                $("#" + selectName).val(value);
              }
            }
          });

          // Ensure bundle and front bench selections are correct
          if (!$clickedBundle.hasClass("cgkit-swatch-selected")) {
            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch').removeClass(
              "cgkit-swatch-selected"
            );
            $clickedBundle.addClass("cgkit-swatch-selected");
          }

          // REMOVED: Forced stainless steel selection - let user choose front bench independently

          // Manual variation lookup and trigger for price updates
          var variations = $form.data("product_variations");
          if (variations) {
            var currentSelection = {
              attribute_pa_bundles: bundleValue,
              "attribute_pa_front-bench": $("#pa_front-bench").val(), // Use whatever is currently selected
            };

            // Include all other current selections
            $form.find("select").each(function () {
              var attrName = $(this).attr("name");
              var attrValue = $(this).val();
              if (attrValue && !currentSelection[attrName]) {
                currentSelection[attrName] = attrValue;
              }
            });

            // Find matching variation
            var matchingVariation = null;
            for (var i = 0; i < variations.length; i++) {
              var variation = variations[i];
              var matches = true;

              for (var attr in currentSelection) {
                if (variation.attributes[attr] !== currentSelection[attr]) {
                  matches = false;
                  break;
                }
              }

              if (matches) {
                matchingVariation = variation;
                break;
              }
            }

            // Trigger found_variation event to update ATC button price
            if (matchingVariation) {
              $form.trigger("found_variation", [matchingVariation]);
            } else {
            }
          }
        }, 100);
      }, 50);
    }
  );
  //  Custom Code for Widget V4
  $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"]').trigger("change");
  $(document).on("click", '.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] button', function () {
    var $clickedController = $(this);
    var controllerValue = $clickedController.data("attribute-value");

    // Remove any existing duplicate messages
    $(".select-wireless-txt").remove();

    // Update controller selection properly
    $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch').removeClass(
      "cgkit-swatch-selected"
    );
    $clickedController.addClass("cgkit-swatch-selected");
    $("#pa_controller").val(controllerValue);

    // If grill-only is selected, manually trigger variation update for price sync
    setTimeout(function () {
      if ($("#pa_bundles").val() === "grill-only") {
        var $form = $(".variations_form");

        // CRITICAL: Ensure controller selection is preserved after events
        setTimeout(function () {
          // Restore controller selection
          $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch').removeClass(
            "cgkit-swatch-selected"
          );
          $clickedController.addClass("cgkit-swatch-selected");
          $("#pa_controller").val(controllerValue);

          // Ensure front bench stays hidden for grill-only
          $("#pa_front-bench").val("none");
          $('ul[data-attribute="attribute_pa_front-bench"]').parents("tr").hide();
        }, 50);

        // Trigger variation change events
        $form.trigger("woocommerce_variation_select_change");
        $form.trigger("check_variations");

        // Also manually find and trigger the variation for immediate price update
        var variations = $form.data("product_variations");
        if (variations) {
          var currentSelection = {
            attribute_pa_bundles: "grill-only",
            "attribute_pa_front-bench": "none",
            attribute_pa_controller: controllerValue,
          };

          // Find matching variation
          var matchingVariation = null;
          for (var i = 0; i < variations.length; i++) {
            var variation = variations[i];
            var matches = true;

            for (var attr in currentSelection) {
              if (variation.attributes[attr] !== currentSelection[attr]) {
                matches = false;
                break;
              }
            }

            if (matches) {
              matchingVariation = variation;
              break;
            }
          }

          // Trigger found_variation event to update ATC button price
          if (matchingVariation) {
            $form.trigger("found_variation", [matchingVariation]);
          } else {
          }

          // IMPORTANT: Also update bundle card prices manually
          updateBundleCardPrices(variations, controllerValue);

          // Final preservation check after all processing
          setTimeout(function () {
            // Final controller restoration
            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch').removeClass(
              "cgkit-swatch-selected"
            );
            $clickedController.addClass("cgkit-swatch-selected");
            $("#pa_controller").val(controllerValue);

            // Final front bench hiding
            $("#pa_front-bench").val("none");
            $('ul[data-attribute="attribute_pa_front-bench"]').parents("tr").hide();

            // Ensure grill-only variation is properly handled
            ensureGrillOnlyVariation();
          }, 200);
        }
      }
    }, 100);
  });
  // Removed legacy AJAX add-to-cart hooks for cross-sell bundles
  $("ul.variable-items-wrapper").each(function () {
    setHeight($(this).find(".variable-item"));
  });

  function setHeight(col) {
    var $col = $(col);
    var $maxHeight = 50;
    $col.each(function () {
      var $thisHeight = $(this).outerHeight();
      if ($thisHeight > $maxHeight) {
        $maxHeight = $thisHeight;
      }
    });
    $col.height($maxHeight);
  }
  $(".tiles-seemore-link").on("click", function (e) {
    e.stopImmediatePropagation();
    let id = $(this).attr("data-id");
    elementorProFrontend.modules.popup.showPopup({
      id: id,
    });
  });
  jQuery(document).on("elementor/popup/hide", (event, id, instance) => {
    if ($(".tiles-seemore-link").length > 0) {
      $([document.documentElement, document.body]).animate(
        {
          scrollTop: $(".elementor-widget-productvarianttilesv4 .button-variable-wrapper").offset().top - 150,
        },
        100
      );
    }
  });

  // Handle Futureproof message display (following old plugin logic)
  function handleFutureproofMessage() {
    var $inStockMessage = $("#vt-in-stock-message");
    var $lowStockMessage = $("#vt-low-stock-message");

    // Get all selected attributes
    var selectedAttributes = {};
    $(".cgkit-attribute-swatches .cgkit-swatch-selected").each(function () {
      var attributeName = $(this).closest(".cgkit-attribute-swatches").data("attribute");
      var attributeValue = $(this).data("attribute-value");
      selectedAttributes[attributeName] = attributeValue;
    });

    // SIMPLE LOGIC: Check controller selection ONLY
    var controllerValue = null;

    // Find controller attribute
    for (var attr in selectedAttributes) {
      if (attr.includes("controller") || attr.includes("wireless")) {
        controllerValue = selectedAttributes[attr];
        break;
      }
    }

    // Simple stock status logic based ONLY on controller selection
    if (controllerValue && (controllerValue.includes("non-wireless") || controllerValue.includes("disabled"))) {
      $inStockMessage.hide();
      $lowStockMessage.show();
    } else if (controllerValue && (controllerValue.includes("wireless") || controllerValue.includes("enabled"))) {
      $inStockMessage.show();
      $lowStockMessage.hide();
    } else {
      $inStockMessage.hide();
      $lowStockMessage.hide();
    }

    // FUTUREPROOF MESSAGE LOGIC - Dynamic for any controller attribute
    // Find any controller-like attribute (attribute that might have wireless/non-wireless options)
    var controllerAttribute = null;
    var controllerValue = null;

    // Look for common controller attribute patterns
    for (var attr in selectedAttributes) {
      if (attr.includes("controller") || attr.includes("wireless") || attr.includes("connectivity")) {
        controllerAttribute = attr;
        controllerValue = selectedAttributes[attr];
        break;
      }
    }

    // If no specific controller found, look for any attribute with wireless/non-wireless values
    if (!controllerAttribute) {
      for (var attr in selectedAttributes) {
        var value = selectedAttributes[attr];
        if (value && (value.includes("wireless") || value.includes("non-wireless") || value.includes("enabled"))) {
          controllerAttribute = attr;
          controllerValue = value;
          break;
        }
      }
    }

    // Handle futureproof message if controller is found
    if (controllerAttribute && controllerValue) {
      var $controllerSwatches = $('.cgkit-attribute-swatches[data-attribute="' + controllerAttribute + '"]');
      var isNonWirelessSelected = controllerValue.includes("non-wireless") || controllerValue.includes("disabled");
      var isWirelessSelected = controllerValue.includes("wireless") || controllerValue.includes("enabled");

      // Only create message if it doesn't exist and non-wireless is selected
      if (isNonWirelessSelected && $(".select-wireless-txt").length <= 0) {
        var messageHtml =
          '<p class="select-wireless-txt" style="margin-top: 15px; text-align: center; color: #333; font-size: 14px; line-height: 1.4;">' +
          'Futureproof your grill by choosing <strong style="color: #BC3116;">Wireless Enabled</strong>' +
          '<svg height="18" fill="#BC3116" style="margin-left: 6px; vertical-align: middle; transform: rotate(-90deg);" viewBox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg">' +
          '<g id="_19" data-name="19">' +
          '<path d="m12 19a1 1 0 0 1 -.71-1.71l5.3-5.29-5.3-5.29a1 1 0 0 1 1.41-1.41l6 6a1 1 0 0 1 0 1.41l-6 6a1 1 0 0 1 -.7.29z"></path>' +
          '<path d="m6 19a1 1 0 0 1 -.71-1.71l5.3-5.29-5.3-5.29a1 1 0 0 1 1.42-1.42l6 6a1 1 0 0 1 0 1.41l-6 6a1 1 0 0 1 -.71.3z"></path>' +
          "</g></svg></p>";

        $controllerSwatches.after(messageHtml);
      }

      // Show/hide message based on selection
      if (isNonWirelessSelected) {
        $(".select-wireless-txt").addClass("show-txt");
      } else {
        $(".select-wireless-txt").removeClass("show-txt");
      }
    }
  }

  // Trigger on any attribute changes
  $(document).on("click change", ".cgkit-attribute-swatches .cgkit-swatch", function () {
    setTimeout(handleFutureproofMessage, 100);
  });

  // Trigger on page load
  handleFutureproofMessage();

  // Monitor badges, savings, and selection states - check every 5 seconds (reduced frequency)
  setInterval(function () {
    // Only run if not already processing
    if (window.isEnsuringGrillOnly) {
      return;
    }

    // Remove badge monitoring - handled by state change detection

    // Continuously enforce selection states
    enforceSelectionStates();

    // Ensure grill-only variations are properly handled
    ensureGrillOnlyVariation();

    // Final check: if grill-only is selected, ensure cart button is enabled
    var currentBundle = $(".variations_form").find('select[name="attribute_pa_bundles"]').val();
    if (currentBundle === "grill-only") {
      $(".single_add_to_cart_button").removeClass("disabled wc-variation-is-unavailable");
      $(".single_add_to_cart_button").addClass("wc-variation-selected");
    }
  }, 5000); // Increased interval to 5 seconds

  // Initialize "Best Value" badge on page load
  $(document).ready(function () {
    setTimeout(function () {
      updateBestValueBadge();
    }, 500);
  });

  // Update "Best Value" badge when variations change
  $(document).on("found_variation", function (event, variation) {
    // Update accordion content
    updateAccordionContent(variation);

    // Update stock status
    setTimeout(handleFutureproofMessage, 100);

    // Update ATC button price using central function
    updateATCButtonPrice(variation);

    // Track previous values for comparison
    var currentController = $("#pa_controller").val();
    var currentFrontBench = $("#pa_front-bench").val();

    // Check if controller or front bench changed
    var controllerChanged = window.previousControllerValue !== currentController;
    var frontBenchChanged = window.previousFrontBenchValue !== currentFrontBench;

    // Update previous values
    window.previousControllerValue = currentController;
    window.previousFrontBenchValue = currentFrontBench;

    // Only update images if controller or front bench changed
    if (controllerChanged || frontBenchChanged) {
      // Get current selections
      var currentSelections = {};
      $(".variations_form select").each(function () {
        var $select = $(this);
        var attrName = $select.attr("name");
        var attrValue = $select.val();
        if (attrName && attrValue) {
          currentSelections[attrName] = attrValue;
        }
      });

      // Ensure controller is included if we have a previous value
      if (window.previousControllerValue) {
        currentSelections["attribute_pa_controller"] = window.previousControllerValue;
      }

      // Only call if currentSelections is valid
      if (currentSelections && typeof currentSelections === "object") {
        debouncedUpdateBundleSwatchImages($(".variations_form"), currentSelections, "attribute_pa_bundles");
      }

      // Update badge
      updateBestValueBadge();
    }
  });

  // Function to update accordion content - only new variant tile data
  function updateAccordionContent(variation) {
    // Update the accordion system (zg-accordion)
    var $excerpt = $(".zg-accordion-excerpt");
    var $content = $(".zg-accordion-content");

    // Update excerpt text from Preview Text field with character limit
    if (variation._vt_dd_preview && variation._vt_dd_preview.trim() !== "") {
      var previewText = variation._vt_dd_preview.trim();

      // Apply character limit if available
      if (typeof zgAccordionSettings !== "undefined" && zgAccordionSettings.previewTextLimit > 0) {
        if (previewText.length > zgAccordionSettings.previewTextLimit) {
          previewText = previewText.substring(0, zgAccordionSettings.previewTextLimit) + "...";
        }
      }

      $excerpt.html(previewText).show();
    } else {
      $excerpt.html("").hide();
    }

    // Update full content from Dropdown Text field
    var contentHtml = "";

    // Add Dropdown Image if available
    if (variation._vt_dd_image_url && variation._vt_dd_image_url.trim() !== "") {
      contentHtml += '<div style="margin-bottom: 15px; width: 100%;">';
      contentHtml +=
        '<img src="' +
        variation._vt_dd_image_url +
        '" alt="Variant tile image" class="zg-accordion-image" style="width: 100%; height: auto; display: block;">';
      contentHtml += "</div>";
    }

    // Add Dropdown Text content
    if (variation._vt_dd_text && variation._vt_dd_text.trim() !== "") {
      contentHtml += variation._vt_dd_text;
    } else {
    }

    $content.find("div").first().html(contentHtml);
  }

  // Enhanced monitoring for selection consistency - check every 500ms
  setInterval(function () {
    // Prevent multiple simultaneous updates
    if (window.isUpdatingSelections) return;
    window.isUpdatingSelections = true;

    try {
      // Sync WooCommerce form with swatch selections
      var $form = $(".variations_form");
      var formSelections = {};

      $form.find("select").each(function () {
        var $select = $(this);
        var attrName = $select.attr("name");
        var attrValue = $select.val();
        if (attrName && attrValue) {
          formSelections[attrName] = attrValue;
        }
      });

      // Check if swatch selections match form selections
      for (var attr in formSelections) {
        var formValue = formSelections[attr];
        var $selectedSwatch = $(
          '[data-attribute="' + attr + '"] .cgkit-swatch[data-attribute-value="' + formValue + '"]'
        );
        var $currentlySelected = $('[data-attribute="' + attr + '"] .cgkit-swatch.cgkit-swatch-selected');

        // If mismatch, fix it
        if (
          $selectedSwatch.length &&
          (!$currentlySelected.length || $currentlySelected.data("attribute-value") !== formValue)
        ) {
          // Remove all selections in this attribute
          $('[data-attribute="' + attr + '"] .cgkit-swatch').removeClass("cgkit-swatch-selected");

          // Add selection to correct swatch
          $selectedSwatch.addClass("cgkit-swatch-selected");

          // Force visual update
          enforceSelectionStates();
        }
      }

      // Ensure selected swatches have proper visual highlighting
      $(".cgkit-swatch-selected").each(function () {
        var $swatch = $(this);
        var $card = $swatch.closest(".cgkit-attribute-swatch");

        // For bundle swatches, ensure the card has selection styling
        if ($swatch.closest('[data-attribute="attribute_pa_bundles"]').length) {
          if (!$card.hasClass("cgkit-selected")) {
            $card.addClass("cgkit-selected");
          }
        }
      });

      // Ensure non-selected swatches don't have highlighting
      $(".cgkit-swatch:not(.cgkit-swatch-selected)").each(function () {
        var $swatch = $(this);
        var $card = $swatch.closest(".cgkit-attribute-swatch");

        // For bundle swatches, remove selection styling
        if ($swatch.closest('[data-attribute="attribute_pa_bundles"]').length) {
          if ($card.hasClass("cgkit-selected")) {
            $card.removeClass("cgkit-selected");
          }
        }
      });

      // Validate that only one swatch per attribute is selected
      $("[data-attribute]").each(function () {
        var $attributeGroup = $(this);
        var selectedCount = $attributeGroup.find(".cgkit-swatch-selected").length;

        if (selectedCount > 1) {
          // Keep only the first selected swatch
          var $firstSelected = $attributeGroup.find(".cgkit-swatch-selected").first();
          $attributeGroup.find(".cgkit-swatch-selected").not($firstSelected).removeClass("cgkit-swatch-selected");
        }
      });
    } catch (error) {
    } finally {
      window.isUpdatingSelections = false;
    }
  }, 500);

  // Prevent other scripts from removing badges and savings (but allow our own removal)
  var originalRemove = $.fn.remove;
  $.fn.remove = function () {
    var $this = $(this);
    if (($this.hasClass("tile-offer") || $this.attr("id") === "vt-total-savings") && !window.isRemovingBadges) {
      return this;
    }
    return originalRemove.apply(this, arguments);
  };

  // Prevent other scripts from interfering with selection classes
  var originalAddClass = $.fn.addClass;
  var originalRemoveClass = $.fn.removeClass;

  $.fn.addClass = function (className) {
    var $this = $(this);
    // Prevent removal of cgkit-swatch-selected class by external scripts
    if (typeof className === "string" && className.includes("cgkit-swatch-selected") && window.selectionLocked) {
      return this;
    }
    return originalAddClass.apply(this, arguments);
  };

  $.fn.removeClass = function (className) {
    var $this = $(this);
    // Prevent removal of cgkit-swatch-selected class by external scripts
    if (typeof className === "string" && className.includes("cgkit-swatch-selected") && window.selectionLocked) {
      return this;
    }
    return originalRemoveClass.apply(this, arguments);
  };

  // Hook into WooCommerce variation events (improved to prevent flickering)
  $(document).on("found_variation", function (event, variation) {
    var $form = $(event.target);
    var currentSelections = {};
    $form.find("select").each(function () {
      var $select = $(this);
      var attrName = $select.attr("name");
      var attrValue = $select.val();
      if (attrName && attrValue) {
        currentSelections[attrName] = attrValue;
      }
    });

    // Ensure controller is always included in selections
    if (!currentSelections["attribute_pa_controller"] && window.previousControllerValue) {
      currentSelections["attribute_pa_controller"] = window.previousControllerValue;
    }

    // Check if this is a grill-only variation and ensure cart button is enabled
    if (currentSelections["attribute_pa_bundles"] === "grill-only") {
      $(".single_add_to_cart_button").removeClass("disabled wc-variation-is-unavailable");
      $(".single_add_to_cart_button").addClass("wc-variation-selected");
    }

    // Update ATC button price using central function
    updateATCButtonPrice(variation);

    // Update bundle prices only - NO image updates for bundle changes
    var variations = $form.data("product_variations");
    var controllerValue = currentSelections["attribute_pa_controller"];
    var currentFrontBench = currentSelections["attribute_pa_front-bench"];

    if (variations && controllerValue) {
      // Update bundle prices only
      updateBundleCardPrices(variations, controllerValue, currentFrontBench);

      // Update bundle images ONLY when controller or front bench changes (not for bundle changes)
      // Check if this variation change was triggered by controller or front bench
      var changedAttribute = null;
      if (currentSelections["attribute_pa_controller"] !== window.previousControllerValue) {
        changedAttribute = "controller";
        window.previousControllerValue = currentSelections["attribute_pa_controller"];
      } else if (currentSelections["attribute_pa_front-bench"] !== window.previousFrontBenchValue) {
        // Special handling for front bench changes
        var newFrontBench = currentSelections["attribute_pa_front-bench"];
        var previousFrontBench = window.previousFrontBenchValue;

        // Don't trigger image updates when switching to grill-only (front bench becomes "none")
        if (newFrontBench === "none" && currentSelections["attribute_pa_bundles"] === "grill-only") {
          window.previousFrontBenchValue = newFrontBench;
        } else {
          changedAttribute = "front-bench";
          window.previousFrontBenchValue = newFrontBench;
        }
      }

      // Only update images if controller or front bench changed
      if (changedAttribute) {
        var bundleAttribute = null;
        if (currentSelections["attribute_pa_bundles"]) {
          bundleAttribute = "attribute_pa_bundles";
        } else if (currentSelections["attribute_pa_bundle"]) {
          bundleAttribute = "attribute_pa_bundle";
        }

        if (bundleAttribute && currentSelections && typeof currentSelections === "object") {
          debouncedUpdateBundleSwatchImages($form, currentSelections, bundleAttribute);
        }
      }
    }
  });

  // Hook into controller and front bench changes specifically (images should only change for these)
  $(document).on("woocommerce_variation_select_change", function (event, variation) {
    var $select = $(event.target);
    if ($select.attr("name") === "attribute_pa_controller" || $select.attr("name") === "attribute_pa_front-bench") {
      // Prevent infinite loops by checking if we're already processing
      if (window.isProcessingVariationChange) {
        return;
      }

      window.isProcessingVariationChange = true;

      setTimeout(function () {
        var $form = $(".variations_form");
        var variations = $form.data("product_variations");
        var controllerValue = $form.find('select[name="attribute_pa_controller"]').val();

        if (variations && controllerValue) {
          updateBundleCardPrices(variations, controllerValue);

          // Update bundle images ONLY when controller or front bench changes
          var bundleAttribute = null;
          if ($form.find('select[name="attribute_pa_bundles"]').val()) {
            bundleAttribute = "attribute_pa_bundles";
          } else if ($form.find('select[name="attribute_pa_bundle"]').val()) {
            bundleAttribute = "attribute_pa_bundle";
          }

          if (bundleAttribute) {
            var selectedAttributes = {};
            $form.find("select").each(function () {
              var $select = $(this);
              var attrName = $select.attr("name");
              var attrValue = $select.val();
              if (attrName && attrValue) {
                selectedAttributes[attrName] = attrValue;
              }
            });
            if (selectedAttributes && typeof selectedAttributes === "object") {
              debouncedUpdateBundleSwatchImages($form, selectedAttributes, bundleAttribute);
            }
          }
        }

        // Reset the processing flag after a delay
        setTimeout(function () {
          window.isProcessingVariationChange = false;
        }, 200);
      }, 100);
    }
  });

  $(document).on("reset_data", function () {
    // Update bundle prices when variation is reset
    var $form = $(".variations_form");
    var variations = $form.data("product_variations");
    var controllerValue = $form.find('select[name="attribute_pa_controller"]').val();
    var currentFrontBench = $form.find('select[name="attribute_pa_front-bench"]').val();

    if (variations && controllerValue) {
      updateBundleCardPrices(variations, controllerValue, currentFrontBench);

      // Update bundle images when variation is reset to ensure correct state
      var bundleAttribute = null;
      if ($form.find('select[name="attribute_pa_bundles"]').val()) {
        bundleAttribute = "attribute_pa_bundles";
      } else if ($form.find('select[name="attribute_pa_bundle"]').val()) {
        bundleAttribute = "attribute_pa_bundle";
      }

      if (bundleAttribute) {
        var selectedAttributes = {};
        $form.find("select").each(function () {
          var $select = $(this);
          var attrName = $select.attr("name");
          var attrValue = $select.val();
          if (attrName && attrValue) {
            selectedAttributes[attrName] = attrValue;
          }
        });
        if (selectedAttributes && typeof selectedAttributes === "object") {
          debouncedUpdateBundleSwatchImages($form, selectedAttributes, bundleAttribute);
        }
      }
    }
  });

  // Handle when no variation is found - ensure grill-only still works
  $(document).on("hide_variation", function () {
    var $form = $(".variations_form");
    var currentBundle = $form.find('select[name="attribute_pa_bundles"]').val();
    var currentController = $form.find('select[name="attribute_pa_controller"]').val();

    // If grill-only is selected, force enable the cart button
    if (currentBundle === "grill-only" && currentController) {
      $(".single_add_to_cart_button").removeClass("disabled wc-variation-is-unavailable");
      $(".single_add_to_cart_button").addClass("wc-variation-selected");
    }
  });

  // Handle swatch clicks
  $(document).on("click", ".cgkit-swatch", function (e) {
    // Prevent default click behavior if already selected
    if ($(this).hasClass("cgkit-swatch-selected")) {
      e.preventDefault();
      e.stopPropagation();
      return;
    }

    // Prevent race conditions
    if (window.isProcessingClick) {
      e.preventDefault();
      e.stopPropagation();
      return;
    }

    window.isProcessingClick = true;

    // Safety timeout to ensure flag gets reset
    setTimeout(function () {
      if (window.isProcessingClick) {
        window.isProcessingClick = false;
      }
    }, 5000);

    var $swatch = $(this);
    var attributeName = $swatch.closest(".cgkit-attribute-swatches").data("attribute");
    var attributeValue = $swatch.data("attribute-value");

    // Update WooCommerce form
    var $form = $(".variations_form");
    var $select = $form.find('select[name="' + attributeName + '"]');

    if ($select.length) {
      // Store current selections before updating
      var currentSelections = {};
      $form.find("select").each(function () {
        var $currentSelect = $(this);
        var currentAttrName = $currentSelect.attr("name");
        var currentAttrValue = $currentSelect.val();
        if (currentAttrName && currentAttrValue) {
          currentSelections[currentAttrName] = currentAttrValue;
        }
      });

      // Update the specific attribute
      $select.val(attributeValue).trigger("change");

      // Remove selection from all swatches in this attribute
      $swatch.closest(".cgkit-attribute-swatches").find(".cgkit-swatch").removeClass("cgkit-swatch-selected");

      // Add selection to clicked swatch
      $swatch.addClass("cgkit-swatch-selected");

      // Update "Best Value" badge for specific combination
      updateBestValueBadgeOptimized();

      // Enforce selection states
      enforceSelectionStates();

      // Update bundle prices (preserve controller selection)
      var variations = $form.data("product_variations");
      var controllerValue =
        currentSelections["attribute_pa_controller"] || $form.find('select[name="attribute_pa_controller"]').val();
      var currentFrontBench =
        currentSelections["attribute_pa_front-bench"] || $form.find('select[name="attribute_pa_front-bench"]').val();

      if (variations && controllerValue) {
        updateBundleCardPrices(variations, controllerValue, currentFrontBench);
      }

      // Ensure grill-only price is always displayed
      setTimeout(function () {
        ensureGrillOnlyPrice();
      }, 100);

      window.isProcessingClick = false;
    } else {
      window.isProcessingClick = false;
    }
  });

  // Function to enforce proper selection states
  function enforceSelectionStates() {
    // Remove pointer-events restrictions - all swatches should remain clickable
    // Users should be able to click any swatch to change their selection
    $(".cgkit-swatch").css({
      "pointer-events": "auto",
      cursor: "pointer",
    });

    // Only ensure bundle cards have proper selection highlighting
    $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch').each(function () {
      var $swatch = $(this);
      if ($swatch.hasClass("cgkit-swatch-selected")) {
        $swatch.addClass("cgkit-selected");
      } else {
        $swatch.removeClass("cgkit-selected");
      }
    });

    $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch').each(function () {
      var $swatch = $(this);
      var isSelected = $swatch.hasClass("cgkit-swatch-selected");
      var pointerEvents = $swatch.css("pointer-events");
      var cursor = $swatch.css("cursor");
    });
  }

  // Ensure grill-only always has a price displayed
  function ensureGrillOnlyPrice() {
    var $grillOnlyPrice = $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"]')
      .find('.cgkit-swatch[data-attribute-value="grill-only"]')
      .find(".tile-price");

    if (
      $grillOnlyPrice.length &&
      ($grillOnlyPrice.text().trim() === "" || $grillOnlyPrice.text().trim() === "&nbsp;")
    ) {
      var $form = $(".variations_form");
      var variations = $form.data("product_variations");
      var controllerValue = $form.find('select[name="attribute_pa_controller"]').val();
      var currentFrontBench = $form.find('select[name="attribute_pa_front-bench"]').val();

      if (variations && controllerValue) {
        // Force update grill-only price
        updateBundleCardPrices(variations, controllerValue, currentFrontBench);
      }
    }
  }

  // Function to ensure grill-only variations are properly handled
  function ensureGrillOnlyVariation() {
    // Prevent infinite calls
    if (window.isEnsuringGrillOnly) {
      return;
    }
    window.isEnsuringGrillOnly = true;

    var $form = $(".variations_form");
    var currentBundle = $form.find('select[name="attribute_pa_bundles"]').val();
    var currentController = $form.find('select[name="attribute_pa_controller"]').val();

    if (currentBundle === "grill-only" && currentController) {
      var variations = $form.data("product_variations");

      if (variations) {
        // Find the matching grill-only variation
        var matchingVariation = null;
        for (var i = 0; i < variations.length; i++) {
          var variation = variations[i];

          if (
            variation.attributes &&
            variation.attributes["attribute_pa_bundles"] === "grill-only" &&
            variation.attributes["attribute_pa_controller"] === currentController
          ) {
            matchingVariation = variation;
            break;
          }
        }

        // If we found a matching variation, trigger it
        if (matchingVariation) {
          $form.trigger("found_variation", [matchingVariation]);
        } else {
          // If no exact match, force enable the cart button anyway
          $(".single_add_to_cart_button").removeClass("disabled wc-variation-is-unavailable");
          $(".single_add_to_cart_button").addClass("wc-variation-selected");
        }
      } else {
        // If no variations data, still enable the cart button for grill-only
        $(".single_add_to_cart_button").removeClass("disabled wc-variation-is-unavailable");
        $(".single_add_to_cart_button").addClass("wc-variation-selected");
      }
    }

    // Reset the flag after a short delay
    setTimeout(function () {
      window.isEnsuringGrillOnly = false;
    }, 100);
  }

  // Trigger initial variation update

  // Global click handler for cart button
  $(document).on("click", ".single_add_to_cart_button", function (e) {
    // Cart button click handler - functionality maintained
  });
});
