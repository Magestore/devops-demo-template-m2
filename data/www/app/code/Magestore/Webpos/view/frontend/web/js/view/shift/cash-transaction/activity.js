/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [ 'jquery',
        'ko',
        'Magestore_Webpos/js/view/base/grid/abstract',
        'Magestore_Webpos/js/helper/datetime',
        'Magestore_Webpos/js/helper/price',


    ],
    function ($, ko, listAbstract, datetimeHelper, priceHelper) {
        "use strict";

        return listAbstract.extend({
            shiftData:  ko.observable({}),
            saleSummaryData: ko.observable({}),
            items: ko.observableArray([]),
            columns: ko.observableArray([]),
            staffName: ko.observable(window.webposConfig.staffName),

            defaults: {
                template: 'Magestore_Webpos/shift/cash-transaction/activity',
            },

            initialize: function () {
                this._super();
                this._render();
            },


            setData: function(data){

                this.setItems(data);
            },

            setShiftData: function(data){

                this.shiftData(data);
            },
            
            getTransactionSymbol: function (type, value) {
                if(!value){
                    return "";
                }
                if(parseInt(value)==0){
                    return "";
                }


                var symbol = "+";
                if(type == "remove"){
                    symbol = "-";
                }
                return symbol;
            },

            steveFormatPrice: function (amount) {
                amount = parseFloat(amount);
                return priceHelper.formatPrice(amount);

            },
            /**
             * return a date time with format: Thursday 4 May, 2016 15:26PM
             * @param dateString
             * @returns {string}
             */
            getFullDatetime: function (dateString) {
                var currentTime = datetimeHelper.stringToCurrentTime(dateString);
                return datetimeHelper.getFullDatetime(currentTime);
            },
        });
    }
);
