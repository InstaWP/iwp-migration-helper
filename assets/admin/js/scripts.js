/**
 * Admin Scripts
 */

(function ($, window, document, plugin_object) {
    "use strict";

    $(document).on('click', '.notice.notice-warning.iwp-hosting-mig-wrap span.mig-button', function () {

        let el_connect_btn = $(this),
            el_hosting_mig_wrap = el_connect_btn.parent();

        if (el_hosting_mig_wrap.hasClass('connected')) {
            window.open(el_connect_btn.data('redirect'), '_blank');
            return;
        }

        el_connect_btn.addClass('loading').html('Connecting...');

        $.ajax({
            type: 'POST',
            url: plugin_object.ajax_url,
            context: this,
            data: {
                'action': 'instawp_connect_website',
            },
            success: function (response) {

                if (response.success) {
                    el_connect_btn.removeClass('loading').html('Connected.');
                }

                setTimeout(function () {
                    el_connect_btn.html('Redirecting...');
                }, 500);

                setTimeout(function () {
                    window.open(response.data.connect_url);
                }, 1000);

                console.log(response);
            },
            error: function () {

            }
        });

    });

})(jQuery, window, document, iwp_hosting_mig);

