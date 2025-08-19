jQuery(document).ready(function ($) {
  if ($(".variations-defaults strong").length) {
    let label = $(".variations-defaults strong").html();
    $(".variations-defaults strong").html(label.replace("Default Form Values", "Default Product Variant"));
  }
  $("#variable_pricing").on("change", function () {
    if ($(this).prop("checked") == true) {
      $("#variable_pricing_0").val("yes");
    } else if ($(this).prop("checked") == false) {
      $("#variable_pricing_0").val("no");
    }
    $(".save-variation-changes").removeAttr("disabled");
    $(".woocommerce_variation").first().addClass("variation-needs-update");
  });

  function variable_manage_gz_bundle(event) {
    console.log("here gz bundle");
    $("input.variable_is_zg_bundle").trigger("change");
  }

  function variable_is_zg_bundle() {
    $(this).closest(".woocommerce_variation").find(".show_if_zg_bundle").hide();
    if ($(this).is(":checked")) {
      $(this).closest(".woocommerce_variation").find(".show_if_zg_bundle").show();
    }
  }

  $("input.variable_is_downloadable, input.variable_is_virtual, input.variable_manage_stock").trigger("change");

  $(document.body).on("woocommerce_variations_loaded", variable_manage_gz_bundle);
  $(document.body).on("woocommerce_variations_added", variable_manage_gz_bundle);
  $(document.body).on("woocommerce_variations_removed", variable_manage_gz_bundle);

  $("#variable_product_options").on("change", "input.variable_is_zg_bundle", variable_is_zg_bundle);
});
