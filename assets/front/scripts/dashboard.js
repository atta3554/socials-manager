(function ($) {
  $(function () {
    function editSocial() {
      let $this = $(this);
      let url = $this.closest(".social").data("social-url");
      let userId = $(".user-socials").data("user-id");
      let editingSocial = SM_USER_SOCIALS.find(
        (social) => social[social["social_name"] + "-url"] === url,
      );

      if (!editingSocial) {
        let data = {
          title: SM_DATA.i18n.social_not_found_title,
          message: SM_DATA.i18n.social_not_found_message,
        };
        swalError({ data });
        return;
      }

      let socialName = editingSocial["social_name"];
      let socialUrl = editingSocial[socialName + "-url"];
      let socialdesc = editingSocial["description"];
      let fieldset = $(`.submit-socials-wrapper fieldset.${socialName}`).filter(
        function () {
          return $(this).data("social-url").trim() === url.trim();
        },
      );

      fieldset.find("input").val(socialUrl);
      $(`.submit-socials-wrapper .form-group.description textarea`).val(
        socialdesc,
      );
      fieldset.addClass("active");
      $(".submit-socials-wrapper").addClass("active");
      $("#submit-socials").data("social-url", socialUrl);
      $("#submit-socials").data("social-name", socialName);
    }

    function deleteSocial() {
      let $this = $(this);
      let url = $this.closest(".social").data("social-url");
      let userId = $(".user-socials").data("user-id");
      let deletingSocial = SM_USER_SOCIALS.find(
        (social) => social[social["social_name"] + "-url"] === url,
      );

      if (!deletingSocial) {
        let data = {
          title: SM_DATA.i18n.social_not_found_title,
          message: SM_DATA.i18n.social_not_found_message,
        };
        swalError({ data });
        return;
      }

      Swal.fire({
        title: SM_DATA.i18n.remove_social_confirmation_title,
        text: SM_DATA.i18n.remove_social_confirmation_message,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: SM_DATA.i18n.confirm_remove_button_text,
        cancelButtonText: SM_DATA.i18n.cancel_remove_button_text,
        reverseButtons: true,
      }).then((result) => {
        if (!result.isConfirmed) {
          return;
        }

        const { ajax_url, nonce_field, remove_nonce } = SM_DATA;

        $.ajax({
          url: ajax_url,
          type: "POST",
          data: {
            action: "sm_remove_social",
            [nonce_field]: remove_nonce,
            user: userId,
            social: deletingSocial,
          },
          beforeSend: () =>
            $("body").append('<div class="loader"><div></div></div>'),
          success: (res) => {
            Swal.fire({
              title: res.data.title,
              text: res.data.message,
              icon: "success",
              didDestroy: () => {
                $this.closest(".social").animate(
                  {
                    width: "toggle",
                  },
                  300,
                );
              },
            });
          },
          error: (err) => swalError(smParseAjaxError(err)),
          complete: () => {
            $(".loader").remove();
          },
        });
      });
    }

    $(".user-socials .edit").each(function () {
      $(this).on("click", editSocial);
    });

    $(".user-socials .delete").each(function () {
      $(this).on("click", deleteSocial);
    });

    $(".submit-socials-wrapper .close button").on("click", function (e) {
      e.preventDefault();
      $(".submit-socials-wrapper fieldset.active").removeClass("active");
      $(".submit-socials-wrapper.active").removeClass("active");
    });

    $("#submit-socials").on("submit", function (e) {
      e.preventDefault();
      let $form = $(this);
      let data = $form.serializeArray();

      data = data.filter((field) => {
        return (
          !field.name.includes("url") ||
          (field.name.includes("url") &&
            field.value.trim() === $form.data("social-url").trim())
        );
      });

      data = [
        ...data,
        {
          name: "social[social_name]",
          value: $form.data("social-name").trim(),
        },
      ];

      $.ajax({
        url: SM_DATA.ajax_url,
        type: "POST",
        data: $.param(data),
        beforeSend: () =>
          $('<div class="loader"><div></div></div>').insertBefore($form),
        success: (res) => swalFire(res, "success"),
        error: (err) => swalError(smParseAjaxError(err)),
        complete: () => {
          $(".loader").remove();

          $(".submit-socials-wrapper fieldset.active")
            .add($(".submit-socials-wrapper.active"))
            .removeClass("active");
        },
      });
    });
  });
})(jQuery);
