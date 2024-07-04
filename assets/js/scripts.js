/**
 * Admin Scripts
 */

(function ($, window, document, plugin_object) {
    "use strict";

    function send_connect_request(el_connect_btn, el_connect_guide, el_connect_btn_text) {

        if (el_connect_btn.hasClass('doing-ajax') || el_connect_btn.hasClass('done')) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: plugin_object.ajax_url,
            context: this,
            data: {
                'action': 'instawp_connect_website',
            },
            beforeSend: function () {
                el_connect_btn.addClass('doing-ajax');
            },
            success: function (response) {

                el_connect_guide.html(response.data.message);

                if (response.success) {

                    if (response.data.redirect_url) {
                        setTimeout(function () {
                            el_connect_btn_text.html('Redirecting...');
                        }, 500);

                        setTimeout(function () {
                            window.open(response.data.redirect_url, '_self');
                        }, 1000);

                        setTimeout(function () {
                            el_connect_btn_text.html('Started...');
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

    function get_query_params() {
        var params = {};
        var queryString = window.location.search.substring(1);
        var queryArray = queryString.split('&');

        for (var i = 0; i < queryArray.length; i++) {
            var pair = queryArray[i].split('=');
            params[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1] || '');
        }

        return params;
    }


    $(document).on('ready', function () {
        let el_notice_wrapper = $('.notice.notice-warning.iwp-hosting-mig-wrap'),
            el_connect_btn = el_notice_wrapper.find('.mig-button'),
            el_transfer_btn = $('button.iwp-btn-transfer'),
            el_connect_guide = el_notice_wrapper.find('.mig-guide-text'),
            query_params = get_query_params(),
            interval_id;


        if (el_notice_wrapper.hasClass('auto-activate-migration')) {
            el_connect_btn.addClass('loading').html('Connecting...');

            interval_id = setInterval(function () {
                send_connect_request(el_connect_btn, el_connect_guide, el_connect_btn)

                if (el_connect_btn.hasClass('done')) {
                    clearInterval(interval_id);
                }

            }, 1000);
        }

        if (typeof query_params.iwp_demo_site_id !== 'undefined' && typeof query_params.iwp_demo_site_url !== 'undefined') {
            localStorage.setItem('iwp_scan_demo_site', 'yes');
            localStorage.setItem('iwp_demo_site_id', query_params.iwp_demo_site_id);
            localStorage.setItem('iwp_demo_site_url', query_params.iwp_demo_site_url);

            el_transfer_btn.parent().find('.iwp-text-header span').html(query_params.iwp_demo_site_url);

            $.ajax({
                type: 'POST',
                url: plugin_object.ajax_url,
                context: this,
                data: {
                    'action': 'instawp_store_demo_site_details',
                    'iwp_demo_site_id': query_params.iwp_demo_site_id,
                    'iwp_demo_site_url': query_params.iwp_demo_site_url,
                },
                success: function (response) {
                    el_transfer_btn.parent().fadeIn();
                    console.log(response);
                }
            });
        }

        if (localStorage.getItem('iwp_scan_demo_site') !== 'yes' && typeof plugin_object.iwp_auto_migrate_url !== 'undefined') {
            window.location.href = plugin_object.iwp_auto_migrate_url;
        }
    });

    $(document).on('click', 'button.iwp-btn-transfer', function () {

        let el_transfer_btn = $(this),
            el_transfer_btn_text = el_transfer_btn.find('span'),
            el_auto_migration_wrap = el_transfer_btn.parent(),
            el_iwp_text_content = el_auto_migration_wrap.find('.iwp-text-content');

        if (el_transfer_btn.hasClass('loading')) {
            return;
        }

        el_transfer_btn.addClass('loading');
        el_transfer_btn_text.html(plugin_object.text_transferring);

        $.post(plugin_object.ajax_url, {
            action: 'iwp_install_plugin',
        })
            .then(function (response) {
                el_iwp_text_content.html(response.data.message);

                if (response.success) {
                    return $.post(plugin_object.ajax_url, {
                        action: 'iwp_set_api_key',
                    });
                }
            })
            .then(function (response) {
                el_iwp_text_content.html(response.data.message);

                if (response.success) {
                    return $.post(plugin_object.ajax_url, {
                        action: 'iwp_connect_demo_site',
                    });
                }
            })
            .then(function (response) {
                el_iwp_text_content.html(response.data.message);

                if (response.success) {
                    return $.post(plugin_object.ajax_url, {
                        action: 'iwp_initiate_migration',
                    });
                }
            })
            .then(function (response) {
                el_iwp_text_content.html(response.data.message);

                if (typeof response.data.iwp_migrate_tracking_url !== 'undefined') {
                    setTimeout(function () {
                        window.location.href = response.data.iwp_migrate_tracking_url;
                    }, 1500);
                }
            })
            .fail(function (error) {
                console.log('Error:', error);
            });
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
            send_connect_request(el_connect_btn, el_connect_guide, el_connect_btn)

            if (el_connect_btn.hasClass('done')) {
                clearInterval(interval_id);
            }

        }, 1000);

        console.log('Finished at: ' + $.now());

    });

})(jQuery, window, document, iwp_hosting_mig);

