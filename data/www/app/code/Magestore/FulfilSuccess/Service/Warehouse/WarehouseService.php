<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\FulfilSuccess\Service\Warehouse;

use Magestore\FulfilSuccess\Service\PickRequest\PickRequestService;
use Magento\Framework\ObjectManagerInterface;

class WarehouseService implements WarehouseServiceInterface
{
    
    /**
     * @var PickRequestService
     */
    protected $pickRequestService;
    
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;    
    
    /**
     * @var ObjectManagerInterface 
     */
    protected $objectManager;
    
    
    public function __construct(
        PickRequestService $pickRequestService,
        \Magento\Framework\Module\Manager $moduleManager,
        ObjectManagerInterface $objectManager
    )
    {
        $this->pickRequestService = $pickRequestService;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }
    
    /**
     * get list warehouse to pick items
     * $productIds = [$itemId => $productId]
     * return [$itemId => [$warehouseId => $qty]]
     * 
     * @param array $productIds
     * @param bool $showOutStock
     * @return array
     */
    public function getWarehousesToPick($productIds, $showOutStock = false)
    {
        if(!$this->moduleManager->isEnabled('Magestore_InventorySuccess')) {
            return [];
        }
        $warehouseStockRegistry = $this->objectManager->get('Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface');
        $warehouseProducts = $warehouseStockRegistry->getStocksWarehouses($productIds);
        
        $pickingQtys = $this->pickRequestService->getPickingQtyProducts($productIds);

        $warehouses = [];
        foreach($warehouseProducts as $warehouseProduct) {
            $warehouseId = $warehouseProduct->getWarehouseId();
            $productId = $warehouseProduct->getProductId();
            $pickingQty = isset($pickingQtys[$warehouseId][$productId]) 
                            ? $pickingQtys[$warehouseId][$productId] 
                            : 0;
            $availableQty = max(0, $warehouseProduct->getTotalQty() - $pickingQty);
            if(!$showOutStock && !$availableQty) {
                /* do not show out-stock warehouse */
                continue;
            }
            $warehouses[$productId][$warehouseId]['available_qty'] = max(0, $warehouseProduct->getTotalQty() - $pickingQty);
            $warehouses[$productId][$warehouseId]['warehouse'] = $warehouseProduct->getWarehouse();
        }
        
        /* transfer data to item-warehouse */
        $itemWarehouses = [];
        foreach($productIds as $itemId => $productId) {
            if(!isset($warehouses[$productId])) {
                continue;
            }
            /* sort warehouse by available_qty */
            $itemWarehouses[$itemId] =  $this->sortWarehousesByQty($warehouses[$productId]);
        }
        
        return $itemWarehouses;
    }
    
    /**
     * Sort warehouse list
     * $warehouses = [$warehouseId => ['available_qty' => $qty, 'warehouse' => $warehouse]]
     * 
     * @param array $warehouses
     * @return array
     */
    public function sortWarehousesByQty($warehouses)
    {
        $sortedWarehouses = [];
        foreach($warehouses as $warehouseId => &$warehouseData) {
            $warehouseData['warehouse_id'] = $warehouseId;
        }
        
        usort($warehouses, [$this, "sortWarehousesByQtyDESC"]);  

        foreach($warehouses as $warehouse) {
            $sortedWarehouses[$warehouse['warehouse_id']] = $warehouse;
        }

        return $sortedWarehouses;
    }
    
    /**
     * Compare lack_qty of warehouses
     * 
     * @param array $warehouseA
     * @param array $warehouseB
     * @return int
     */
    public function sortWarehousesByQtyDESC($warehouseA, $warehouseB)
    {
        if($warehouseA['available_qty'] == $warehouseB['available_qty'])
            return 0;
        if($warehouseA['available_qty'] > $warehouseB['available_qty'])
            return -1;
        return 1;
    }    
    
}