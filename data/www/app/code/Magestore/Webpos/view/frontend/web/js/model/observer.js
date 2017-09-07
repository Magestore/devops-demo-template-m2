/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery',
        'Magestore_Webpos/js/observer/inventory/catalog-product-collection-load-after',
        'Magestore_Webpos/js/observer/inventory/sales-order-cancel-after',
        'Magestore_Webpos/js/observer/inventory/sales-order-creditmemo-save-after',
        'Magestore_Webpos/js/observer/synchronization/model-save-after',
        'Magestore_Webpos/js/observer/synchronization/model-massupdate-after',
        'Magestore_Webpos/js/observer/checkout/place-order-before',
        'Magestore_Webpos/js/observer/checkout/place-order-after',
        'Magestore_Webpos/js/observer/checkout/sync-offline-order-after',
        'Magestore_Webpos/js/observer/customer/sync-offline-customer-after',
        'Magestore_Webpos/js/observer/shift/sync-offline-shift-after',
        'Magestore_Webpos/js/observer/shift/refresh-shift-listing',
        'Magestore_Webpos/js/observer/shift/after-closed-shift',
        'Magestore_Webpos/js/observer/catalog/stock-item-save-after',
        'Magestore_Webpos/js/observer/catalog/stock-item-massupdate-after',
        'Magestore_Webpos/js/observer/catalog/stock-item-page-massupdate-after',
        'Magestore_Webpos/js/observer/catalog/stock-item-pull-after',
        'Magestore_Webpos/js/observer/shift/update-shift-after-create-order',
        'Magestore_Webpos/js/observer/shift/open-shift-after',
        'Magestore_Webpos/js/observer/checkout/cart-item-remove-after',
        'Magestore_Webpos/js/observer/sales/after-take-payment',
        'Magestore_Webpos/js/observer/customer/customer-pull-duplicate',
        'Magestore_Webpos/js/observer/shift/update-shift-after-take-payment',
        'Magestore_Webpos/js/observer/checkout/tax-calculation-finish-pull-after',
        'Magestore_Webpos/js/observer/catalog/category/load-product-by-category',
        'Magestore_Webpos/js/observer/integration/rewardpoints/sync-prepare-maps',
        'Magestore_Webpos/js/observer/integration/storecredit/sync-prepare-maps'
    ],
    function ($, 
              catalogProductCollectionLoadAfter,
              inventorySalesOrderCancelAfter,
              inventorySalesOrderCreditmemoSaveAfter,
              modelSaveAfter,
              modelMassUpdateAfter,
              placeOrderBefore,
              placeOrderAfter,
              syncOrderAfter,
              customerAfter,
              syncOfflineShiftAfter,
              refreshShiftListing,
              afterClosedShift,
              catalogStockItemSaveAfter,
              catalogStockItemMassupdateAfter,
              catalogStockItemPageMassupdateAfter,
              catalogStockItemPullAfter,
              updateShiftAfterCreateOrder,
              openShiftAfter,
              cartItemRemoveAfter,
              orderTakePaymentAfter,
              customerPullDuplicate,
              updateShiftAfterTakePayment,
              finishPullTaxCalculation,
              loadProductByCategory,
              rewardpointsSyncPrepareMaps,
              storecreditSyncPrepareMaps
    ) {
        "use strict";

        return {
            processEvent: function() {
                catalogProductCollectionLoadAfter.execute();
                inventorySalesOrderCancelAfter.execute();
                inventorySalesOrderCreditmemoSaveAfter.execute();
                modelSaveAfter.execute();
                modelMassUpdateAfter.execute();
                placeOrderBefore.execute();                   
                placeOrderAfter.execute();
                syncOrderAfter.execute();                
                customerAfter.execute();
                syncOfflineShiftAfter.execute();
                refreshShiftListing.execute();
                afterClosedShift.execute();
                catalogStockItemSaveAfter.execute();
                catalogStockItemMassupdateAfter.execute();
                catalogStockItemPageMassupdateAfter.execute();
                catalogStockItemPullAfter.execute();
                updateShiftAfterCreateOrder.execute();
                openShiftAfter.execute();
                cartItemRemoveAfter.execute();
                orderTakePaymentAfter.execute();
                customerPullDuplicate.execute();
                updateShiftAfterTakePayment.execute();
                finishPullTaxCalculation.execute();
                loadProductByCategory.execute();
                rewardpointsSyncPrepareMaps.execute();
                storecreditSyncPrepareMaps.execute();
            },
        };
    }
);