/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define([
    'jquery',
    'Magestore_Webpos/js/model/resource-model/magento-rest/abstract',
    'Magestore_Webpos/js/lib/cookie',
    'mage/translate',
    'Magestore_Webpos/js/lib/jquery.toaster',
    'jquery/ui'
], function ($, restAbstract, Cookies, Translate) {
    $.widget("magestore.webposLogin", {
        _create: function () {
            var self = this, options = this.options;
            $.extend(this, {

            });
            $(this.element).mage('validation', {
                submitHandler: function (form) {
                    self.ajaxLogin();
                }
            });

        },

        ajaxLogin: function () {
            var self = this;
            var apiUrl = '/webpos/staff/login';
            var deferred = $.Deferred();
            var staff = {};
            staff.username = $(this.element).find('#username').val();
            staff.password = $(this.element).find('#pwd').val();
            var loginButton = $(this.element).find('button');
            loginButton.html(Translate('Please wait ...'));
            loginButton.find('button').prop("disabled",true);
            restAbstract().setPush(true).setLog(false).callRestApi(
                apiUrl,
                'post',
                {},
                {
                    'staff': staff
                },
                deferred
            );
            deferred.done(function (data) {

                if (data != false) {
                    Cookies.set('WEBPOSSESSION', data, { expires: parseInt(window.webposConfig.timeoutSession) });
                    Cookies.set('check_login', 1, { expires: parseInt(window.webposConfig.timeoutSession) });
                    window.location.reload();
                } else {
                    loginButton.html(Translate('Login'));
                    loginButton.prop("disabled",false);

                    $.toaster(
                        {
                            priority: 'danger',
                            title: Translate("Warning"),
                            message: Translate("Your login information is wrong!")
                        }
                    );

                }
            });
            deferred.fail(function (data) {
                var self = this;
                loginButton.html(Translate('Login'));
                loginButton.prop("disabled",false);
                $.toaster(
                    {
                        priority: 'danger',
                        title: Translate("Warning"),
                        message: Translate("Your login information is wrong!")
                    }
                );
            })
        }
    });

    return $.magestore.webposLogin;

});