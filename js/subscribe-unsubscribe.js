jQuery(document).ready(function($) {
    var submit = jQuery('.subscribe-now-subscribe-submit-button');

    if (submit.length === 0) {
        return false;
    }

    submit.on('click', function() {
        var button = $(this);

        var data = {
            'action': 'subscribe_user',
            'email': button.parent().find('.subscribe-now-subscribe-email').val()
        };
        button.find('span').addClass('shown');

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(SUBSCRIBE_NOW_ADMIN_AJAX, data, function(response) {
            button.find('span').removeClass('shown');

            var ptag = button.parent().find('p');
            var result = JSON.parse(response);

            ptag.show();
            ptag.text(result.message);
        });
    })
});

jQuery(document).ready(function($) {
    var submit = jQuery('.subscribe-now-unsubscribe-submit-button');

    if (submit.length === 0) {
        return false;
    }

    submit.on('click', function() {
        var button = $(this);

        var data = {
            'action': 'unsubscribe_user',
            'email': button.parent().find('.subscribe-now-unsubscribe-email').val()
        };
        button.find('span').addClass('shown');

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(SUBSCRIBE_NOW_ADMIN_AJAX, data, function(response) {
            button.find('span').removeClass('shown');

            var ptag = button.parent().find('p');
            var result = JSON.parse(response);

            ptag.show();
            ptag.text(result.message);
        });
    })
});