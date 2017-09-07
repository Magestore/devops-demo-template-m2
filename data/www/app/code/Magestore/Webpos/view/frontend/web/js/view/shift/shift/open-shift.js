/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [ 'jquery',
        'ko',
        'uiComponent',
        'Magestore_Webpos/js/model/shift/shift',
        'Magestore_Webpos/js/model/shift/current-shift',
        'Magestore_Webpos/js/helper/price',
        'Magestore_Webpos/js/helper/shift',
        'Magestore_Webpos/js/action/notification/add-notification',
        'Magestore_Webpos/js/model/resource-model/magento-rest/shift/shift',
        'Magestore_Webpos/js/helper/datetime',
        'Magestore_Webpos/js/model/event-manager',
    ],
    function ($, ko, Component, shift, currentShift, priceHelper, shiftHelper, notification, onlineResource, datetimeHelper, Event) {
        "use strict";

        return Component.extend({
            float_amount: ko.observable(''),
            opened_note: ko.observable(''),
            locationId: ko.observable(window.webposConfig.locationId),
            shiftId: ko.observable(window.webposConfig.shiftId),
            floatAmountFormatted: ko.observable(''),
            staffId: ko.observable(window.webposConfig.staffId),
            staffName: ko.observable(window.webposConfig.staffName),

            defaults: {
                template: 'Magestore_Webpos/shift/shift/open-shift',
            },

            initialize: function () {
                this._super();

                this.floatAmountFormatted = ko.pureComputed(function () {

                    return priceHelper.formatPrice(this.float_amount());
                }, this)
            },

            /**
             * prepare data to update shift to online database using rest Api
             */

            getOnlineData: function () {
                var postData = {};
                postData.float_amount = priceHelper.toPositiveNumber(this.float_amount());
                postData.base_float_amount = priceHelper.currencyConvert(postData.float_amount, window.webposConfig.currentCurrencyCode, window.webposConfig.baseCurrencyCode);
                postData.balance = priceHelper.toPositiveNumber(this.float_amount());
                postData.base_balance = priceHelper.currencyConvert(postData.balance, window.webposConfig.currentCurrencyCode, window.webposConfig.baseCurrencyCode);
                postData.opened_note = this.opened_note();
                postData.base_currency_code = window.webposConfig.baseCurrencyCode;
                postData.shift_currency_code = window.webposConfig.currentCurrencyCode;
                postData.status = 0;
                postData.staff_id = priceHelper.toPositiveNumber(this.staffId());
                postData.location_id = priceHelper.toPositiveNumber(this.locationId());
                postData.opened_at = datetimeHelper.getBaseSqlDatetime();
                postData.closed_amount = 0;
                postData.base_closed_amount = 0;
                postData.closed_at = datetimeHelper.getBaseSqlDatetime();
                postData.closed_note = "";
                postData.cash_left = 0;
                postData.base_cash_left = 0;
                postData.total_sales = 0;
                postData.base_total_sales = 0;
                postData.cash_added = postData.float_amount;
                postData.base_cash_added = postData.base_float_amount;
                postData.cash_removed = 0;
                postData.base_cash_removed = 0;
                postData.cash_sale = 0;
                postData.base_cash_sale = 0;
                return postData;
            },

            /**
             * get all data of a shift to be store in indexed-db.
             * The data format is like an item in listing page.
             */
            getOfflineData: function () {
                var postData = this.getOnlineData();
                postData['entity_id'] = 0;
                postData['sale_summary'] = [];
                postData['cash_transaction'] = [];
                postData['zreport_sales_summary'] = {grand_total: 0, discount_amount: 0, total_refunded: 0}
                return postData;
            },

            OpenNewShift: function () {
                
                var self = this;
                var shiftModel = shift();
                var offlineData = self.getOfflineData();
                
                var deferred = shiftModel.setData(offlineData).setMode('offline').update();
                deferred.done(function (response) {

                    if(response){
                        Event.dispatch('refresh_shift_listing',response);
                        self.syncShift(response.shift_id);
                    }
                });

                this.closeForm();
                //show notification
                //notification('Shift opened! Float Amount=' + priceHelper.formatPrice(postData.float_amount), true, 'success', 'Notice');
            },

            syncShift: function (shiftId) {
                var self = this;
                var params = this.getOnlineData();
                params.shift_id = shiftId;
                
                var deferred = $.Deferred();
                onlineResource().setPush(true).createShift(params,deferred, "open_shift_after");
                deferred.done(function (response) {
                    //Event.dispatch('sync_offline_shift_after',[{'action':params,'response':response}]);
                    self.clearInput();

                });
            },

            closeForm: function () {
                $(".popup-for-right").hide();
                $(".popup-for-right").removeClass('fade-in');
                $(".wrap-backover").hide();
                $('.notification-bell').show();
                $('#c-button--push-left').show();
            },

            floatAmountChange: function (data,event) {
                this.float_amount(priceHelper.toNumber(event.target.value));
            },

            //clear input value
            clearInput: function () {
                this.float_amount('');
                this.opened_note('');
            }
        });
    }
);
