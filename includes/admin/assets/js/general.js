jQuery(document).ready(function ($) {
  // Only run on product edit pages to avoid conflicts
  if (!$("body").hasClass("post-type-product") || !$("#woocommerce-product-data").length) {
    return;
  }

  // Wait for WooCommerce to fully initialize before running our code
  $(document.body).on("woocommerce_variations_loaded", function () {
    // Only run our customizations after WooCommerce variations are loaded
    if ($(".variations-defaults strong").length) {
      let label = $(".variations-defaults strong").html();
      $(".variations-defaults strong").html(label.replace("Default Form Values", "Default Product Variant"));
    }
  });

  // Use more specific selectors and ensure we don't interfere with WooCommerce's event handling
  $(document.body).on("change", "#variable_pricing", function (e) {
    // Prevent event bubbling to avoid conflicts
    e.stopPropagation();

    if ($(this).prop("checked") == true) {
      $("#variable_pricing_0").val("yes");
    } else if ($(this).prop("checked") == false) {
      $("#variable_pricing_0").val("no");
    }
    $(".save-variation-changes").removeAttr("disabled");
    $(".woocommerce_variation").first().addClass("variation-needs-update");
  });

  function variable_manage_gz_bundle(event) {
    // Use setTimeout to ensure this runs after WooCommerce's handlers
    setTimeout(function () {
      $("input.variable_is_zg_bundle").trigger("change");
    }, 100);
  }

  function variable_is_zg_bundle() {
    $(this).closest(".woocommerce_variation").find(".show_if_zg_bundle").hide();
    if ($(this).is(":checked")) {
      $(this).closest(".woocommerce_variation").find(".show_if_zg_bundle").show();
    }
  }

  // Only trigger these after WooCommerce variations are loaded
  $(document.body).on("woocommerce_variations_loaded", function () {
    $("input.variable_is_downloadable, input.variable_is_virtual, input.variable_manage_stock").trigger("change");
  });

  // Use more specific event binding to avoid conflicts
  $(document.body).on("woocommerce_variations_loaded", variable_manage_gz_bundle);
  $(document.body).on("woocommerce_variations_added", variable_manage_gz_bundle);
  $(document.body).on("woocommerce_variations_removed", variable_manage_gz_bundle);

  // Use more specific selector to avoid interfering with WooCommerce's event handling
  $(document.body).on("change", "#variable_product_options input.variable_is_zg_bundle", variable_is_zg_bundle);

  // Enhanced Variant Tile Media Upload Functionality
  $(document).on("click", ".vt-media-upload-btn", function (e) {
    e.preventDefault();
    e.stopPropagation(); // Prevent event bubbling

    var loop = $(this).data("loop");
    var imageIdField = $("#_vt_dd_image_id" + loop);
    var previewContainer = $("#vt-media-preview-" + loop);
    var editBtn = $('.vt-media-edit-btn[data-loop="' + loop + '"]');
    var removeBtn = $('.vt-media-remove-btn[data-loop="' + loop + '"]');

    // Check if wp.media is available
    if (typeof wp === "undefined" || typeof wp.media === "undefined") {
      alert("Media upload is not available. Please refresh the page and try again.");
      return;
    }

    // Create a new media frame with full WordPress media library
    var media_frame = wp.media({
      title: "Select or Upload Media",
      button: {
        text: "Use this media",
      },
      multiple: false,
      library: {
        type: "image",
      },
    });

    // When media is selected, run a callback
    media_frame.on("select", function () {
      var attachment = media_frame.state().get("selection").first().toJSON();

      // Update the hidden input field
      imageIdField.val(attachment.id);

      // Update the preview with enhanced info
      var imageUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
      var previewHtml =
        '<div class="vt-media-item">' +
        '<img src="' +
        imageUrl +
        '" alt="' +
        (attachment.alt || "") +
        '" style="max-width: 150px; height: auto;" />' +
        "</div>";

      previewContainer.html(previewHtml).show();

      // Show edit and remove buttons
      editBtn.show();
      removeBtn.show();

      // Mark variation as needing update and enable save button
      imageIdField.closest(".woocommerce_variation").addClass("variation-needs-update");
      $(".save-variation-changes").removeAttr("disabled");
    });

    // Open the modal
    media_frame.open();
  });

  // Edit media functionality - opens WordPress media editor
  $(document).on("click", ".vt-media-edit-btn", function (e) {
    e.preventDefault();
    e.stopPropagation(); // Prevent event bubbling

    var loop = $(this).data("loop");
    var imageIdField = $("#_vt_dd_image_id" + loop);
    var imageId = imageIdField.val();

    if (!imageId) return;

    // Check if wp.media is available
    if (typeof wp === "undefined" || typeof wp.media === "undefined") {
      alert("Media editor is not available. Please refresh the page and try again.");
      return;
    }

    // Create the media frame for editing
    var media_frame = wp.media({
      title: "Edit Media",
      button: {
        text: "Update",
      },
      multiple: false,
      library: {
        type: "image",
      },
    });

    // Set the selected attachment
    media_frame.on("open", function () {
      var selection = media_frame.state().get("selection");
      var attachment = wp.media.attachment(imageId);
      attachment.fetch();
      selection.add(attachment ? [attachment] : []);
    });

    // When media is updated, refresh the preview
    media_frame.on("update", function () {
      var attachment = media_frame.state().get("selection").first().toJSON();
      var previewContainer = $("#vt-media-preview-" + loop);

      // Update the preview with new info
      var imageUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
      var previewHtml =
        '<div class="vt-media-item">' +
        '<img src="' +
        imageUrl +
        '" alt="' +
        (attachment.alt || "") +
        '" style="max-width: 150px; height: auto;" />' +
        "</div>";

      previewContainer.html(previewHtml);

      // Mark variation as needing update
      imageIdField.closest(".woocommerce_variation").addClass("variation-needs-update");
      $(".save-variation-changes").removeAttr("disabled");
    });

    // Open the modal
    media_frame.open();
  });

  // Remove media functionality
  $(document).on("click", ".vt-media-remove-btn", function (e) {
    e.preventDefault();
    e.stopPropagation(); // Prevent event bubbling

    var loop = $(this).data("loop");
    var imageIdField = $("#_vt_dd_image_id" + loop);
    var previewContainer = $("#vt-media-preview-" + loop);
    var editBtn = $('.vt-media-edit-btn[data-loop="' + loop + '"]');

    // Clear the hidden input field
    imageIdField.val("");

    // Clear the preview
    previewContainer.html("").hide();

    // Hide the edit and remove buttons
    editBtn.hide();
    $(this).hide();

    // Mark variation as needing update and enable save button
    imageIdField.closest(".woocommerce_variation").addClass("variation-needs-update");
    $(".save-variation-changes").removeAttr("disabled");
  });

  // Handle variations loaded event to reinitialize media upload functionality
  $(document.body).on("woocommerce_variations_loaded", function () {
    // Reinitialize any existing media previews
    $(".vt-image-id").each(function () {
      var loop = $(this).attr("id").replace("_vt_dd_image_id", "");
      var imageId = $(this).val();
      var editBtn = $('.vt-media-edit-btn[data-loop="' + loop + '"]');
      var removeBtn = $('.vt-media-remove-btn[data-loop="' + loop + '"]');

      if (imageId && imageId !== "0") {
        // Show edit and remove buttons if image exists
        editBtn.show();
        removeBtn.show();
      } else {
        // Hide edit and remove buttons if no image
        editBtn.hide();
        removeBtn.hide();
      }
    });
  });
});
