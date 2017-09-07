/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'require',
        'jquery',
        'ko',
        'Magestore_Webpos/js/view/layout',
        'Magestore_Webpos/js/view/base/list/collection-list',
        'Magestore_Webpos/js/model/checkout/checkout',
        'Magestore_Webpos/js/model/checkout/payment-popup',
        'Magestore_Webpos/js/view/checkout/checkout/renderer/payment-factory',
    ],
    function (require, $, ko, ViewManager, colGrid, CheckoutModel, Payment, RenderPaymentFactory) {
        "use strict";
        return colGrid.extend({
            items: ko.observableArray([]),
            columns: ko.observableArray([]),
            defaults: {
                template: 'Magestore_Webpos/checkout/checkout/payment_popup',
            },
            initialize: function () {
                this.isShowHeader = true;
                this.model = Payment().setMode('offline');
                this._super();
                this._render();
            },
            _prepareColumns: function () {
                this.addColumn({headerText: "Title", rowText: "title", renderer: RenderPaymentFactory.get()});
            },
            _prepareCollection: function () {
                this.filterAttribute = 'code';
                // if(this.collection == null) {
                    this.collection = this.model.getCollection()
                        .addFieldToFilter('type', '0', 'eq')
                        .addFieldToFilter('code', this.getSelectPaymentCodes(), 'nin')
                    ;
                // }
            },
            setPaymentMethod: function (data, event) {
                var viewManager = require('Magestore_Webpos/js/view/layout');
                if(data.type == '0'){
                    viewManager.getSingleton('view/checkout/checkout/payment_selected').addPayment(data);
                    if($('#payment_selected') !== undefined){
                        $('#payment_selected').show();
                    }
                    if($('#payment_creditcard') !== undefined){
                        $('#payment_creditcard').hide();
                    }
                    if($('#payment_list') !== undefined){
                        $('#payment_list').hide();
                    }
                }
            },
            getSelectPaymentCodes: function () {
                var selectdCodes = [];
                ko.utils.arrayForEach(CheckoutModel.selectedPayments(), function (item) {
                    selectdCodes.push(item.code);
                });
                return selectdCodes;
            },
            checkPaymentCollection: function () {
                if(this.items().length > 0){
                    return false;
                }
                return true;
            },
        });
    }
);
