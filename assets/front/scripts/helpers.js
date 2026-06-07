jQuery(function($) {
  window.sm_ajax = function($form, ajax_data) {
    $.ajax({
      url: ajax_data.ajax_url,
      type: "POST",
      data : $form.serialize(),
      beforeSend: () => $('<div class="loader"><div></div></div>').insertBefore($form),
      success: (res)=> swalFire(res, 'success'),
      error: (err)=> swalError(JSON.parse(err.responseText)),
      complete: ()=> {
        $('.loader').remove();

        $('fieldset input', $form)
        .add($('select#social_media', $form))
        .add($('div.description textarea', $form))
        .val('');

        $('.submit-btn', $form)
        .add($('.description', $form))
        .add($('fieldset.active', $form))
        .removeClass('active');
      }
    })
  }

  window.swalError = function(response) {

    Swal.fire({
      title: response.data.title,
      text: response.data.message,
      icon: "error"
    });
  }

  window.swalFire = function(response, type) {
    if(type === 'error') {
      swalError(response);
      return;
    } else if (type === 'success') {
      
      if(!response.success) {
        swalError(response);
        return;
      }

      Swal.fire({
        title: response.data.title,
        text: response.data.message,
        icon: "success",
        didDestroy: () => {
          if(response.data.url) {
            $('<div class="loader fixed"><div></div></div>').prependTo('body')
            window.location = response.data.url
          }
        }
      });
    }
  }
})