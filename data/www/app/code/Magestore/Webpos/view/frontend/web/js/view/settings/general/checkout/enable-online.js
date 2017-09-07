/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'Magestore_Webpos/js/view/settings/general/element/select',
        'Magestore_Webpos/js/model/event-manager'
    ],
    function (Select, Event) {
        "use strict";

        return Select.extend({
            defaults: {
                elementName: 'os_checkout.enable_online_mode',
                configPath: 'os_checkout/enable_online_mode',
                defaultValue: 0
            },
            initialize: function () {
                this._super();
                Event.dispatch('checkout_mode_configuration_change', '');
            },
            saveConfig: function (data, event) {
                this._super();
                Event.dispatch('checkout_mode_configuration_change', '');
            }
        });
    }
);