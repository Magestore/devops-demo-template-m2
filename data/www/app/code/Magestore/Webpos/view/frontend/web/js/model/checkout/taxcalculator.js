/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

require([
    'Magestore_Webpos/js/model/customer/group',
    ]);
    
define(
    [
        'jquery',
        'ko',
        'Magestore_Webpos/js/model/abstract',
        'Magestore_Webpos/js/model/checkout/taxrule-factory',
        'Magestore_Webpos/js/model/checkout/taxrate-factory',
        'Magestore_Webpos/js/model/customer/group-factory',
    ],
    function ($, ko, modelAbstract, TaxRuleFactory, TaxRateFactory, CustomerGroupFactory) {
        "use strict";
        return modelAbstract.extend({
            rules: ko.observableArray([]),
            rates: ko.observableArray([]),
            groups: ko.observableArray([]),
            initialize: function () {
                this._super();
            },
            initData: function (){
                var self = this;
                if(self.rules().length <= 0){
                    self.getRules();
                }
                if(self.rates().length <= 0){
                    self.getRates();
                }
                if(self.groups().length <= 0){
                    self.getGroups();
                }
            },
            reInitData: function(){
                var self = this;
                self.getRules();
                self.getRates();
                self.getGroups();
            },
            getRules: function(){
                var self = this;
                self.rules([]);
                var deferred = TaxRuleFactory.get().getCollection().load();
                deferred.done(function (response) {
                    if(response.items && response.items.length > 0){
                        $.each(response.items, function(){
                            var rule = {
                                id:this.id,
                                customer_tc_ids:this.customer_tax_class_ids,
                                product_tc_ids:this.product_tax_class_ids,
                                rates_ids:this.tax_rate_ids,
                                priority:this.priority
                            };
                            self.rules.push(rule);
                        });
                    }
                });
            },
            getRates: function(){
                var self = this;
                self.rates([]);
                var deferred = TaxRateFactory.get().getCollection().load();
                deferred.done(function (response) {
                    if(response.items && response.items.length > 0){
                        $.each(response.items, function(){
                            var rate = {
                                id:this.id,
                                country:this.tax_country_id,
                                region_id:this.tax_region_id,
                                postcode:this.tax_postcode,
                                rate:this.rate
                            };
                            self.rates.push(rate);
                        });
                    }
                });
            },
            getGroups: function(){
                var self = this;
                self.groups([]);
                var deferred = CustomerGroupFactory.get().getCollection().load();
                deferred.done(function (response) {
                    if(response.items && response.items.length > 0){
                        $.each(response.items, function(){
                            var group = {
                                id:this.id,
                                tax_class_id:this.tax_class_id
                            };
                            self.groups.push(group);
                        });
                    }
                });
            },
            getProductTaxRate: function(productTaxClassId, customerGroup, billingAddress){
                var self = this;
                var customerTaxClassId = self.getCustomerTaxClassId(customerGroup);
                var rate = self.getRate(productTaxClassId,customerTaxClassId, billingAddress);
                return rate;
            },
            getCustomerTaxClassId: function(customerGroup){
                var customerTaxClassId = "";
                if(this.groups() && this.groups().length > 0){
                    $.each(this.groups(), function(){
                        if(this.id == customerGroup){
                            customerTaxClassId = this.tax_class_id;
                        }
                    });
                }
                return customerTaxClassId;
            },
            getRate: function(productTaxClassId, customerTaxClassId, address){
                var self = this;
                var rateIds = "";
                var rates = [];
                var tempRates = [];
                if(customerTaxClassId != ""){
                    if(this.rules() && this.rules().length > 0){
                        $.each(this.rules(), function(){
                            if(
                                $.inArray(parseInt(customerTaxClassId), this.customer_tc_ids) !== -1  
                                && $.inArray(parseInt(productTaxClassId), this.product_tc_ids) !== -1
                            ){
                                rateIds = this.rates_ids;
                                var tempRate = self.getRateValue(rateIds, address);
                                if(tempRate > 0){
                                    var priority = this.priority;
                                    var isNew = true;
                                    $.each(tempRates, function(index, rate){
                                        if(this.priority == priority){
                                            isNew = false;
                                            rate.rate += parseFloat(tempRate);
                                            tempRates[index] = rate;
                                        }
                                    });
                                    if(isNew == true){
                                        tempRates.push({priority:priority, rate: parseFloat(tempRate)});
                                    }
                                }
                            }
                        });
                    }
                }else{
                    if(this.rules() && this.rules().length > 0){
                        rateIds = [];
                        $.each(this.rules(), function(){
                            if(
                                $.inArray(parseInt(productTaxClassId), this.product_tc_ids) !== -1
                            ){
                                rateIds = this.rates_ids;
                                var tempRate = self.getRateValue(rateIds, address);
                                if(tempRate > 0){
                                    var priority = this.priority;
                                    var isNew = true;
                                    $.each(tempRates, function(index, rate){
                                        if(this.priority == priority){
                                            isNew = false;
                                            rate.rate += parseFloat(tempRate);
                                            tempRates[index] = rate;
                                        }
                                    });
                                    if(isNew == true){
                                        tempRates.push({priority:priority, rate: parseFloat(tempRate)});
                                    }
                                }
                            }
                        });
                    }
                }
                tempRates.sort(self.sortRulesRate);
                $.each(tempRates, function(){
                    rates.push(this.rate);
                });
                return rates;
            },
            getRateValue: function(rateIds, address){
                var self = this;
                var rate = 0;
                if(rateIds != ""){
                    if(rateIds && rateIds.length > 0){
                        $.each(rateIds, function(){
                            var rateId = this;
                            if(self.rates() && self.rates().length > 0){
                                $.each(self.rates(), function(){
                                    if(this.id == rateId){
                                        if(
                                            (this.country == "*" || this.country == address.country_id)&&
                                            (this.region_id == "*" || this.region_id == "0" || this.region_id == 0 || this.region_id == address.region_id)&&
                                            (this.postcode == "*" || this.postcode == address.postcode)
                                        ){
                                            if(this.rate > rate){
                                                rate = this.rate;
                                            }
                                        }
                                    }
                                });
                            }
                        });
                    }
                }
                return rate;
            },
            getOriginRate: function(productTaxClassId, customerGroup){
                var self = this;
                var customerTaxClassId = self.getCustomerTaxClassId(customerGroup);
                var address = {
                    country_id:window.webposConfig['shipping/origin/country_id'],
                    postcode:window.webposConfig['shipping/origin/postcode'],
                    region_id:(window.webposConfig['shipping/origin/region_id'])?window.webposConfig['shipping/origin/region_id']:0
                };
                var rateIds = "";
                var rates = [];
                var tempRates = [];
                if(customerTaxClassId != ""){
                    if(this.rules() && this.rules().length > 0){
                        $.each(this.rules(), function(){
                            if(
                                $.inArray(parseInt(customerTaxClassId), this.customer_tc_ids) !== -1
                                && $.inArray(parseInt(productTaxClassId), this.product_tc_ids) !== -1
                            ){
                                rateIds = this.rates_ids;
                                var tempRate = self.getRateValue(rateIds, address);
                                if(tempRate > 0){
                                    var priority = this.priority;
                                    var isNew = true;
                                    $.each(tempRates, function(index, rate){
                                        if(this.priority == priority){
                                            isNew = false;
                                            rate.rate += parseFloat(tempRate);
                                            tempRates[index] = rate;
                                        }
                                    });
                                    if(isNew == true){
                                        tempRates.push({priority:priority, rate: parseFloat(tempRate)});
                                    }
                                }
                            }
                        });
                    }
                }else{
                    if(this.rules() && this.rules().length > 0){
                        rateIds = [];
                        $.each(this.rules(), function(){
                            if(
                                $.inArray(parseInt(productTaxClassId), this.product_tc_ids) !== -1
                            ){
                                rateIds = this.rates_ids;
                                var tempRate = self.getRateValue(rateIds, address);
                                if(tempRate > 0){
                                    var priority = this.priority;
                                    var isNew = true;
                                    $.each(tempRates, function(index, rate){
                                        if(this.priority == priority){
                                            isNew = false;
                                            rate.rate += parseFloat(tempRate);
                                            tempRates[index] = rate;
                                        }
                                    });
                                    if(isNew == true){
                                        tempRates.push({priority:priority, rate: parseFloat(tempRate)});
                                    }
                                }
                            }
                        });
                    }
                }
                tempRates.sort(self.sortRulesRate);
                $.each(tempRates, function(){
                    rates.push(this.rate);
                });
                return rates;
            },
            sortRulesRate: function(ruleA, ruleB){
                var aPriority = parseFloat(ruleA.priority);
                var bPriority = parseFloat(ruleB.priority);
                return ((aPriority < bPriority) ? -1 : ((aPriority > bPriority) ? 1 : 0));
            }

        });
    }
);