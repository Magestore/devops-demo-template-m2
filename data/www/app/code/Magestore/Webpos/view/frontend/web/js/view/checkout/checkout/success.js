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
        'Magestore_Webpos/js/view/layout',
        'Magestore_Webpos/js/model/checkout/checkout',
        'Magestore_Webpos/js/helper/price',
        'Magestore_Webpos/js/helper/alert',
        'Magestore_Webpos/js/action/notification/add-notification',
        'mage/translate',
        'Magestore_Webpos/js/model/event-manager'
    ],
    function ($,ko, Component, ViewManager, CheckoutModel,  PriceHelper, Alert, AddNoti, Translate, Event) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magestore_Webpos/checkout/checkout/success'
            },
            successMessage: ko.observable(),
            successImageUrl: ko.observable(),
            printWindow: ko.observable(),
            initialize: function () {
                this._super();
                this.orderData = ko.pureComputed(function(){
                    var result = CheckoutModel.createOrderResult();
                    return (result && result.increment_id)?result:false;
                });
                this.createdOrder = ko.pureComputed(function(){
                    var result = CheckoutModel.createOrderResult();
                    return (result && result.increment_id)?true:false;
                });
                var self = this;
                Event.observer('webpos_order_save_after',function(event,data){
                    if(data && data.increment_id){
                        ViewManager.getSingleton('view/checkout/checkout/receipt').initDefaultData();
                        if(ViewManager.getSingleton('view/checkout/checkout/receipt').isAutoPrint()){
                            self.printReceipt();
                        }
                    }
                });
            },
            getOrderData: function(key){
                var orderData = this.orderData();
                var data = "";
                if(orderData){
                    data = orderData;
                    if(key){
                        if(typeof data[key] != "undefined"){
                            data = data[key];
                        }else{
                            data = "";
                        }
                    }
                }
                return data;
            },
            getCustomerEmail: function(){
                return this.getOrderData('customer_email');
            },
            getGrandTotal: function(){
                return PriceHelper.formatPrice(this.getOrderData('grand_total'));
            },
            getOrderIdMessage: function(){
                return "#"+this.getOrderData('increment_id');
            },
            printReceipt: function(){
                ViewManager.getSingleton('view/checkout/checkout/receipt').initDefaultData();
                var print_window = window.open('', 'print_offline', 'status=1,width=500,height=700');
                var html = ViewManager.getSingleton('view/checkout/checkout/receipt').toHtml();
                if(print_window){
                    this.printWindow(print_window);
                    print_window.document.write(html);
                    print_window.print();
                }else{
                    AddNoti(Translate("Your browser has blocked the automatic popup, please change your browser setting or print the receipt manually"), true, "warning", Translate('Message'));
                }
            },
            startNewOrder: function(){
                ViewManager.getSingleton('view/checkout/checkout/payment_selected').initPayments();
                ViewManager.getSingleton('view/checkout/cart').switchToCart();
                ViewManager.getSingleton('view/checkout/cart').emptyCart();
                CheckoutModel.resetCheckoutData();
                if(this.printWindow()){
                    this.printWindow().close();
                }
            },
            sendEmail: function(){
                if(this.getCustomerEmail()){
                    CheckoutModel.sendEmail(this.getCustomerEmail(),this.getOrderData('increment_id'));
                    AddNoti(Translate("An email has been sent for this order"), true, "success", Translate('Message'));
                }else{
                    Alert({
                        priority:"warning",
                        title: "Warning",
                        message: "Please enter the email address"
                    });
                }
            },
            saveEmail: function(data,event){
                if(!this.orderData()){
                    this.orderData({});
                }
                this.orderData().customer_email = event.target.value;
            }
        });
    }
);