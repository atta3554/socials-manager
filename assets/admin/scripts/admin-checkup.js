let choices = document.querySelectorAll(
  '.target_specification input[type="radio"]',
);
let usersBox = document.querySelector(".users");
let users = usersBox.querySelectorAll("li");

choices.forEach((choice) =>
  choice.addEventListener("change", (e) => {
    if (e.target.value === "specefic_user") {
      usersBox.classList.add("active");
    } else {
      usersBox.classList.remove("active");
      jQuery("#user-id").val("");
      usersBox.querySelector("li.active")?.classList.remove("active");
    }
  }),
);

users.forEach((user) =>
  user.addEventListener("click", () => {
    if (user.classList.contains("active")) {
      user.classList.remove("active");
      jQuery("#user-id").val("");
    } else {
      usersBox.querySelector("li.active")?.classList.remove("active");
      user.classList.add("active");
      jQuery("#user-id").val(user.dataset.id);
    }
  }),
);

jQuery(function ($) {
  $(".validation_btns button").each(function () {
    let _this = $(this);
    _this.on("click", function () {
      let userId = $("#user-id").val();

      let data = $("fieldset").serializeArray();
      data.push({ name: "social", value: this.getAttribute("id") });

      if ($("#specefic_user").is(":checked")) {
        if (userId) {
          data.push({ name: "user-id", value: userId });
        } else {
          alert($("#invalid_validation_type").val());
          return;
        }
      } else if ($("#all_users").is(":checked")) {
        data.push({ name: "user-id", value: "all" });
      } else {
        alert($("#invalid_values").val());
        return;
      }

      data = jQuery.param(data);

      $.ajax({
        url: $("#ajax_url").val(),
        method: "POST",
        data,
        beforeSend: () =>
          $('<div class="loader"><div></div></div>').insertAfter(
            ".wrap .validation_btns",
          ),
        success: (res) => console.log(res),
        error: (err) => console.log(err),
        complete: () => $(".loader").remove(),
      });
    });
  });
});
