/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'Magestore_Webpos/js/model/resource-model/magento-rest/abstract'
    ],
    function ($,onlineAbstract) {
        "use strict";

        return onlineAbstract.extend({
            initialize: function () {
                this._super();;
                this.setChangePassWordApiUrlApiUrl('/webpos/staff/changepassword');
            },
            /* Set changePassWord Api Url*/
            setChangePassWordApiUrlApiUrl: function (changePassWord) {
                this.changePassWordApiUrl = changePassWord;
            },
            changePassWord: function(postData, deferred){
                if(!deferred) {
                    deferred = $.Deferred();
                }
                if(this.changePassWordApiUrl) {
                    this.callRestApi(
                        this.changePassWordApiUrl,
                        'post',
                        {},
                        postData,
                        deferred
                    );
                }
                return deferred;
            },
        });
    }
);