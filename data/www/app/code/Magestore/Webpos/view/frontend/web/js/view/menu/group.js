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
        'Magestore_Webpos/js/helper/staff'
    ],
    function ($,ko, Component, staffHelper) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magestore_Webpos/menu/group'
            },
            initialize: function () {
                this._super();
            },
            hasChilds: function(){
                if (this.id == 'inventory' && !staffHelper.isHavePermission('Magestore_Webpos::manage_inventory')) {
                    return false;
                }
                return (this.elems().length > 0)?true:false;
            },
            initData: function (object) {
                if(object.id){
                    this.id = object.id;
                }
                if(object.title){
                    this.title = object.title;
                }
                if(object.sortOrder){
                    this.sortOrder = object.sortOrder;
                }
            },


        });
    }
);