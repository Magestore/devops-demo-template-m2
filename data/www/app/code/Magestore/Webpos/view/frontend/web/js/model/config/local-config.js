/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery'
    ],
    function ($) {
        "use strict";
        return {
            prepareKey: function(key) {
                return 'webpos_' + WEBPOS.getConfig('staffId') + '_' + key;
            },
            prepareValueForSave: function (value) {
                return value;
            },
            prepareValueForGet: function (value) {
                return value;
            },            
            save: function(key, value) {
                key = this.prepareKey(key);
                value = this.prepareValueForSave(value);
                return localStorage.setItem(key, value);
            },
            get: function(key) {
                key = this.prepareKey(key);
                var value = localStorage.getItem(key);  
                return this.prepareValueForGet(value);             
            },
        };
    }
);