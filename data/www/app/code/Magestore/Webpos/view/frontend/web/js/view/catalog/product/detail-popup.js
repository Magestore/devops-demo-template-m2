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
        'Magestore_Webpos/js/view/catalog/product/type/configurable',
        'Magestore_Webpos/js/model/checkout/cart',
        'Magestore_Webpos/js/helper/price',
        'Magestore_Webpos/js/helper/alert',
        'Magestore_Webpos/js/model/catalog/product-factory',
        'Magestore_Webpos/js/model/inventory/stock-item-factory',
        'mage/translate',
        'Magestore_Webpos/js/helper/general',
        'mage/validation',
        'mage/validation/validation',
        
    ],
    function ($, ko, Component, configurable, CartModel, priceHelper, alertHelper, ProductFactory, StockItemFactory, $t, Helper) {
        "use strict";
        window.timeout = 0;
        return Component.extend({
            itemData: ko.observable({}),
            styleOfPopup: ko.observable('view_detail'),
            focusQtyInput: true,
            qtyAddToCart: ko.observable(1),
            defaultPriceAmount: ko.observable(),
            basePriceAmount: ko.observable(),
            configurableProductIdResult: ko.observable(),
            configurableOptionsResult: ko.observable(),
            configurableLabelResult: ko.observable(),
            groupedProductResult: ko.observableArray([]),
            bundleOptionsValueResult: ko.observableArray([]),
            bundleOptionsQtyResult: ko.observableArray([]),
            bundleChildsQtyResult: ko.observableArray([]),
            bundleOptionsLableResult: ko.observableArray([]),
            customOptionsValueResult: ko.observableArray([]),
            customOptionsLableResult: ko.observableArray([]),
            creditProductResult: ko.observableArray([]),
            creditValue: ko.observable(),
            bundleItem: ko.observable(),
            groupItem: ko.observable(),
            defaults: {
                template: 'Magestore_Webpos/catalog/product/detail-popup'
            },
            initialize: function () {
                this.resetSuperAttributeData();
                configurable.detailPopup(this);
                this._super();
            },
            getTypeId: function () {
                return this.itemData().type_id;
            },
            getQtyIncrement: function(){
                var self = this;
                var product = self.getProductData();
                return parseFloat(product.qty_increment);
            },
            isQtyDecimal: function(){
                var self = this;
                var product = self.getProductData();
                return (parseInt(product.is_qty_decimal) == 1)?true:false;
            },
            incQty: function(){
                var self = this;
                var qty = this.getQtyAddToCart();
                var increment = self.getQtyIncrement();
                increment = (increment > 0)?increment:1;
                qty += increment;
                this.qtyAddToCart(qty);
            },
            descQty: function(){
                var self = this;
                var qty = this.getQtyAddToCart();
                var increment = self.getQtyIncrement();
                increment = (increment > 0)?increment:1;
                qty -= increment;
                this.qtyAddToCart(qty);
            },
            getQtyAddToCart: function(){
                var self = this;
                var increment = self.getQtyIncrement();
                if (this.qtyAddToCart() <= increment || isNaN(this.qtyAddToCart())) {
                    return increment;
                }
                return this.qtyAddToCart();
            },
            modifyQty: function(data,event){
                var self = this;
                var increment = self.getQtyIncrement();
                var isQtyDecimal = self.isQtyDecimal();
                var qty = (isQtyDecimal)?parseFloat(event.target.value):parseInt(event.target.value);
                if((increment > 0) && qty%increment > 0){
                    qty -= (isQtyDecimal)?parseFloat(qty%increment):parseInt(qty%increment);
                    qty = (qty > 0)?qty:increment;
                }
                event.target.value = qty;
                this.qtyAddToCart(qty);
            },
            setAllData: function () {
                var self = this;
                this.qtyAddToCart(1);
                if (this.itemData().images) {
                    this.reloadJs();
                }
                /* set config */
                this.defaultPriceAmount('');
                //configurable.priceConfig($.parseJSON(self.getItemData().price_config));
                if (this.getTypeId() == 'configurable') {
                    if (window.timeout != 0) {
                        var timeout = 0;
                    } else {
                        window.timeout = 1;
                        var timeout = 1000;
                    }
                    var spConfigData = $.parseJSON(self.itemData().json_config);
                    this.defaultPriceAmount(priceHelper.convertAndFormat(spConfigData.prices.finalPrice.amount));
                    setTimeout(function() {
                        configurable.priceConfig($.parseJSON(self.itemData().price_config));
                        configurable.options.spConfig = spConfigData;
                        configurable.options.optionTemplate = '<%- data.label %>' +
                            '<% if (data.finalPrice.value) { %>' +
                            ' <%- data.finalPrice.formatted %>' +
                            '<% } %>';
                        configurable.createPriceBox();
                        configurable._create();
                    }, timeout);
                } else {
                    this.defaultPriceAmount(priceHelper.convertAndFormat(this.itemData().final_price));
                    //configurable.priceConfig($.parseJSON(self.itemData().price_config));
                }
            },
            prepareAddToCart: function() {
                var self = this;
                self.updatePrice();
                var ProductModel = ProductFactory.get();
                
                if (this.validateAddToCartForm()) {
                    var product = self.getProductData();
                    var stocks = product.stocks;
                    if (product.super_group && product.super_group.length > 0) {
                        ko.utils.arrayForEach(product.super_group, function (product) {
                            if (product.id) {
                                for(var i in stocks) {
                                    if(stocks[i].sku === product.sku) {
                                        product.stocks = [stocks[i]];
                                        break;
                                    }
                                }
                                ProductModel.data = product;
                                product.unit_price = ProductModel.getFinalPrice();
                                self.addProduct(product);
                            }
                        });
                    }else if(product.storecredit_type == 2){
                        var rate = parseFloat(product.storecredit_rate);
                        if(parseFloat($('#storecredit_'+product.id).val()) < parseFloat(product.storecredit_min) || parseFloat($('#storecredit_'+product.id).val()) > parseFloat(product.storecredit_max)){
                            alertHelper({
                                priority: "danger",
                                title: "Error",
                                message: "Invalid credit value!"
                            });
                        }else{
                            var basePrice = parseFloat($('#storecredit_'+product.id).val()) * rate;
                            self.basePriceAmount(basePrice);
                            self.creditValue(parseFloat($('#storecredit_'+product.id).val()));
                            product = self.getProductData();
                            self.addProduct(product);
                            product.credit_price_amount = undefined;
                        }
                    }else if(product.storecredit_type == 3){
                        product = self.getProductData();
                        self.addProduct(product);
                        product.credit_price_amount = undefined;
                    }else {
                        self.addProduct(product);
                        product.credit_price_amount = undefined;
                    }
                    self.closeDetailPopup();
                } else {
                    if ($('.swatch-option').length > 0) {
                        alertHelper({title:'Error', content: $t('Please choose all options')});
                    }
                }
            },
            /* Validate Add Address Form */
            validateAddToCartForm: function () {
                var form = '#product_addtocart_form';
                return $(form).validation() && $(form).validation('isValid');
            },
            getProductData: function(){
                var self =  this;
                var product = self.itemData();
                if (product.type_id == "configurable") {
                    product.super_attribute = self.configurableOptionsResult();
                    product.unit_price = self.basePriceAmount();
                    product.child_id = self.configurableProductIdResult();
                    product.options_label = self.configurableLabelResult();
                }
                if (product.type_id == "grouped") {
                    product.super_group = self.groupedProductResult();
                    product.unit_price = "";
                    product.options_label = "";
                }
                if (product.type_id == "customercredit") {
                    var rate = product.storecredit_rate;
                    if(typeof product.credit_price_amount !== 'undefined'){
                        product.amount = self.creditValue();
                        product.credit_price_amount = parseFloat(product.amount) * parseFloat(rate);
                    }else{
                        if(product.storecredit_type == 3){
                            var values = product.customercredit_value.split(',');
                            product.credit_price_amount = parseFloat(values[0]) * parseFloat(rate);
                            product.amount = parseFloat(values[0]);
                        }else if(product.storecredit_type == 2){
                            product.credit_price_amount = parseFloat(product.storecredit_min) * parseFloat(rate);
                            product.amount = parseFloat(product.storecredit_min);
                        }else{
                            product.credit_price_amount = parseFloat(product.customercredit_value) * parseFloat(rate);
                            product.amount = parseFloat(product.customercredit_value);
                        }
                    }
                    self.creditValue(product.amount);
                    self.basePriceAmount(product.credit_price_amount);
                    product.unit_price = self.basePriceAmount();
                    product.options_label = priceHelper.convertAndFormat(self.creditValue());
                    product.hasOption = true;
                }
                if (product.type_id == "bundle") {
                    product.bundle_option = self.bundleOptionsValueResult();
                    product.bundle_option_qty = self.bundleOptionsQtyResult();
                    product.bundle_childs_qty = self.bundleChildsQtyResult();
                    product.unit_price = self.basePriceAmount();
                    product.options_label = self.bundleOptionsLableResult();
                }
                if (self.customOptionsValueResult().length > 0) {
                    product.selected_options = self.customOptionsValueResult();
                    product.unit_price = self.basePriceAmount();
                    product.custom_options_label = self.customOptionsLableResult();
                }
                product.qty = self.qtyAddToCart();
                return product;
            },
            addProduct: function(product){
                var self = this;
                var Product = ProductFactory.get();
                if(!product.stocks && !Helper.isUseOnline('stocks')){
                    var stockItem = StockItemFactory.get();
                    var deferred = stockItem.loadByProductId(product.id);
                    deferred.done(function(stock){
                        if(stock.data){
                            product.stocks = [stock.data];
                            Product.setData(product);
                            CartModel.addProduct(Product.getInfoBuyRequest(CartModel.customerGroup()));
                            self.customOptionsValueResult([]);
                            self.customOptionsLableResult([]);
                            $("#search-header-product").val("");
                        }
                    });
                }else{
                    Product.setData(product);
                    CartModel.addProduct(Product.getInfoBuyRequest(CartModel.customerGroup()));
                    Product.resetTempAddData();
                    self.customOptionsValueResult([]);
                    self.customOptionsLableResult([]);
                    $("#search-header-product").val("");
                }
            },
            closeDetailPopup: function() {
                $("#popup-product-detail").hide();
                $(".wrap-backover").hide();
                $('.notification-bell').show();
                $('#c-button--push-left').show();
            },
            reloadJs: function () {
                var $j = jQuery.noConflict();
                if ($j("#product-img-slise").find('div.owl-controls').length > 0) {
                    var removeControl = $j("#product-img-slise").find('div.owl-controls');
                    removeControl[0].remove();
                }
                setTimeout(function(){
                    $j("#product-img-slise").owlCarousels({
                        items: 1,
                        itemsDesktop: [1000, 1],
                        itemsDesktopSmall: [900, 1],
                        itemsTablet: [600, 1],
                        itemsMobile: false,
                        navigation: true,
                        pagination:true,
                        navigationText: ["", ""]
                    });
                }, 50);
            },
            updatePrice: function () {
                var self = this;
                var product = self.itemData();
                if (product.type_id == "grouped" && self.groupItem()) {
                    self.groupItem().updatePrice();
                }
                if (product.type_id == "bundle" && self.bundleItem()) {
                    self.bundleItem().updatePrice();
                }
                
            },
            showPopup: function(){
                var self = this;
                var product = self.itemData();
                product.super_attribute = [];
                product.super_group = [];
                product.bundle_option = [];
                product.bundle_option_qty = [];
                product.bundle_childs_qty = [];
                product.options_label = '';
                product.selected_options = [];
                product.custom_options_label = '';
                self.configurableOptionsResult([]);
                self.configurableLabelResult('');
                self.customOptionsValueResult([]);
                self.customOptionsLableResult([]);
                self.creditProductResult([]);
                self.bundleOptionsLableResult([]);
                self.bundleChildsQtyResult([]);
                self.bundleOptionsQtyResult([]);
                self.bundleOptionsValueResult([]);
                self.groupedProductResult([]);
                configurable.options.state = {};

                $("#popup-product-detail").show();
                $("#popup-product-detail").removeClass("fade");
                $(".wrap-backover").show();

                $(document).click(function(e) {
                    if( e.target.id == 'popup-product-detail') {
                        $("#popup-product-detail").hide();
                        $(".wrap-backover").hide();
                        $('.notification-bell').show();
                        $('#c-button--push-left').show();
                    }
                });
                this.updatePrice();
            },
            
            isShowAvailableQty: function(){
                if(this.itemData().is_virtual) {
                    return false;
                }
                return true;
            },
            
            getAvailableQty: function(productData){
                var stocks = productData.stocks;
                var qty = 0;

                for(var i in stocks) {  
                    qty = qty + stocks[i].qty;
                }
                if(Helper.isStockOnline()){
                    qty = (productData.qty)?productData.qty:0;
                }
                if(productData.type_id == 'configurable'
                    || productData.type_id == 'bundle'
                    || productData.type_id == 'grouped') {
                    return qty + ' ' + $t('child item(s)');  
                } else {
                    return qty + ' ' + $t('item(s)');                    
                }                 
            },
            
            resetSuperAttributeData: function() {
                configurable.options.state = {};
            }
        });
    }
);