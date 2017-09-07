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
        'Magestore_Webpos/js/view/menu/group',
        'Magestore_Webpos/js/view/menu/item',
        'Magestore_Webpos/js/model/resource-model/magento-rest/abstract',
        'Magestore_Webpos/js/lib/cookie',
        'Magestore_Webpos/js/helper/full-screen-loader',
        'Magento_Ui/js/modal/confirm',
        'mage/translate'
    ],
    function ($,ko, Component, Group, Item, restAbstract, Cookies, fullScreenLoader, confirm, Translate) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magestore_Webpos/menu'
            },
            initialize: function () {
                this._super();
            },
            /**
             * 
             * @param {id:'',title:''} dataObject
             * @param int position
             * @returns void
             */
            addMenuGroup: function (dataObject, position) {
                var object = new Group();
                object.initData(dataObject);
                var validateMenu = true;
                $.each(this.elems(), function(){
                    if(object && object.id && this.id == object.id){
                        validateMenu = false;
                        var datetime = "Logged time: " + new Date();
                        console.log(datetime+':\nMenu group with id "'+ data.id + '" already existed');
                    }
                });
                if(object && object.id && object.title && validateMenu == true){
                    this.insertChild(object, position);
                    var datetime = "Logged time: " + new Date();
                    console.log(datetime+':\nAdded menu group: '+ object.title);
                }
            },
            /**
             * 
             * @param {id:'',title:'', group:'',is_display:'1',icon_class:''} dataObject
             * @param int position
             * @returns void
             */
            addMenuItem: function (dataObject, position) {
                var object = new Item();
                object.initData(dataObject);
                var foundGroup = false;
                var childs = this.elems();
                $.each(childs, function(){
                    if(object && object.data.group && this.id == object.data.group){
                        foundGroup = true;
                        this.insertChild(object,position);
                        var datetime = "Logged time: " + new Date();
                        console.log(datetime+':\nAdded menu item: '+ object.data.title);
                    }
                });
                if(foundGroup == false){
                    var datetime = "Logged time: " + new Date();
                    console.log(datetime+':\nNot found menu group with id "'+ object.data.group + '" ');
                }
            },

            logout: function () {
                var deferredSession = this.getSessionId();
                deferredSession.done(function (response) {
                    var sessionId = response;
                    confirm({
                        content: Translate('Are you sure you want to logout?'),
                        actions: {
                            confirm: function () {
                                var apiUrl = '/webpos/staff/logout';
                                var deferred = $.Deferred();
                                Cookies.remove('WEBPOSSESSION');
                                fullScreenLoader.startLoader();

                                restAbstract().setPush(true).setLog(false).callRestApi(
                                    apiUrl + '?session=' + sessionId,
                                    'post',
                                    {},
                                    {
                                    },
                                    deferred
                                );

                                deferred.always(function (data) {
                                    window.location.reload();
                                });
                            },
                            always: function (event) {
                                event.stopImmediatePropagation();
                            }
                        }
                    });
                });

            },

            getSessionId: function () {
                var deferred = $.Deferred();
                deferred.resolve(Cookies.get('WEBPOSSESSION'));
                return deferred;
            }
        });
    }
);
