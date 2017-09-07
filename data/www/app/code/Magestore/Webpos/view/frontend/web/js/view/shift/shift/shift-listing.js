/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'ko',
        'Magestore_Webpos/js/view/layout',
        'Magestore_Webpos/js/view/base/list/collection-list',
        'Magestore_Webpos/js/model/shift/shift',
        'Magestore_Webpos/js/helper/price',
        'Magestore_Webpos/js/helper/shift',
        'Magestore_Webpos/js/helper/datetime',
        'Magestore_Webpos/js/model/staff/current-staff',
        'Magestore_Webpos/js/model/event-manager',
        
    ],
    function ($, ko, ViewManager, colGrid, shift, priceHelper, shiftHelper, datetimeHelper, CurrentStaff, Event) {
        "use strict";

        return colGrid.extend({
            items: ko.observableArray([]),
            columns: ko.observableArray([]),
            selectedId: ko.observable(null),
            canOpenShift: ko.observable(true),
            shiftListingData: ko.observable({}),
            staffId: ko.observable(window.webposConfig.staffId),
            hasNoShift: ko.observable(false),

            defaults: {
                template: 'Magestore_Webpos/shift/shift/shift-listing',
            },
            initialize: function () {
                this.listenMenuClickedEvent();
                this.isShowHeader = true;
                this._super();
                this._render();
            },
            _prepareCollection: function () {
                if (this.collection == null) {
                    this.collection = shift().getCollection();
                    this.collection.addFieldToFilter('staff_id', window.webposConfig.staffId, 'eq');
                }
            },
            loadItem: function (data, event) {
                this.initData(data);
                this.selectedId(data.shift_id);
            },

            getSelectedId: function () {
                return this.selectedId();
            },

            _prepareItems: function () {
                var deferred = $.Deferred();
                var self = this;
                this.getCollection().setOrder('opened_at', 'DESC').load(deferred);
                this.startLoading();
                deferred.done(function (data) {
                    self.finishLoading();
                    self.setItems(data.items);
                    if (data.total_count > 0) {
                        self.hasNoShift(false);
                        self.shiftListingData(data);
                        var checkOpen = shiftHelper.checkHasOpenShift(data.items);
                        if (checkOpen.hasOpen) {
                            self.canOpenShift(false);
                            window.webposConfig.shiftId = checkOpen.shiftId;
                        }
                        else {
                            window.webposConfig.shiftId = '';
                            self.canOpenShift(true);
                        }
                        self.initData(data.items[0]);
                        self.selectedId(data.items[0].shift_id);
                    }
                    else {
                        self.hasNoShift(true);
                    }

                });
            },

            getDateOnly: function (dateString) {
                var currentTime = datetimeHelper.stringToCurrentTime(dateString);
                var datetime = this.reFormatDateString(currentTime);
                return datetimeHelper.getWeekDay(currentTime) + " " + datetime.getDate() + " " + datetimeHelper.getMonthShortText(currentTime);
            },

            getTimeOnly: function (dateString) {
                var currentTime = datetimeHelper.stringToCurrentTime(dateString);
                var datetime = this.reFormatDateString(currentTime);
                return datetimeHelper.getTimeOfDay(datetime);
            },


            reFormatDateString: function (dateString) {
                var date = '';
                if (typeof dateString === 'string') {
                    date = new Date(dateString.split(' ').join('T'))
                } else {
                    date = new Date(dateString);
                }
                return date;
            },


            initData: function (data) {
                var viewManager = require('Magestore_Webpos/js/view/layout');
                viewManager.getSingleton('view/shift/sales-summary/sales-summary').setData(data.sale_summary);
                viewManager.getSingleton('view/shift/sales-summary/sales-summary').setShiftData(data);
                viewManager.getSingleton('view/shift/cash-transaction/activity').setData(data.cash_transaction);
                viewManager.getSingleton('view/shift/cash-transaction/activity').setShiftData(data);
                viewManager.getSingleton('view/shift/shift/shift-detail').setShiftData(data);
                viewManager.getSingleton('view/shift/cash-transaction/cash-adjustment').setShiftData(data);
                viewManager.getSingleton('view/shift/shift/close-shift').setShiftData(data);
                viewManager.getSingleton('view/shift/sales-summary/zreport').setShiftData(data);
            },

            afterRenderOpenButton: function () {
                $('#shift_container .icon-add .icon-iconPOS-add').click(function () {
                    $("#popup-open-shift").addClass('fade-in');
                    $(".wrap-backover").show();
                    $('.notification-bell').hide();
                    $('#c-button--push-left').hide();
                });
                $('.wrap-backover').click(function () {
                    $(".wrap-backover").hide();
                    $('.notification-bell').show();
                    $('#c-button--push-left').show();
                });
            },

            refreshData: function () {
                this.collection = shift().getCollection();
                this.collection.addFieldToFilter('staff_id', window.webposConfig.staffId, 'eq');
                this._prepareItems();
            },

            listenMenuClickedEvent: function () {
                var self = this;
                Event.observer('register_shift_show_container_after', function (event, eventData) {
                    self.refreshData();
                });
            },

            toNumber: function (amount) {
                return priceHelper.toNumber(amount);
            }
        });
    }
);