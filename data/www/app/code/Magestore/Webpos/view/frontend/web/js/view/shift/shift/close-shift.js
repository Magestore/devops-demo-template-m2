/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magestore_Webpos/js/model/shift/cash-transaction',
        'Magestore_Webpos/js/model/shift/shift',
        'Magestore_Webpos/js/helper/price',
        'Magestore_Webpos/js/helper/datetime',
        'Magestore_Webpos/js/action/notification/add-notification',
        'Magestore_Webpos/js/model/event-manager',
        'Magestore_Webpos/js/model/resource-model/magento-rest/shift/shift'
    ],
    function ($, ko, Component, cashTransactionModel, shift, priceHelper, datetimeHelper, notification, Event, shiftOnlineResource) {
        "use strict";

        return Component.extend({

            shiftData: ko.observable({}),
            balance: ko.observable(''),
            closed_amount: ko.observable(''),
            closed_note: ko.observable(''),
            cash_left: ko.observable(''),
            closedAmountFormatted: ko.observable(''),
            cashLeftFormatted: ko.observable(''),
            keypressWaiting: '',
            cashLeftErrorMessage: ko.observable(''),
            staffId: ko.observable(window.webposConfig.staffId),
            staffName: ko.observable(window.webposConfig.staffName),
            saveOfflineCompleted: ko.observable(0),

            defaults: {
                template: 'Magestore_Webpos/shift/shift/close-shift',
            },

            initialize: function () {
                this._super();

                //recalculate closedAmountFormatted when closed_amount changed.
                this.closedAmountFormatted = ko.pureComputed(function () {
                    this.validateInputAmount();
                    return priceHelper.formatPrice(this.closed_amount());
                }, this);

                //recalculate cashLeftFormatted when cash_left changed.
                this.cashLeftFormatted = ko.pureComputed(function () {
                    this.validateInputAmount();
                    return priceHelper.formatPrice(this.cash_left());
                }, this);

            },

            /**
             * check if cash_left is less than closed amount or not
             * @returns {boolean}
             */
            validateInputAmount: function () {
                if (this.cash_left() > this.closed_amount()) {
                    this.cashLeftErrorMessage("Cash left must be less than the Closed amount");
                    return false;
                }
                else {
                    this.cashLeftErrorMessage("");
                    return true;
                }
            },

            //set all cash transaction data of the selected shift to Items
            //each transaction is an Item
            setData: function (data) {
                this.setItems(data);
            },

            //set all information of the selected shift to ShiftData
            //call this funciton from shift-listing
            setShiftData: function (data) {
                this.shiftData(data);
                this.initData();
            },

            /* update value of the estimated cash in the cash drawer*/
            initData: function () {
                this.balance(this.shiftData().balance);
                this.balance(priceHelper.formatPrice(this.balance()));
            },

            //get data from the form and call to CashTransaction model then save to database
            closeShift: function () {
                if (!this.validateInputAmount()) {
                    return;
                }
                this.saveOfflineCompleted(0);
                //this.saveOfflineLastTransaction();
                this.saveOfflineShift();
                this.closeForm();
            },

            /**
             * do some additional task when everything is completed.
             */
            closeCompleted: function () {
                //set the current open shift to null
                window.webposConfig.shiftId = '';
                //clear input value
                this.cash_left('');
                this.closed_amount('');
                this.closed_note('');

            },

            /**
             * prepare data to update shift
             * @returns {{}}
             */
            getShiftDataOnline: function () {
                var postData = {};
                var removedValue = priceHelper.toPositiveNumber(this.closed_amount()) - priceHelper.toPositiveNumber(this.cash_left());
                postData.base_currency_code = window.webposConfig.baseCurrencyCode;
                postData.shift_currency_code = window.webposConfig.currentCurrencyCode;
                postData.shift_id = this.shiftData().shift_id;
                postData.entity_id = this.shiftData().entity_id;
                postData.staff_id = priceHelper.toPositiveNumber(this.shiftData().staff_id);
                postData.location_id = priceHelper.toPositiveNumber(this.shiftData().location_id);
                postData.float_amount = priceHelper.toPositiveNumber(this.shiftData().float_amount);
                postData.base_float_amount = priceHelper.currencyConvert(postData.float_amount, window.webposConfig.currentCurrencyCode, window.webposConfig.baseCurrencyCode);
                postData.closed_amount = priceHelper.toPositiveNumber(this.closed_amount());
                postData.base_closed_amount = priceHelper.currencyConvert(postData.closed_amount, window.webposConfig.currentCurrencyCode, window.webposConfig.baseCurrencyCode);
                postData.closed_at = datetimeHelper.getBaseSqlDatetime();
                postData.opened_at = this.shiftData().opened_at;
                postData.closed_note = this.closed_note();
                postData.cash_left = priceHelper.toPositiveNumber(this.cash_left());
                postData.base_cash_left = priceHelper.currencyConvert(postData.cash_left, window.webposConfig.currentCurrencyCode, window.webposConfig.baseCurrencyCode);
                postData.status = 1;
                postData.total_sales = priceHelper.toPositiveNumber(this.shiftData().total_sales);
                postData.base_total_sales = priceHelper.currencyConvert(postData.total_sales, window.webposConfig.currentCurrencyCode, window.webposConfig.baseCurrencyCode);
                postData.balance = priceHelper.toPositiveNumber(this.cash_left());
                postData.base_balance = priceHelper.currencyConvert(postData.balance, window.webposConfig.currentCurrencyCode, window.webposConfig.baseCurrencyCode);
                postData.opened_note = this.shiftData().opened_note;
                postData.cash_added = this.shiftData().cash_added;
                postData.base_cash_added = priceHelper.currencyConvert(postData.cash_added, window.webposConfig.currentCurrencyCode, window.webposConfig.baseCurrencyCode);
                postData.cash_removed = priceHelper.toPositiveNumber(this.shiftData().cash_removed) + removedValue;
                postData.base_cash_removed = priceHelper.currencyConvert(postData.cash_removed, window.webposConfig.currentCurrencyCode, window.webposConfig.baseCurrencyCode);
                postData.cash_sale = priceHelper.toPositiveNumber(this.shiftData().cash_sale);
                postData.base_cash_sale = priceHelper.currencyConvert(postData.cash_sale, window.webposConfig.currentCurrencyCode, window.webposConfig.baseCurrencyCode);

                return postData;
            },

            /**
             *
             * @returns {*|{}}
             */
            getShiftDataOffline: function () {
                var postData = this.getShiftDataOnline();
                var lastTransactionData = this.getLastTransactionData();

                postData.sale_summary = this.shiftData().sale_summary;
                postData.cash_transaction = this.shiftData().cash_transaction;
                if (lastTransactionData.value > 0) {
                    postData.cash_transaction.push(lastTransactionData);
                }
                postData.zreport_sales_summary = this.shiftData().zreport_sales_summary;
                return postData;
            },

            /**
             *
             */
            saveOfflineShift: function () {
                var self = this;
                var shiftModel = shift();
                var postData = self.getShiftDataOffline();
                var deferred = shiftModel.setData(postData).setMode('offline').update();
                deferred.done(function (response) {
                    if (response) {
                        //show notification
                        //notification('Shift closed! Balance=' + priceHelper.formatPrice(postData.balance) + ', Total Sales='+ priceHelper.formatPrice(postData.total_sales), true, 'success', 'Notice');
                        Event.dispatch('after_closed_shift', response);
                        //self.syncTransaction();
                        self.syncShift();
                    }
                });
            },

            syncShift: function () {
                var self = this;
                var postData = this.getShiftDataOnline();
                var deferred = $.Deferred();
                shiftOnlineResource().setPush(true).createShift(postData, deferred, "sync_offline_shift_after");
                deferred.always(function (response) {
                    self.closeCompleted();
                });
            },

            /**
             * prepare data for the last transaction: remove cash from cash drawer before closing a shift.
             * @returns {{shift_id: (*|exports.indexes.shift_id|{unique}|schema.shift.indexes.shift_id), location_id: (*|exports.indexes.location_id|{unique}), value: number, base_value: *, note: string, balance: *, base_balance: *, type: string, base_currency_code: *, transaction_currency_code: *, created_at: string}}
             */
            getLastTransactionData: function () {
                var value = priceHelper.toPositiveNumber(this.closed_amount()) - priceHelper.toPositiveNumber(this.cash_left());
                var balance = priceHelper.toPositiveNumber(this.cash_left());

                var data = {
                    'shift_id': this.shiftData().shift_id,
                    'location_id': priceHelper.toPositiveNumber(this.shiftData().location_id),
                    'value': value,
                    'base_value': priceHelper.currencyConvert(value, window.webposConfig.currentCurrencyCode, window.webposConfig.baseCurrencyCode),
                    'note': 'Remove cash when closed Shift',
                    'balance': balance,
                    'base_balance': priceHelper.currencyConvert(balance, window.webposConfig.currentCurrencyCode, window.webposConfig.baseCurrencyCode),
                    'type': 'remove',
                    'base_currency_code': window.webposConfig.baseCurrencyCode,
                    'transaction_currency_code': window.webposConfig.currentCurrencyCode,
                    'created_at': datetimeHelper.getBaseSqlDatetime()
                };

                return data;
            },

            closeForm: function () {
                $(".popup-for-right").hide();
                $(".popup-for-right").removeClass('fade-in');
                $(".wrap-backover").hide();
                $('.notification-bell').show();
                $('#c-button--push-left').show();
            },

            cashLeftChange: function (data, event) {
                this.cash_left(priceHelper.toNumber(event.target.value));

            },

            closedAmountChange: function (data, event) {
                this.closed_amount(priceHelper.toNumber(event.target.value));
            }
        });
    }
);