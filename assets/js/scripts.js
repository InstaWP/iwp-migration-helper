/**
 * Admin Scripts
 */

(function ($, window, document, plugin_object) {
    "use strict";


    function send_connect_request(el_connect_btn, el_connect_guide) {

        if (el_connect_btn.hasClass('doing-ajax') || el_connect_btn.hasClass('done')) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: plugin_object.ajax_url,
            context: this,
            data: {
                'action': 'instawp_connect_website'
            },
            beforeSend: function () {
                el_connect_btn.addClass('doing-ajax');
            },
            success: function (response) {

                el_connect_guide.html(response.data.message);

                if (response.success) {

                    if (response.data.redirect_url) {
                        setTimeout(function () {
                            el_connect_btn.html('Redirecting...');
                        }, 500);

                        setTimeout(function () {
                            window.open(response.data.redirect_url, '_self');
                        }, 1000);

                        setTimeout(function () {
                            el_connect_btn.html('Started...');
                            el_connect_guide.html('Migration is going to be started...')
                        }, 500);

                        el_connect_btn.removeClass('doing-ajax').addClass('done');
                    }

                    el_connect_btn.removeClass('doing-ajax');
                } else {
                    el_connect_btn.removeClass('doing-ajax').addClass('done');
                }
            },
            error: function () {
                el_connect_btn.removeClass('doing-ajax').addClass('done');
            }
        });
    }

    $(document).on('ready', function () {
        let el_notice_wrapper = $('.notice.notice-warning.iwp-hosting-mig-wrap'),
            el_connect_btn = el_notice_wrapper.find('.mig-button'),
            el_connect_guide = el_notice_wrapper.find('.mig-guide-text'),
            interval_id;

        if (el_notice_wrapper.hasClass('auto-activate-migration')) {
            el_connect_btn.addClass('loading').html('Connecting...');

            interval_id = setInterval(function () {
                send_connect_request(el_connect_btn, el_connect_guide)

                if (el_connect_btn.hasClass('done')) {
                    clearInterval(interval_id);
                }

            }, 1000);
        }
    });

    $(document).on('click', '.notice.notice-warning.iwp-hosting-mig-wrap span.mig-button', function () {

        let el_connect_btn = $(this),
            el_hosting_mig_wrap = el_connect_btn.parent().parent(),
            el_connect_guide = el_hosting_mig_wrap.find('.mig-guide-text'),
            interval_id;

        if (el_hosting_mig_wrap.hasClass('connected')) {
            window.open(el_connect_btn.data('redirect'), '_self');
            return;
        }

        el_connect_btn.addClass('loading').html('Connecting...');

        console.log('Started at: ' + $.now());

        interval_id = setInterval(function () {
            send_connect_request(el_connect_btn, el_connect_guide)

            if (el_connect_btn.hasClass('done')) {
                clearInterval(interval_id);
            }

        }, 1000);

        console.log('Finished at: ' + $.now());

    });

})(jQuery, window, document, iwp_hosting_mig);

