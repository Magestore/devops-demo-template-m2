<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Model\PurchaseOrder\Option;


/**
 * Class Warehouse
 * @package Magestore\PurchaseOrderSuccess\Model\PurchaseOrder\Option
 */
class Warehouse extends \Magestore\PurchaseOrderSuccess\Model\Option\AbstractOption
{
    public function getWarehouseOptions(){
        $collection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection')
            ->addFieldToSelect('warehouse_id')
            ->addFieldToSelect('warehouse_code')
            ->addFieldToSelect('warehouse_code');
        $options = ['' => __('Please select a warehouse')];
        foreach ($collection->getItems() as $warehouse){
            $options[$warehouse->getId()] = $warehouse->getWarehouseCode();
        }
        return $options;
    }

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionHash()
    {
        return $this->getWarehouseOptions();
    }
}