/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [ 'jquery',
        'ko',
        'Magestore_Webpos/js/view/base/grid/abstract',
        'Magestore_Webpos/js/helper/price',
    ],
    function ($, ko, listAbstract, priceHelper) {
        "use strict";

        return listAbstract.extend({
            shiftData: ko.observable({}),
            saleSummaryData: ko.observable({}),
            items: ko.observableArray([]),
            columns: ko.observableArray([]),
            total_sales: ko.observable(0),
            paymentMethodClass: ko.observable(null),
            priceFormatter: ko.observable(''),
            hasData: ko.observable(false),
            isNotSync: ko.observable(true),

            defaults: {
                template: 'Magestore_Webpos/shift/sales-summary/sales-summary',
            },

            initialize: function () {

                this._super();
                this._render();
            },

            setData: function(data){
                if(!data){
                    this.hasData(false);
                    return;
                }
                this.setItems(data);
                if (data.length == 0){
                   this.hasData(false);
                }
                else {
                    this.hasData(true);
                }
            },

            setShiftData: function(data){
                this.shiftData(data);
                this.total_sales(data.total_sales);
                this.checkSync();

            },
            generatePaymentCode: function (paymentMethod) {
                return "icon-iconPOS-payment-" + paymentMethod;
            },
            
            checkSync: function () {

                if(priceHelper.toPositiveNumber(this.shiftData().entity_id) > 0){
                    this.isNotSync(false);
                }
                else {
                    this.isNotSync(true);
                }
            },

            setSyncSuccessful: function () {
                this.isNotSync(false);
            }
        });
    }
);
