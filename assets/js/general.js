/**
 * ZG Variation Tiles - Simplified General Script
 *
 * This is a simplified version that removes conflicting logic
 * and lets the variation-price-fix.js handle price and ATC updates
 */

jQuery(document).ready(function ($) {
  // Remove stock message elements from DOM immediately
  function removeStockMessages() {
    $('.vt-stock-message-container, #vt-in-stock-message, #vt-low-stock-message, .vt-stock-message, .stock-dot').remove();
  }

  // Remove immediately
  removeStockMessages();

  // Use MutationObserver to catch any dynamically added stock messages
  if (typeof MutationObserver !== 'undefined') {
    var observer = new MutationObserver(function(mutations) {
      var hasStockMessages = $('.vt-stock-message-container').length > 0;
      if (hasStockMessages) {
        removeStockMessages();
      }
    });

    if (document.body) {
      observer.observe(document.body, {
        childList: true,
        subtree: true
      });
    }
  }

  // Only run on product pages
  if (!$("body").hasClass("single-product") && !$("body").hasClass("woocommerce-page")) {
    return;
  }

  // Global variable declarations to prevent undefined errors
  if (typeof window.lastBundleValue === "undefined") {
    window.lastBundleValue = null;
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
        }
      });
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
      }, 100);

      return result;
    };
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
        Object.keys(currentSelections).forEach(function (attr) {
          var value = currentSelections[attr];
          var $dropdown = form.querySelector('[name="' + attr.replace("attribute_", "") + '"]');
          var $swatch = form.querySelector(
            '.cgkit-attribute-swatches[data-attribute="' +
              attr +
              '"] .cgkit-swatch[data-attribute-value="' +
              value +
              '"]'
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

  // Hide None Buttons
  $('button.swatch.cgkit-swatch[data-attribute-value="none"]').parent("li").hide();

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

    // Update futureproof message
    setTimeout(handleFutureproofMessage, 100);

    // Update badge
    updateBestValueBadge();
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
    }

    $content.find("div").first().html(contentHtml);
  }

  // Handle Futureproof message display
  function handleFutureproofMessage() {
    // Get all selected attributes
    var selectedAttributes = {};
    $(".cgkit-attribute-swatches .cgkit-swatch-selected").each(function () {
      var attributeName = $(this).closest(".cgkit-attribute-swatches").data("attribute");
      var attributeValue = $(this).data("attribute-value");
      selectedAttributes[attributeName] = attributeValue;
    });

    // FUTUREPROOF MESSAGE LOGIC - Dynamic for any controller attribute
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

  // Handle variation resets
  $(document).on("woocommerce_reset_variations", function () {
    setTimeout(function () {
      ensureAllVariationsEnabled();
    }, 100);
  });

  // Prevent other scripts from removing badges and savings (but allow our own removal)
  var originalRemove = $.fn.remove;
  $.fn.remove = function () {
    var $this = $(this);
    if (($this.hasClass("tile-offer") || $this.attr("id") === "vt-total-savings") && !window.isRemovingBadges) {
      return this;
    }
    return originalRemove.apply(this, arguments);
  };

  // Handle popup functionality
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

  // === UNIVERSAL VARIATION TILES SELECTION HANDLING ===
  // Ensure consistent selection states across all products

  function ensureConsistentSelectionStates() {
    // Handle all swatch selections consistently
    $(".cgkit-attribute-swatches .cgkit-swatch").each(function () {
      var $swatch = $(this);
      var $parent = $swatch.closest(".cgkit-attribute-swatch");

      // Remove any existing selection classes
      $parent.removeClass("selected active");

      // Add selection class if swatch is selected
      if ($swatch.hasClass("cgkit-swatch-selected")) {
        $parent.addClass("selected active");
      }
    });

    // Ensure proper visual feedback for all attribute types
    $(".cgkit-attribute-swatches").each(function () {
      var $container = $(this);
      var attribute = $container.data("attribute");

      // Apply consistent styling based on attribute type
      if (attribute && (attribute.includes("payment-method") || attribute.includes("pizza-oven"))) {
        $container.addClass("button-toggle-style");
      }
    });
  }

  // === DEFAULT SELECTION HANDLING ===
  // Ensure default selections are properly applied and visible

  function applyDefaultSelections() {
    var $form = $(".variations_form");
    if (!$form.length) return;

    // Get all select elements and their default values
    $form.find("select[name^='attribute_']").each(function () {
      var $select = $(this);
      var attrName = $select.attr("name");
      var defaultValue = $select.val();

      if (defaultValue && defaultValue !== "") {
        // Find corresponding swatch
        var $swatch = $(
          '.cgkit-attribute-swatches[data-attribute="' +
            attrName +
            '"] .cgkit-swatch[data-attribute-value="' +
            defaultValue +
            '"]'
        );

        if ($swatch.length) {
          // Remove selection from all swatches in this attribute
          $('.cgkit-attribute-swatches[data-attribute="' + attrName + '"] .cgkit-swatch').removeClass(
            "cgkit-swatch-selected"
          );

          // Add selection to the default swatch
          $swatch.addClass("cgkit-swatch-selected");

          // Update parent container
          var $parent = $swatch.closest(".cgkit-attribute-swatch");
          $parent.addClass("selected active");
        }
      }
    });

    // Apply consistent styling after setting defaults
    ensureConsistentSelectionStates();
  }

  // Force default selections on page load
  function forceDefaultSelections() {
    // Wait for CommerceKit to initialize
    setTimeout(function () {
      applyDefaultSelections();

      // Also trigger CommerceKit's selection update
      if (typeof window.cgkitUpdateAvailableAttributes === "function") {
        var $form = $(".variations_form")[0];
        if ($form) {
          window.cgkitUpdateAvailableAttributes($form);
        }
      }
    }, 200);

    // Additional fallback with longer delay
    setTimeout(function () {
      applyDefaultSelections();
    }, 1000);
  }

  // Run on page load
  $(document).ready(function () {
    // Force default selections first
    forceDefaultSelections();

    // Then ensure consistent states
    setTimeout(function () {
      ensureConsistentSelectionStates();
    }, 300);
  });

  // Run when swatches are clicked
  $(document).on("click", ".cgkit-attribute-swatches .cgkit-swatch", function () {
    setTimeout(ensureConsistentSelectionStates, 100);
  });

  // Run when variations change
  $(document).on("found_variation", function () {
    setTimeout(ensureConsistentSelectionStates, 100);
  });

  // Also run when CommerceKit initializes
  $(document).on("cgkit_initialized", function () {
    forceDefaultSelections();
  });

  // Set height for variable items
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
});
