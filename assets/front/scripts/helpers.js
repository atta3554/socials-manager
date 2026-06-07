jQuery(function($) {
  window.smParseAjaxError = function(xhr) {
    if(xhr && xhr.responseJSON) {
      return xhr.responseJSON;
    }

    if(xhr && xhr.responseText) {
      try {
        return JSON.parse(xhr.responseText);
      } catch (e) {
        return {
          data: {
            title: 'Error',
            message: xhr.statusText || 'Request failed.'
          }
        };
      }
    }

    return {
      data: {
        title: 'Error',
        message: 'Request failed.'
      }
    };
  }

  window.sm_ajax = function($form, ajax_data) {
    $.ajax({
      url: ajax_data.ajax_url,
      type: "POST",
      data : $form.serialize(),
      beforeSend: () => $('<div class="loader"><div></div></div>').insertBefore($form),
      success: (res)=> swalFire(res, 'success'),
      error: (err)=> swalError(smParseAjaxError(err)),
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
    response = response && response.data ? response : smParseAjaxError(response);

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
