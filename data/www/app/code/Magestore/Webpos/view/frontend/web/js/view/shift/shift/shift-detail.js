/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [ 'jquery',
        'ko',
        'uiComponent',
        'Magestore_Webpos/js/helper/datetime',
        'Magestore_Webpos/js/helper/staff'
    ],
    function ($, ko, Component, datetimeHelper, staffHelper) {
        "use strict";

        return Component.extend({
            shiftData: ko.observable({}),
            shiftOpenedAt: ko.observable(''),
            isClosedShift: ko.observable(false),
            noSalesSummary: ko.observable('main-content'),

            defaults: {
                template: 'Magestore_Webpos/shift/shift/shift-detail',
            },
            canMakeAdjustment: ko.pureComputed(function(){
                return (staffHelper.isHavePermission('Magestore_Webpos::manage_shift_adjustment'))?true:false;
            }),
            initialize: function () {
                this._super();
            },

            setShiftData: function(data){
                this.shiftData(data);
                this.shiftOpenedAt(datetimeHelper.getFullDate(data.opened_at));
                if (data.status == 1){
                    this.isClosedShift(true);
                }
                else {
                    this.isClosedShift(false);
                }
                if(data.sale_summary.length == 0){
                    this.noSalesSummary("no-sales-summary main-content");
                }
                else {
                    this.noSalesSummary("main-content");
                }
            },
            
            afterClosedShift: function () {
                this.isClosedShift(true);
            },

            afterRenderCashAdjustmentButton: function () {
                $('.footer-shift .btn-make-adjustment').click(function (event) {
                    //var ptop = (event.pageY/2) - 785;
                    var ptop = 150;
                    $("#popup-make-adjustment").addClass('fade-in');
                    $("#popup-make-adjustment").css({top: ptop + 'px'}).fadeIn(350);
                    $(".wrap-backover").show();
                    $('.notification-bell').hide();
                    $('#c-button--push-left').hide();
                });

                $('.wrap-backover').click(function () {
                    $(".popup-for-right").hide();
                    $(".popup-for-right").removeClass('fade-in');
                    $(".wrap-backover").hide();
                    $('.notification-bell').show();
                    $('#c-button--push-left').show();
                });
            },

            afterRenderCloseButton: function () {
                $('.footer-shift .btn-close-shift').click(function (event) {
                    //var ptop = (event.pageY/2) - 185;
                    var ptop = 150;
                    $("#popup-close-shift").addClass('fade-in');
                    $("#popup-close-shift").css({ top: ptop + 'px'}).fadeIn(350);
                    $(".wrap-backover").show();
                    $('.notification-bell').hide();
                    $('#c-button--push-left').hide();
                });

                $('.wrap-backover').click(function () {
                    $(".popup-for-right").hide();
                    $(".popup-for-right").removeClass('fade-in');
                    $(".wrap-backover").hide();
                    $('.notification-bell').show();
                    $('#c-button--push-left').show();
                });
            },

            afterRenderZReportButton: function () {
                $('.footer-shift .btn-print').click(function () {
                    var ptop = 10;
                    $("#print-shift-popup").addClass('fade-in');
                    $("#print-shift-popup").css({ top: ptop + 'px'});
                    $(".wrap-backover").show();
                    $('.notification-bell').hide();
                    $('#c-button--push-left').hide();
                });

                $('.wrap-backover').click(function () {
                    $(".popup-for-right").hide();
                    $(".popup-for-right").removeClass('fade-in');
                    $(".wrap-backover").hide();
                    $('.notification-bell').show();
                    $('#c-button--push-left').show();   
                });
            }

        });
    }
);
