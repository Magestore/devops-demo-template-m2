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
        "mage/translate",
        "mage/validation"
    ],
    function ($, ko, Component, Translate) {
        "use strict";
        return Component.extend({
            countryArray: ko.observableArray(window.webposConfig.country),
            addShipping: ko.observable(),

            /* Binding billing address information in create customer form*/
            isShowBillingSummaryForm: ko.observable(false),
            firstNameBilling: ko.observable(''),
            lastNameBilling: ko.observable(''),
            companyBilling: ko.observable(''),
            phoneBilling: ko.observable(''),
            street1Billing: ko.observable(''),
            street2Billing: ko.observable(''),
            countryBilling: ko.observable(''),
            regionBilling: ko.observable(''),
            regionIdBilling: ko.observable(0),
            cityBilling: ko.observable(''),
            zipcodeBilling: ko.observable(''),
            vatBilling: ko.observable(''),
            billingAddressTitle: ko.observable(Translate('Add Billing Address')),
            leftButton: ko.observable(Translate('Cancel')),
            /* End binding*/

            regionObjectBilling: ko.observable(''),
            regionIdComputedBilling: '',

            /* Template for knockout js*/
            defaults: {
                template: 'Magestore_Webpos/checkout/customer/add-billing-address'
            },

            /* Hide billing address*/
            hideBillingAddress: function () {
                var formAddBillingAddress = $('#form-customer-add-billing-address-checkout');
                var formAddCustomerCheckout = $('#form-customer-add-customer-checkout');
                if (this.billingAddressTitle == Translate('Add Billing Address')) {
                    this.isShowBillingSummaryForm(false);
                } else {
                    this.isShowBillingSummaryForm(false);
                    this.resetFormInfo();
                }
                formAddBillingAddress.removeClass('fade-in');
                formAddBillingAddress.removeClass('show');
                formAddBillingAddress.addClass('fade');
                formAddCustomerCheckout.addClass('fade-in');
                formAddCustomerCheckout.addClass('show');
                formAddCustomerCheckout.removeClass('fade');
                $('.wrap-backover').show();
                $('.notification-bell').hide();
                $('#c-button--push-left').hide();
                
            },

            /* Save billing address*/
            saveBillingAddress: function () {
                var self = this;
                if (this.validateBillingAddressForm()) {
                    var addBillingForm =  $('#form-customer-add-billing-address-checkout');
                    var addCustomerForm = $('#form-customer-add-customer-checkout');
                    var regionIdBillingAddress = addBillingForm.find('#region_id');
                    if (regionIdBillingAddress.is(':visible')) {
                        var selected = regionIdBillingAddress.find(":selected");
                        var regionCode = selected.data('region-code');
                        var region = selected.html();
                        this.regionObjectBilling({
                            region_id: self.regionIdBilling(),
                            region_code : regionCode,
                            region : region
                        });
                        this.regionIdComputedBilling = self.regionIdBilling();
                    } else {
                        this.regionObjectBilling({
                            region_id: 0,
                            region_code : self.regionBilling(),
                            region : self.regionBilling()
                        });
                        self.regionIdBilling(0);
                    }
                    addBillingForm.removeClass('fade-in');
                    addBillingForm.removeClass('show');
                    addBillingForm.addClass('fade');
                    addCustomerForm.addClass('fade-in');
                    addCustomerForm.addClass('show');
                    addCustomerForm.removeClass('fade');
                    $('.wrap-backover').show();
                    $('.notification-bell').hide();
                    $('#c-button--push-left').hide();
                    this.isShowBillingSummaryForm(true);
                    self.addShipping.call().isSameBillingShipping(false);
                }
            },

            /* validate billing address form */
            validateBillingAddressForm: function () {
                var form = '#form-customer-add-billing-address-checkout';
                return $(form).validation({}) && $(form).validation('isValid');
            },

            /* Reset Form*/
            resetFormInfo: function () {
                this.firstNameBilling('');
                this.lastNameBilling('');
                this.companyBilling('');
                this.phoneBilling('');
                this.street1Billing('');
                this.street2Billing('');
                this.countryBilling('');
                this.regionBilling('');
                this.regionIdBilling('');
                this.cityBilling('');
                this.zipcodeBilling('');
                this.vatBilling('');
                this.regionIdComputedBilling = '';
                this.billingAddressTitle(Translate('Add Billing Address'));
                this.leftButton(Translate('Cancel'));
            }
        });
    }
);
