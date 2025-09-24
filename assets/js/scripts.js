/**
 * Admin Scripts
 */

(function ($, window, document, plugin_object) {
    "use strict";

    function send_connect_request(el_connect_btn, el_connect_guide, el_connect_btn_text) {

        if (el_connect_btn.hasClass('doing-ajax') || el_connect_btn.hasClass('done')) {
            return;
        }

        let postData = {
            'action': 'instawp_connect_website',
        };

        if (el_connect_btn.hasClass('e2e-mig-wo-connects')) {
            postData.e2e_mig_wo_connects = 1;
        }

        $.ajax({
            type: 'POST',
            url: plugin_object.ajax_url,
            context: this,
            data: postData,
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
                    el_connect_btn.removeClass(['doing-ajax', 'loading']).addClass('done').html(plugin_object.connect_btn_name);
                }
            },
            error: function () {
                el_connect_btn.removeClass('doing-ajax').addClass('done');
            }
        });
    }

    function displayMessage(el, el_transfer_btn, message, hasError = true) {
        if (!message) {
            return false;
        }
        if (hasError) {
            el.addClass('error-msg');
            el_transfer_btn.removeClass('loading');
            el_transfer_btn.find('span').html(plugin_object.transfer_site);
        } else {
            el.removeClass('error-msg');
        }
        el.text(message);
    }

    // Get https url
    function getHttpsUrl(url) {
        if (!url) {
            return '';
        }
        url = url.replace('http://', 'https://');
        if (!url.includes('://')) {
            url = 'https://' + url;
        }
        if (url.length < 14) {
            return '';
        }
        return url.endsWith('/') ? url.slice(0, -1) : url;
    }

    // Get post data
    function getPostData(action) {
        return {
            action: action,
            iwp_nonce: plugin_object.iwp_nonce,
        };
    }

    // Helper function to post and handle errors
    function postStep(actionData, el_msg, el_transfer_btn) {
        return $.post(plugin_object.ajax_url, actionData)
            .then(function (response) {
                displayMessage(el_msg, el_transfer_btn, response?.data?.message, !response.success);
                if (!response.success) {
                    throw response; // stops the chain
                }
                return response;
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
                send_connect_request(el_connect_btn, el_connect_guide, el_connect_btn)

                if (el_connect_btn.hasClass('done')) {
                    clearInterval(interval_id);
                }

            }, 1000);
        }
    });

    $(document).on('click', 'span.iwp-reset', function () {

        let el_reset_btn = $(this);

        if (el_reset_btn.hasClass('loading')) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: plugin_object.ajax_url,
            context: this,
            beforeSend: function () {
                el_reset_btn.addClass('loading');
            },
            data: {
                'action': 'iwp_reset_side_data',
                'reset_nonce': el_reset_btn.data('reset-nonce'),
            },
            success: function (response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });

    // Demo site url input box
    $(document).on('mouseout', '#iwp-demo-site-url-input', function () {

        if (!plugin_object.has_demo_url_box) {
            return false;
        }

        let el_input = $(this),
            el_button = $('button.iwp-btn-transfer');

        if (el_input && el_button) {
            let demo_site_url = el_input.val();
            if (demo_site_url) {
                demo_site_url = getHttpsUrl(demo_site_url);
                el_input.val(demo_site_url);
            }
            demo_site_url = Boolean(demo_site_url);
            el_button.toggleClass('disabled', !demo_site_url);
            el_button.prop('disabled', !demo_site_url);
        }
    });

    // Auto Migration
    $(document).on('click', 'button.iwp-btn-transfer', function () {

        let el_transfer_btn = $(this),
            el_input = $('#iwp-demo-site-url-input'),
            el_admin_email = $('#iwp-demo-site-email-input'),
            el_transfer_btn_text = el_transfer_btn.find('span'),
            el_auto_migration_wrap = el_transfer_btn.parent(),
            el_msg = $('#iwp-mig-res-message');

        if (el_transfer_btn.hasClass('loading')) {
            return;
        }

        el_transfer_btn_text.html(plugin_object.text_transferring);
        const postData = getPostData('iwp_set_data_install_plugin');
        if (plugin_object.has_demo_url_box) {
            if (!el_input || !el_input.val()) {
                displayMessage(el_msg, el_transfer_btn, plugin_object.demo_site_url_required, true);
                return false;
            }
            postData.demo_site_url = getHttpsUrl(el_input.val());
            postData.admin_email = el_admin_email.val();
        }

        el_transfer_btn.addClass('loading');
        postStep(postData, el_msg, el_transfer_btn)
            .then(() => postStep(getPostData('iwp_set_api_key'), el_msg, el_transfer_btn))
            .then(() => postStep(getPostData('iwp_connect_demo_site'), el_msg, el_transfer_btn))
            .then(() => postStep(getPostData('iwp_initiate_migration'), el_msg, el_transfer_btn))
            .then(function (response) {
                displayMessage(el_msg, el_transfer_btn, response?.data?.message, !response.success);
                if (response.data?.iwp_migrate_tracking_url) {
                    setTimeout(function () {
                        window.location.href = response.data.iwp_migrate_tracking_url;
                    }, 1500);
                }
            })
            .fail(function (error) {
                displayMessage(el_msg, el_transfer_btn, error?.data?.message || error.responseText || 'An error occurred.', true);
                console.error('Error:', error);
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

