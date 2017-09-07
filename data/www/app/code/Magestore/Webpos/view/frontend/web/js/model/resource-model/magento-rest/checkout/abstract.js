/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'Magestore_Webpos/js/helper/general',
        'Magestore_Webpos/js/model/resource-model/magento-rest/abstract'
    ],
    function ($, Helper, onlineAbstract) {
        "use strict";

        return onlineAbstract.extend({
            initialize: function () {
                this._super();
            },
            getCallBackEvent: function(key){
            },
            setApiUrl: function(key,value){
            },
            getApiUrl: function(key){
            },
            /**
             * Function to send API request and control respose
             * @param apiUrl
             * @param params
             * @param deferred
             * @param callBackEvent
             * @param method
             */
            callApi: function(apiUrl, params, deferred, callBackEvent, method){
                var self = this;
                method = (method)?method:'post';
                self.callRestApi(apiUrl, method, {}, params, deferred, callBackEvent);
                deferred.done(function (response) {
                    if(typeof response == 'string'){
                        response = JSON.parse(response);
                    }
                    self.processResponseData(response);
                }).fail(function (response) {
                    if(typeof response == 'string'){
                        response = JSON.parse(response);
                    }
                    if(response.responseText){
                        var error = JSON.parse(response.responseText);
                        if(error.message != undefined){
                            Helper.alert({priority:"danger",title: Helper.__("Error"),message: error.message});
                        }
                    }else{
                        Helper.alert({priority:"danger",title: Helper.__("Error"),message: Helper.__('Please check your network connection. Or disable checkout online to continue.')});
                    }
                }).always(function(response){
                    if(typeof response == 'string'){
                        response = JSON.parse(response);
                    }
                    response = (response.responseText)?JSON.parse(response.responseText):response;
                    if(response.messages){
                        self.processResponseMessages(response.messages, response.status);
                    }
                });
            },
            /**
             * Function to process response data - update sections - online checkout
             * @param data
             */
            processResponseData: function(data){
                var self = this;
                if(data){
                    Helper.dispatchEvent('checkout_call_api_after', {
                        data: data
                    });
                    if (typeof data.quote_id != 'undefined' && !data.quote_id) {
                        data.quote_init = {quote_id: "", customer_id: ""};
                    }
                    if (data.quote_init && !data.quote_init.customer_id) {
                        data.quote_init.customer_id = "";
                    }
                    Helper.dispatchEvent('init_quote_online_after', {
                        data: data.quote_init
                    });
                    if(!data.increment_id) {
                        if (data.shipping) {
                            Helper.dispatchEvent('load_shipping_online_after', {
                                items: data.shipping
                            });
                        }
                        if (data.totals) {
                            Helper.dispatchEvent('load_totals_online_after', {
                                items: data.totals
                            });
                        }
                        if (data.payment) {
                            Helper.dispatchEvent('load_payment_online_after', {
                                items: data.payment
                            });
                        }
                        if (data.items) {
                            Helper.dispatchEvent('load_items_online_after', {
                                items: data.items
                            });
                            Helper.dispatchEvent('collect_totals', '');
                        }
                    }
                }

            },
            /**
             * Function to process API response messages
             * @param messages
             */
            processResponseMessages: function(messages, status){
                if(messages && messages.error){
                    $.each(messages.error, function(index, message){
                        if(message.message){
                            Helper.alert({
                                priority: 'danger',
                                title: Helper.__('Error'),
                                message: message.message
                            });
                        }
                    });
                }
                if(messages && messages.success){
                    $.each(messages.success, function(index, message){
                        if(message.message){
                            Helper.alert({
                                priority: 'success',
                                title: Helper.__('Message'),
                                message: message.message
                            });
                        }
                    });
                }
                if($.isArray(messages)){
                    var priority = (status == '1')?'success':'danger';
                    var title = (status == '1')?'Message':'Error';
                    $.each(messages, function(index, message){
                        Helper.alert({
                            priority: priority,
                            title: Helper.__(title),
                            message: message
                        });
                    });
                }
            }
        });
    }
);