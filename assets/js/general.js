jQuery(document).ready(function ($) {
  // COMMERCEKIT-AWARE SOLUTION: Work with CommerceKit's patterns
  var globalSelectionState = {};

  // Function to show/hide badges and total savings based on deals
  function updateVariationBadges() {
    // Get current form selections
    var currentSelections = {};
    $(".variations select").each(function () {
      var $select = $(this);
      var attribute = $select.attr("name");
      var value = $select.val();
      if (attribute && value) {
        currentSelections[attribute] = value;
      }
    });

    // Find the matching variation
    var $variationForm = $("form.variations_form");
    var productId = $variationForm.data("product_id");

    if (productId) {
      // Get variation data from WooCommerce
      var variationData = $variationForm.data("product_variations");

      if (variationData) {
        // Find matching variation
        var matchingVariation = null;
        for (var i = 0; i < variationData.length; i++) {
          var variation = variationData[i];
          var matches = true;

          // Check if all current selections match this variation
          for (var attr in currentSelections) {
            if (variation.attributes && variation.attributes[attr] !== currentSelections[attr]) {
              matches = false;
              break;
            }
          }

          if (matches) {
            matchingVariation = variation;
            break;
          }
        }

        // Check for offer label
        var offerLabel =
          matchingVariation.vt_offer_label ||
          matchingVariation._vt_offer_label ||
          matchingVariation.offer_label ||
          matchingVariation.variation_offer_label;

        // Remove ALL existing badges first
        window.isRemovingBadges = true;
        $(".tile-offer").remove();
        window.isRemovingBadges = false;

        // Remove existing badges
        window.isRemovingBadges = true;
        $(".tile-offer").remove();
        window.isRemovingBadges = false;

        // Add badge if deal exists
        if (matchingVariation && offerLabel && offerLabel.trim() !== "") {
          var selectedBundle = currentSelections["attribute_pa_bundles"];
          if (selectedBundle) {
            var $selectedButton = $(
              '[data-attribute="attribute_pa_bundles"] .cgkit-attribute-swatch button[data-attribute-value="' +
                selectedBundle +
                '"]'
            );

            if ($selectedButton.length) {
              var $newBadge = $(
                '<span class="tile-offer" style="position: absolute !important; top: 10px !important; right: 10px !important; background: #bc3116 !important; color: #fff !important; font-weight: 700 !important; border-radius: 12px !important; padding: 4px 8px !important; font-size: 11px !important; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important; z-index: 999 !important; display: block !important; visibility: visible !important; opacity: 1 !important; white-space: nowrap !important; line-height: 1 !important;">' +
                  offerLabel +
                  "</span>"
              );
              $selectedButton.append($newBadge);
            }
          }
        }

        // Always update total savings (calculated for every combination)
        var regularPrice = matchingVariation.display_regular_price;
        var salePrice = matchingVariation.display_price;
        var savingsText = "";

        if (regularPrice && salePrice && regularPrice > salePrice) {
          var savings = regularPrice - salePrice;
          var savingsFormatted = "$" + savings.toFixed(0);
          savingsText = "TOTAL SAVINGS: " + savingsFormatted;
        } else {
          savingsText = "TOTAL SAVINGS: $0";
        }

        // Update or create savings display
        var $existingSavings = $("#vt-total-savings");
        if ($existingSavings.length) {
          $existingSavings.text(savingsText);
        } else {
          var $savingsHtml = $(
            '<div id="vt-total-savings" style="font-weight: 600; font-family: Inter; font-size: 16px; margin: 10px 0; text-align: left; width: 100%; display: block;">' +
              savingsText +
              "</div>"
          );
          $(".single_add_to_cart_button").before($savingsHtml);
        }
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
    // Remove disabled class from ALL variation swatches - NEVER disable anything
    $(".cgkit-swatch").removeClass("cgkit-disabled");

    // Ensure all required options exist in select dropdowns
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
      // Capture current state before CommerceKit processes
      var beforeSelections = captureCurrentSelections();

      var parent = input.closest(".cgkit-attribute-swatches");
      var attr_name = parent.getAttribute("data-attribute");
      var attr_value = input.getAttribute("data-attribute-value");

      console.log("CommerceKit processing:", attr_name, "=", attr_value);
      console.log("Before selections:", beforeSelections);

      // CRITICAL: Ensure all options exist BEFORE CommerceKit processes
      ensureAllVariationsEnabled();

      // Call original CommerceKit function
      var result = originalCgkitUpdateSwatch.call(this, input);

      // Update badges and savings after CommerceKit update
      setTimeout(function () {
        updateVariationBadges(); // Update badges when variation changes
      }, 100);

      // Handle special logic for our variations
      setTimeout(function () {
        // STEP 1: Handle business logic based on what was clicked
        if (attr_name === "attribute_pa_bundles" && attr_value === "grill-only") {
          // For grill-only: hide front bench and set to none
          $("#pa_front-bench").val("none");
          $('ul[data-attribute="attribute_pa_front-bench"]').parents("tr").hide();

          // CRITICAL: Ensure controller is preserved from beforeSelections
          var preservedController = beforeSelections["attribute_pa_controller"];
          if (preservedController) {
            console.log("🔧 Preserving controller for grill-only:", preservedController);
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

          // Immediately trigger variation lookup for grill-only
          setTimeout(function () {
            var $form = $(".variations_form");

            // Double-check controller is still set before triggering variation lookup
            var currentController = $("#pa_controller").val();
            if (!currentController && preservedController) {
              console.log("🔧 Re-setting controller before variation lookup:", preservedController);
              $("#pa_controller").val(preservedController);
            }

            $form.trigger("woocommerce_variation_select_change");
            $form.trigger("check_variations");

            // Manual variation lookup to ensure correct grill-only price
            var variations = $form.data("product_variations");
            var finalController = $("#pa_controller").val();

            if (variations && finalController) {
              // Find the correct grill-only variation for this controller
              var targetAttributes = {
                attribute_pa_bundles: "grill-only",
                attribute_pa_controller: finalController,
                "attribute_pa_front-bench": "none",
              };

              console.log("🔍 Looking for grill-only variation with attributes:", targetAttributes);

              var correctVariation = null;
              for (var i = 0; i < variations.length; i++) {
                var variation = variations[i];
                var matches = true;

                for (var attr in targetAttributes) {
                  if (variation.attributes[attr] !== targetAttributes[attr]) {
                    matches = false;
                    break;
                  }
                }

                if (matches) {
                  correctVariation = variation;
                  console.log(
                    "✅ Found correct grill-only variation:",
                    variation.variation_id,
                    "price:",
                    variation.display_price || variation.price
                  );
                  break;
                }
              }

              // If we found the correct variation, trigger it manually
              if (correctVariation) {
                setTimeout(function () {
                  $form.trigger("found_variation", [correctVariation]);
                  console.log("🎯 Manually triggered correct grill-only variation");
                }, 50);
              }

              // Update bundle card prices
              updateBundleCardPrices(variations, finalController);
            }

            console.log(
              "Triggered variation lookup and bundle price update for grill-only with controller:",
              finalController
            );
          }, 100);
        } else if (attr_name === "attribute_pa_bundles" && attr_value !== "grill-only" && attr_value !== "") {
          // For non-grill-only bundles: just show front bench (preserve whatever was selected)
          $('ul[data-attribute="attribute_pa_front-bench"]').parents("tr").show();

          // Get the CURRENT front bench value (not the preserved one)
          var currentFrontBench = $("#pa_front-bench").val();
          console.log("🪵 Current front bench when switching to", attr_value, ":", currentFrontBench);
          console.log("🪵 Type of currentFrontBench:", typeof currentFrontBench);
          console.log("🪵 Boolean check - !currentFrontBench:", !currentFrontBench);
          console.log("🪵 Boolean check - currentFrontBench === 'none':", currentFrontBench === "none");

          // Also check what the visual swatch shows
          var selectedSwatch = $(
            '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch-selected'
          );
          console.log(
            "🪵 Visual swatch selected:",
            selectedSwatch.length ? selectedSwatch.data("attribute-value") : "none"
          );

          // CRITICAL FIX: Check visual swatch selection, not just dropdown value
          // When front bench is hidden (grill-only), dropdown stays "none" but user can still select visually
          var visuallySelected = selectedSwatch.length ? selectedSwatch.data("attribute-value") : null;
          var shouldUseDefault =
            (!currentFrontBench || currentFrontBench === "none") && (!visuallySelected || visuallySelected === "none");

          console.log("🪵 Should use stainless-steel default?", shouldUseDefault);

          if (shouldUseDefault) {
            console.log("🪵 Setting default stainless-steel because front bench was:", currentFrontBench);
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
            console.log("🪵 Preserving user's front bench selection:", valueToPreserve);

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

          // Trigger variation lookup for bundle change
          setTimeout(function () {
            var $form = $(".variations_form");
            $form.trigger("woocommerce_variation_select_change");
            $form.trigger("check_variations");
            console.log("Triggered variation lookup for bundle change");
          }, 100);
        }

        // STEP 2: Always restore ALL preserved selections (except the one that was just changed)
        Object.keys(beforeSelections).forEach(function (preservedAttr) {
          if (preservedAttr !== attr_name && preservedAttr !== "attribute_pa_front-bench") {
            // Don't override what was just clicked
            // Don't override front-bench - we handle it explicitly in STEP 1
            var preservedValue = beforeSelections[preservedAttr];
            var $preservedSwatch = $(
              '.cgkit-attribute-swatches[data-attribute="' +
                preservedAttr +
                '"] .cgkit-swatch[data-attribute-value="' +
                preservedValue +
                '"]'
            );

            if ($preservedSwatch.length && !$preservedSwatch.hasClass("cgkit-swatch-selected")) {
              // Update dropdown
              var selectName = preservedAttr.replace("attribute_", "");
              $("#" + selectName).val(preservedValue);

              // Update visual swatch
              $('.cgkit-attribute-swatches[data-attribute="' + preservedAttr + '"] .cgkit-swatch').removeClass(
                "cgkit-swatch-selected"
              );
              $preservedSwatch.addClass("cgkit-swatch-selected");

              console.log("Restored lost selection:", preservedAttr, "=", preservedValue);
            }
          }
        });

        // STEP 3: Always ensure everything stays enabled
        ensureAllVariationsEnabled();

        // Update global state
        globalSelectionState = captureCurrentSelections();
        console.log("After selections:", globalSelectionState);
      }, 50);

      return result;
    };
  }

  // CRITICAL: Hook into cgkitUpdateAvailableAttributes - this is what clears selections!
  if (typeof window.cgkitUpdateAvailableAttributes === "function") {
    var originalCgkitUpdate = window.cgkitUpdateAvailableAttributes;
    window.cgkitUpdateAvailableAttributes = function (form) {
      console.log("🔍 cgkitUpdateAvailableAttributes called - preserving current state");

      // Capture current selections before CommerceKit processes them
      var currentSelections = captureCurrentSelections();
      console.log("Selections before cgkitUpdateAvailableAttributes:", currentSelections);

      // ENSURE all required options exist BEFORE CommerceKit checks
      ensureAllVariationsEnabled();

      // Call original CommerceKit function
      var result = originalCgkitUpdate.call(this, form);

      // IMMEDIATELY restore selections that CommerceKit might have cleared
      setTimeout(function () {
        console.log("🔧 Restoring selections after cgkitUpdateAvailableAttributes");

        // Restore each selection that was lost
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
            console.log("❌ Lost selection:", attr, "was:", value, "now:", $dropdown.value);

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

              console.log("✅ Restored:", attr, "=", value);
            }
          }
        });

        // Final pass to ensure everything stays enabled
        ensureAllVariationsEnabled();

        console.log("Final selections after restoration:", captureCurrentSelections());
      }, 5); // Very short timeout to catch CommerceKit immediately

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
        var $form = $(".variations_form");
        var variations = $form.data("product_variations");
        if (variations) {
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

          // Trigger found_variation event to update prices
          if (matchingVariation) {
            console.log("Updating prices for grill-only + controller change:", matchingVariation.variation_id);
            $form.trigger("found_variation", [matchingVariation]);
          }
        }
      }
    }, 50);
  });

  // Function to manually update bundle card prices when controller changes
  function updateBundleCardPrices(variations, controllerValue) {
    var availableBundles = {};
    var lastAttribute = "attribute_pa_bundles";

    console.log("🎲 Updating bundle card prices for controller:", controllerValue);
    console.log("Total variations available:", variations ? variations.length : 0);

    // Build current selection for filtering
    var filterAttributes = {
      attribute_pa_controller: controllerValue,
      // Don't include bundles or front-bench as these vary per bundle
    };

    // Filter variations that match the controller selection
    variations.forEach(function (variation) {
      // Check if this variation matches our controller
      var isMatching = true;
      for (var attr in filterAttributes) {
        if (variation.attributes[attr] !== filterAttributes[attr]) {
          isMatching = false;
          break;
        }
      }

      if (isMatching) {
        var bundle = variation.attributes[lastAttribute];
        console.log(
          "Checking variation for bundle:",
          bundle,
          "front-bench:",
          variation.attributes["attribute_pa_front-bench"],
          "ID:",
          variation.variation_id
        );

        // For grill-only, ensure front-bench is "none"
        if (bundle === "grill-only" && variation.attributes["attribute_pa_front-bench"] !== "none") {
          console.log("❌ Skipping grill-only with front-bench:", variation.attributes["attribute_pa_front-bench"]);
          return; // Skip this variation
        }
        // For other bundles, we can be more flexible with front-bench
        if (bundle && !availableBundles[bundle]) {
          availableBundles[bundle] = variation.price_html;
          console.log(
            "✅ Found price for",
            bundle,
            ":",
            variation.price_html,
            "| variation ID:",
            variation.variation_id
          );
        }
      }
    });

    console.log("Available bundles with prices:", availableBundles);

    // Clear previous prices
    $('.cgkit-attribute-swatches[data-attribute="' + lastAttribute + '"] .tile-price').empty();

    // Update with new prices
    Object.keys(availableBundles).forEach(function (bundle) {
      var priceHtml = availableBundles[bundle];
      var cleanPriceHtml = "";

      if (priceHtml) {
        // Remove "Total:" text and colon, keep everything else
        cleanPriceHtml = priceHtml.replace(/Total:\s*/g, "");
      }

      $('.cgkit-attribute-swatches[data-attribute="' + lastAttribute + '"]')
        .find('.cgkit-swatch[data-attribute-value="' + bundle + '"]')
        .find(".tile-price")
        .html(cleanPriceHtml);

      console.log("Updated", bundle, "card price to:", cleanPriceHtml);
    });
  }

  // Initial setup
  setTimeout(function () {
    ensureAllVariationsEnabled();
  }, 1000);

  $(document).on("found_variation", "form.cart", function (event, variation) {
    console.log("🎯 found_variation event triggered");
    console.log("Variation details:");
    console.log("- ID:", variation ? variation.variation_id : "none");
    console.log("- Price:", variation ? variation.price : "none");
    console.log("- Display Price:", variation ? variation.display_price : "none");
    console.log("- Regular Price:", variation ? variation.regular_price : "none");
    console.log("- Attributes:", variation ? variation.attributes : "none");

    // Log current form selections for comparison
    var currentSelections = {};
    $("form.cart select").each(function () {
      currentSelections[$(this).attr("name")] = $(this).val();
    });
    console.log("Current form selections:", currentSelections);

    // Clear any existing savings display
    $("#total-savings-dv").remove();

    var savingsAmount = "";
    if (variation && variation.save && variation.save !== "") {
      savingsAmount = variation.save;
    }

    // Insert savings if present
    if (savingsAmount) {
      $('<div id="total-savings-dv">TOTAL SAVINGS: ' + savingsAmount).insertBefore(".single_add_to_cart_button");
    }

    if (variation) {
      var $btn = $(".single_add_to_cart_button.elementor-button");
      // Use display_price for final sale price, fallback to price
      var finalPrice = "";
      if (variation.display_price !== undefined && variation.display_price !== "") {
        finalPrice = variation.display_price;
        console.log("💰 Using display_price:", finalPrice);
      } else if (variation.price !== undefined && variation.price !== "") {
        finalPrice = variation.price;
        console.log("💰 Using price:", finalPrice);
      }

      if (finalPrice) {
        // Format the price with currency symbol
        var formattedPrice = "";
        if (typeof wc_price !== "undefined") {
          formattedPrice = wc_price(finalPrice);
        } else {
          // Fallback formatting
          formattedPrice = "$" + parseFloat(finalPrice).toFixed(2);
        }
        console.log("💰 Final formatted price:", formattedPrice);

        // Update the entire button text to show "Add to cart - $price"
        var buttonText = "Add to cart - " + formattedPrice;
        if ($btn.find(".elementor-button-text").length) {
          $btn.find(".elementor-button-text").text(buttonText);
        } else {
          $btn.text(buttonText);
        }
        console.log("✅ Updated ATC button text:", buttonText);
      } else {
        console.log("❌ No valid price found in variation");
      }
    } else {
      console.log("❌ No variation data received");
    }

    // Clear any existing badge display
    $("#vt-offer-badge").remove();
    $("#vt-accordion-container").remove();

    // Add badge and accordion if present in variation data
    if (variation && variation.vt_offer_label) {
      var badgeHtml = '<div id="vt-offer-badge" class="vt-offer-badge">' + variation.vt_offer_label + "</div>";
      $(".single_add_to_cart_button").before(badgeHtml);
    }

    if (variation && variation.vt_dd_preview && variation.vt_dd_text) {
      var accordionHtml =
        '<div id="vt-accordion-container" class="vt-accordion-container">' +
        '<div class="vt-accordion-header">' +
        variation.vt_dd_preview +
        " <span class='vt-accordion-icon'>+</span>" +
        "</div>" +
        '<div class="vt-accordion-content" style="display: none;">' +
        variation.vt_dd_text;

      // Add savings info if available
      if (variation.vt_msrp && variation.vt_now && variation.vt_saving) {
        accordionHtml += "<br><br><strong>MSRP:</strong> $" + variation.vt_msrp;
        accordionHtml += "<br><strong>Now:</strong> $" + variation.vt_now;
        accordionHtml += "<br><strong>Total Savings:</strong> $" + variation.vt_saving;
      }

      accordionHtml += "</div></div>";

      $(".single_add_to_cart_button").before(accordionHtml);

      // Add click handler for accordion
      $("#vt-accordion-container .vt-accordion-header").on("click", function () {
        var $content = $(this).next(".vt-accordion-content");
        var $icon = $(this).find(".vt-accordion-icon");

        if ($content.is(":visible")) {
          $content.slideUp();
          $icon.text("+");
        } else {
          $content.slideDown();
          $icon.text("-");
        }
      });
    }
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
    // Clear previous prices
    $('.cgkit-attribute-swatches[data-attribute="' + bundleAttribute + '"] .tile-price').empty();
    // Update with new prices
    Object.keys(availableBundles).forEach(function (bundle) {
      // Remove only "Total:" text and colon, keep the full price display
      var priceHtml = availableBundles[bundle];
      var cleanPriceHtml = "";

      if (priceHtml) {
        // Remove "Total:" text and colon, keep everything else
        cleanPriceHtml = priceHtml.replace(/Total:\s*/g, "");
      }

      var $target = $('.cgkit-attribute-swatches[data-attribute="' + bundleAttribute + '"]')
        .find('.cgkit-swatch[data-attribute-value="' + bundle + '"]')
        .find(".tile-price");

      console.log("Updating price for bundle:", bundle, "Target found:", $target.length, "Price:", cleanPriceHtml);

      $target.html(cleanPriceHtml);
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

    // Update bundle swatch images based on current combination
    updateBundleSwatchImages($form, selectedAttributes, bundleAttribute);

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

      // Update images if we have enough attributes and a bundle attribute
      if (Object.keys(selectedAttributes).length >= 2 && bundleAttribute) {
        setTimeout(function () {
          updateBundleSwatchImages($form, selectedAttributes, bundleAttribute);
        }, 100); // Small delay to ensure the selection is processed
      }
    }
  });

  // Trigger initial image sync when page loads
  $(document).ready(function () {
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

        if (Object.keys(selectedAttributes).length >= 2 && bundleAttribute) {
          updateBundleSwatchImages($form, selectedAttributes, bundleAttribute);
        }
      }
    }, 500);
  });

  // Function to update bundle swatch images
  function updateBundleSwatchImages($form, selectedAttributes, bundleAttribute) {
    var variations = $form.data("product_variations");
    if (!variations) return;

    // Get only image-type bundle swatches
    var $bundleSwatches = $(
      '.cgkit-attribute-swatches[data-attribute="' +
        bundleAttribute +
        '"] .cgkit-attribute-swatch.cgkit-image .cgkit-swatch'
    );

    $bundleSwatches.each(function () {
      var $swatch = $(this);
      var bundleValue = $swatch.data("attribute-value");

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

      // Special handling for grill-only: always use "none" for front bench
      if (bundleValue === "grill-only") {
        targetCombination["attribute_pa_front-bench"] = "none";
      }

      // Find the matching variation for this bundle with current attributes
      var matchingVariation = null;
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

      // Update the swatch image if we found a matching variation
      if (matchingVariation && matchingVariation.image) {
        var $swatchImg = $swatch.find("img");
        if ($swatchImg.length) {
          $swatchImg.attr("src", matchingVariation.image.src);
          $swatchImg.attr("srcset", matchingVariation.image.srcset || "");
          $swatchImg.attr("sizes", matchingVariation.image.sizes || "");
          $swatchImg.attr("alt", matchingVariation.image.alt || "");
        } else {
          // If no image exists, create one
          var $newImg = $(
            '<img src="' + matchingVariation.image.src + '" alt="' + (matchingVariation.image.alt || "") + '" />'
          );
          $swatch.prepend($newImg);
        }
      }

      // Also update the price for this bundle using the same matching variation
      if (matchingVariation && matchingVariation.price_html) {
        var priceHtml = matchingVariation.price_html;
        var cleanPriceHtml = priceHtml.replace(/Total:\s*/g, "");
        $swatch.find(".tile-price").html(cleanPriceHtml);
      }
    });
  }

  $(".variations_form").on("woocommerce_variation_select_change", function (e, v) {});
  $(document).on("show_variation", function (event, variation) {});

  $(document.body).on("woocommerce_variation_has_changed", function (variant, obj) {
    isChangedtoNone = false;
  });

  // Handle variation resets
  $(document).on("woocommerce_reset_variations", function () {
    setTimeout(function () {
      ensureAllVariationsEnabled();
    }, 100);
  });

  // Additional logic to maintain front bench hiding for grill-only
  $(document).on("woocommerce_update_variation_values woocommerce_variation_select_change", function () {
    // If grill-only is selected, ensure front bench stays hidden
    if ($("#pa_bundles").val() === "grill-only") {
      setTimeout(function () {
        $('ul[data-attribute="attribute_pa_front-bench"]').parents("tr").hide();
      }, 50);
    }
  });

  $(document).on("click", '.cgkit-swatch[data-attribute-value="grill-only"]', function (ele) {
    var $clickedSwatch = $(ele.target);
    var $form = $(".variations_form");

    // Preserve ALL current selections using CommerceKit-aware system
    var allCurrentSelections = captureCurrentSelections();
    console.log("Current selections before grill-only click:", allCurrentSelections);

    // Handle grill-only selection
    if (!$clickedSwatch.hasClass("cgkit-swatch-selected")) {
      // Remove selection from other bundle swatches
      $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch').removeClass(
        "cgkit-swatch-selected"
      );
      // Add selection to grill-only
      $clickedSwatch.addClass("cgkit-swatch-selected");
      $("#pa_bundles").val("grill-only");
    }

    // Set front bench to "none" and hide the front bench selection for grill-only
    $("#pa_front-bench").val("none");
    $('ul[data-attribute="attribute_pa_front-bench"]').parents("tr").hide();
    $(".single_add_to_cart_button").removeClass("disabled");

    // Restore ALL other selections (especially controller)
    setTimeout(function () {
      // Ensure grill-only stays selected
      if (!$clickedSwatch.hasClass("cgkit-swatch-selected")) {
        $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch').removeClass(
          "cgkit-swatch-selected"
        );
        $clickedSwatch.addClass("cgkit-swatch-selected");
      }

      // Restore all other preserved selections
      Object.keys(allCurrentSelections).forEach(function (attribute) {
        if (attribute !== "attribute_pa_bundles") {
          // Don't restore bundle, we want grill-only
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
            $('.cgkit-attribute-swatches[data-attribute="' + attribute + '"] .cgkit-swatch').removeClass(
              "cgkit-swatch-selected"
            );
            // Add selection to correct swatch
            $targetSwatch.addClass("cgkit-swatch-selected");

            // Also update the select dropdown
            var selectName = attribute.replace("attribute_", "");
            $("#" + selectName).val(value);
            console.log("Restored", attribute, "to:", value);
          }
        }
      });

      // Ensure all variations stay enabled
      ensureAllVariationsEnabled();

      // Trigger variation events
      $form.trigger("woocommerce_variation_select_change");
      $form.trigger("check_variations");

      // Final check and manual variation trigger
      setTimeout(function () {
        // Final restoration to ensure nothing was lost
        Object.keys(allCurrentSelections).forEach(function (attribute) {
          if (attribute !== "attribute_pa_bundles") {
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

        // Ensure grill-only styling is applied
        if (!$clickedSwatch.hasClass("cgkit-swatch-selected")) {
          $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch').removeClass(
            "cgkit-swatch-selected"
          );
          $clickedSwatch.addClass("cgkit-swatch-selected");
        }

        // Manual variation lookup and trigger
        var variations = $form.data("product_variations");
        if (variations) {
          var currentSelection = {
            attribute_pa_bundles: "grill-only",
            "attribute_pa_front-bench": "none",
          };

          // Include all other current selections
          $form.find("select").each(function () {
            var attrName = $(this).attr("name");
            var attrValue = $(this).val();
            if (attrValue && attrName !== "attribute_pa_bundles" && attrName !== "attribute_pa_front-bench") {
              currentSelection[attrName] = attrValue;
            }
          });

          console.log("Final selection for variation lookup:", currentSelection);

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

          // Trigger found_variation event manually
          if (matchingVariation) {
            console.log("Found matching variation:", matchingVariation.variation_id);
            $form.trigger("found_variation", [matchingVariation]);
          } else {
            console.log("No matching variation found for:", currentSelection);
          }
        }
      }, 100);
    }, 50);
  });

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
      console.log("Current selections before bundle change:", allCurrentSelections);

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
      console.log("🪵 [Old Handler] Current front bench when switching to", bundleValue, ":", currentFrontBench);

      // Also check what the visual swatch shows
      var selectedSwatch = $(
        '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch-selected'
      );
      var visuallySelected = selectedSwatch.length ? selectedSwatch.data("attribute-value") : null;
      console.log("🪵 [Old Handler] Visual swatch selected:", visuallySelected);

      // CRITICAL FIX: Check visual swatch selection, not just dropdown value
      var shouldUseDefault =
        (!currentFrontBench || currentFrontBench === "none") && (!visuallySelected || visuallySelected === "none");
      console.log("🪵 [Old Handler] Should use stainless-steel default?", shouldUseDefault);

      if (shouldUseDefault) {
        console.log("🪵 [Old Handler] Setting default stainless-steel because front bench was:", currentFrontBench);
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
        console.log("🪵 [Old Handler] Preserving user's front bench selection:", valueToPreserve);

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
              $('.cgkit-attribute-swatches[data-attribute="' + attribute + '"] .cgkit-swatch').removeClass(
                "cgkit-swatch-selected"
              );
              // Add selection to correct swatch
              $targetSwatch.addClass("cgkit-swatch-selected");

              // Also update the select dropdown
              var selectName = attribute.replace("attribute_", "");
              $("#" + selectName).val(value);
              console.log("Restored", attribute, "to:", value);
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

          var $stainlessSteel = $(
            '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch[data-attribute-value="stainless-steel"]'
          );
          if (!$stainlessSteel.hasClass("cgkit-swatch-selected")) {
            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch').removeClass(
              "cgkit-swatch-selected"
            );
            $stainlessSteel.addClass("cgkit-swatch-selected");
          }

          // Manual variation lookup and trigger for price updates
          var variations = $form.data("product_variations");
          if (variations) {
            var currentSelection = {
              attribute_pa_bundles: bundleValue,
              "attribute_pa_front-bench": $("#pa_front-bench").val(), // Use whatever is currently selected
            };

            console.log("🪵 [Old Handler] Using current front bench for variation lookup:", $("#pa_front-bench").val());

            // Include all other current selections
            $form.find("select").each(function () {
              var attrName = $(this).attr("name");
              var attrValue = $(this).val();
              if (attrValue && !currentSelection[attrName]) {
                currentSelection[attrName] = attrValue;
              }
            });

            console.log("Bundle changed to", bundleValue, "looking for variation:", currentSelection);

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
              console.log(
                "Found variation for bundle change:",
                matchingVariation.variation_id,
                "price:",
                matchingVariation.display_price || matchingVariation.price
              );
              $form.trigger("found_variation", [matchingVariation]);
            } else {
              console.log("No variation found for bundle change:", currentSelection);
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

          console.log("Preserved controller:", controllerValue, "and ensured front bench hidden");
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

          console.log("Controller changed to", controllerValue, "looking for grill-only variation:", currentSelection);

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
            console.log(
              "Found variation for controller change:",
              matchingVariation.variation_id,
              "price:",
              matchingVariation.display_price || matchingVariation.price
            );
            $form.trigger("found_variation", [matchingVariation]);
          } else {
            console.log("No variation found for controller change:", currentSelection);
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

            console.log("Final preservation - Controller:", controllerValue, "Front bench hidden");
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
    var $controllerSwatches = $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"]');
    var $nonWirelessButton = $controllerSwatches.find('.cgkit-swatch[data-attribute-value="non-wireless"]');
    var $wirelessButton = $controllerSwatches.find('.cgkit-swatch[data-attribute-value="wireless-enabled"]');

    // Check current selection
    var isNonWirelessSelected = $nonWirelessButton.hasClass("cgkit-swatch-selected");
    var isWirelessSelected = $wirelessButton.hasClass("cgkit-swatch-selected");

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

    // Show/hide message based on selection (using CSS classes like old plugin)
    if (isNonWirelessSelected) {
      $(".select-wireless-txt").addClass("show-txt");
    } else {
      $(".select-wireless-txt").removeClass("show-txt");
    }

    // Handle stock messages with same logic
    var $inStockMessage = $("#vt-in-stock-message");
    var $lowStockMessage = $("#vt-low-stock-message");

    console.log("🔍 Stock Message Debug:");
    console.log("- isWirelessSelected:", isWirelessSelected);
    console.log("- isNonWirelessSelected:", isNonWirelessSelected);
    console.log("- controllerSwatches length:", $controllerSwatches.length);

    // Show appropriate stock message based on controller selection
    if (isWirelessSelected && $controllerSwatches.length > 0) {
      // Show "In Stock" when Wireless Enabled is selected
      console.log("✅ Showing In Stock (Wireless Selected)");
      $inStockMessage.show();
      $lowStockMessage.hide();
    } else if (isNonWirelessSelected && $controllerSwatches.length > 0) {
      // Show "Low in Stock" when Non-Wireless is selected
      console.log("✅ Showing Low in Stock (Non-Wireless Selected)");
      $inStockMessage.hide();
      $lowStockMessage.show();
    } else {
      // Hide both when no selection or other cases
      console.log("✅ Hiding both messages (No selection)");
      $inStockMessage.hide();
      $lowStockMessage.hide();
    }
  }

  // Trigger on controller changes
  $(document).on(
    "click change",
    '.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch',
    function () {
      setTimeout(handleFutureproofMessage, 100);
    }
  );

  // Trigger on page load
  handleFutureproofMessage();

  // Update badges and savings on page load
  setTimeout(function () {
    updateVariationBadges(); // Update badges based on current variation
  }, 500);

  // Continuously monitor and update badges based on variation changes
  setInterval(function () {
    updateVariationBadges();
  }, 1000);

  // Monitor badges and savings - check every 2 seconds
  setInterval(function () {
    // Check if badges exist and are visible
    $(".tile-offer").each(function () {
      var $badge = $(this);
      if (!$badge.is(":visible") || $badge.css("position") !== "absolute" || $badge.css("opacity") === "0") {
        updateVariationBadges();
        return false; // Break the loop
      }
    });
  }, 2000);

  // Prevent other scripts from removing badges and savings (but allow our own removal)
  var originalRemove = $.fn.remove;
  $.fn.remove = function () {
    var $this = $(this);
    if (($this.hasClass("tile-offer") || $this.attr("id") === "vt-total-savings") && !window.isRemovingBadges) {
      return this;
    }
    return originalRemove.apply(this, arguments);
  };

  // Hook into WooCommerce variation events
  $(document).on("found_variation", function (event, variation) {
    setTimeout(function () {
      updateVariationBadges();
    }, 100);
  });

  $(document).on("reset_data", function () {
    setTimeout(function () {
      updateVariationBadges();
    }, 100);
  });

  // Hook into CommerceKit swatch clicks
  $(document).on("click", ".cgkit-swatch", function () {
    setTimeout(function () {
      updateVariationBadges();
    }, 200);
  });
});
