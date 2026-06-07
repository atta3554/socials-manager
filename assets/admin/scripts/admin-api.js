(function ($) {
  $(function () {
    $('[id*="-provider"]').each(function (select) {
      $(this).on("change", function (event) {
        const parent = $(this).closest(".field");
        if (event.target.value === "apify") {
          parent.find('[class*="-actor-id"]').addClass("active");
          parent.find('[class*="-official-data"]').removeClass("active");
        } else if (event.target.value === "official") {
          parent.find('[class*="-actor-id"]').removeClass("active");
          parent.find('[class*="-official-data"]').addClass("active");
        } else {
          parent.find('[class*="-actor-id"]').removeClass("active");
          parent.find('[class*="-official-data"]').removeClass("active");
        }
      });
    });
  });
})(jQuery);
