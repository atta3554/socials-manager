(function($) {

  $(function() {
    $('#social_media').on('change', (event) => {
      
      if(!event.target.value) {
        $('.submit-btn', $('#submit-socials'))
        .add($('.description', $('#submit-socials')))
        .removeClass('active');
      }

      $('#submit-socials')
      .find('fieldset.active')
      .removeClass('active');
      
      $('#submit-socials')
      .find('fieldset.' + event.target.value)
      .add($('.submit-btn', $('#submit-socials')))
      .add($('.description', $('#submit-socials')))
      .addClass('active');
      
    })

    $('#submit-socials').on('submit', function(event) {
      event.preventDefault()
      sm_ajax($(this), ajax_data);
    })

  });

})(jQuery);