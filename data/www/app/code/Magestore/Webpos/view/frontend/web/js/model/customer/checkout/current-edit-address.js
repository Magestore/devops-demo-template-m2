/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
define(
    ['ko'],
    function (ko) {
        'use strict';
        var currentEditAddress = ko.observable(null);
        return {
            currentEditAddress: currentEditAddress,
            setCurrentEditAddress: function (address) {
                this.currentEditAddress(address);
            },
            getCurrentEditAddress: function () {
                return this.currentEditAddress();
            }
        };
    }
);
