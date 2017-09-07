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
        'uiComponent',
        'Magestore_Webpos/js/model/customer/customer-factory',
        'Magestore_Webpos/js/model/directory/country',
        'Magestore_Webpos/js/model/customer/current-customer',
        'mage/translate',
        'Magestore_Webpos/js/model/event-manager',
        'Magestore_Webpos/js/region-updater',
        "mage/validation",
        
    ],
    function ($, ko, ViewManager, Component, CustomerFactory, countryModel, currentCustomer, Translate) {
        "use strict";
        return Component.extend({
            /* Ko observable for address input*/
            firstName: ko.observable(''),
            lastName: ko.observable(''),
            company: ko.observable(''),
            phone: ko.observable(''),
            street1: ko.observable(''),
            street2: ko.observable(''),
            country: ko.observable(''),
            region: ko.observable(''),
            region_id: ko.observable(0),
            city: ko.observable(''),
            zipcode: ko.observable(''),
            vatId: ko.observable(''),
            countryArray: ko.observableArray(window.webposConfig.country),
            addressTitle: ko.observable(Translate('New Address')),
            currentEditAddressType: ko.observable(null),
            /* End Observable*/
            
            /* Selector for control UI*/
            editCustomerForm: $('#form-edit-customer'),
            addAddressForm: $('#form-customer-add-address-checkout'),
            /* End selector for control UI*/

            /* Add address template*/
            defaults: {
                template: 'Magestore_Webpos/checkout/customer/add-address'
            },

            /* Cancel Address*/
            cancelAddress: function () {
                this.hideAddressForm();
            },

            /* Auto run when initialize*/
            initialize: function () {
                this.editCustomer = ViewManager.getSingleton('view/checkout/customer/edit-customer');
                this.editCustomer.addAddress(this);
                this._super();
            },

            /* Save Address */
            saveAddress: function () {
                var self = this;
                var customerDeferred;
                var newAddressData =  self.getAddressData();
                var currentCustomerData = currentCustomer.data();
                if (this.validateAddressForm()) {
                    if (ViewManager.getSingleton('view/checkout/customer/edit-customer').currentEditAddressId()) {
                        var addressIndex = -1;
                        var currentEditAddressId = ViewManager.getSingleton('view/checkout/customer/edit-customer').currentEditAddressId();
                        var allAddress = ViewManager.getSingleton('view/checkout/customer/edit-customer').addressArray();
                        $.each(allAddress, function (index, value) {
                            if (value.id == currentEditAddressId) {
                                addressIndex = index;
                                var addressData = self.getAddressData();
                                addressData.id = value.id;
                                allAddress[index] = addressData;
                            }
                        });
                        currentCustomerData.addresses = allAddress;
                    } else {
                        var currentAddress = currentCustomerData.addresses;
                        if (currentAddress instanceof Array) {
                            currentAddress.push(newAddressData);
                        } else {
                            currentAddress = [];
                            currentAddress.push(newAddressData);
                        }
                        ViewManager.getSingleton('view/checkout/customer/edit-customer').addressArray(currentAddress);
                        currentCustomerData.addresses = currentAddress;
                    }
                    customerDeferred = CustomerFactory.get().setData(currentCustomerData).setPush(false).save();
                    customerDeferred.done(function (data) {
                        currentCustomer.setData(data);
                        ViewManager.getSingleton('view/checkout/customer/edit-customer').addressArray(currentCustomerData.addresses);
                        ViewManager.getSingleton('view/checkout/customer/edit-customer').showBillingPreview();
                        ViewManager.getSingleton('view/checkout/customer/edit-customer').showShippingPreview();
                    });

                    this.hideAddressForm();
                }
            },

            /* Hide Address */
            hideAddressForm: function () {
                this.editCustomerForm.removeClass('fade');
                this.editCustomerForm.addClass('fade-in');
                this.editCustomerForm.addClass('show');
                this.addAddressForm.removeClass('fade-in');
                this.addAddressForm.removeClass('show');
                this.addAddressForm.addClass('fade');
                this.addressTitle(Translate('New Address'));
                ViewManager.getSingleton('view/checkout/customer/edit-customer').currentEditAddressId(false);
                this.resetAddressForm();
            },

            /* reset address form */
            resetAddressForm: function () {
                this.firstName('');
                this.lastName('');
                this.company('');
                this.phone('');
                this.street1('');
                this.street2('');
                this.country('');
                this.region('');
                this.region_id('');
                this.city('');
                this.zipcode('');
                this.vatId('');
                this.currentEditAddressType(null);
                $('#form-customer-add-address-checkout').find('#region').val('');
            },

            /* Validate Add Address Form */
            validateAddressForm: function () {
                var form = '#form-customer-add-address-checkout';
                return $(form).validation() && $(form).validation('isValid');
            },

            /* Get Address Data Form*/
            getAddressData: function () {
                var data = {};
                var self = this;
                data.id = 'nsync' + Date.now();
                data.firstname = this.firstName();
                data.lastname = this.lastName();
                data.company = this.company();
                data.telephone = this.phone();
                data.street = [this.street1(), this.street2()];
                data.country_id = this.country();

                var regionIdAddAddress = $('#form-customer-add-address-checkout').find('#region_id');
                if (regionIdAddAddress.is(':visible')) {
                    var selected = regionIdAddAddress.find(":selected");
                    var regionCode = selected.data('region-code');
                    var region = selected.html();
                    data.region = {
                        region_id: self.region_id(),
                        region_code : regionCode,
                        region : region
                    };
                    data.region_id = self.region_id();
                } else {
                    data.region = {
                        region_id: 0,
                        region_code : self.region(),
                        region : self.region()
                    };
                    data.region_id = 0;
                }
                
                data.city = self.city();
                data.postcode = self.zipcode();
                data.vatId = self.vatId();
                return data;
            },

            /* Render select for region */
            _renderSelectOption: function (selectElement, key, value) {
                selectElement.append($.proxy(function () {
                    var name = value.name.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, '\\$&'),
                        tmplData,
                        tmpl;

                    if (value.code && $(name).is('span')) {
                        key = value.code;
                        value.name = $(name).text();
                    }

                    tmplData = {
                        value: key,
                        title: value.name,
                        isSelected: false,
                        code: value.code
                    };

                    tmpl = this.regionTmpl({
                        data: tmplData
                    });

                    return $(tmpl);
                }, this));
            },

            /* Remove select options for region */
            _removeSelectOptions: function (selectElement) {
                selectElement.find('option').each(function (index) {
                    if ($(this).val()) {
                        $(this).remove();
                    }
                });
            }
        });
    }
);
