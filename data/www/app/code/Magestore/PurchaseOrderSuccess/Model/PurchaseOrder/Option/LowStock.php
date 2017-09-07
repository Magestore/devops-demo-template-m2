<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Model\PurchaseOrder\Option;

/**
 * Class LowStock
 * @package Magestore\PurchaseOrderSuccess\Model\PurchaseOrder\Option
 */
class LowStock extends \Magestore\PurchaseOrderSuccess\Model\Option\AbstractOption
{
    /**
     * @return array
     */
    public function getLowStockList() {
        /** @var \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\Collection $collection */
        $collection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\Collection');
        $collection->setOrder('created_at', 'DESC');
        $collection->getSelect()->where(new \Zend_Db_Expr('created_at >= (NOW() - INTERVAL 1 MONTH )'));
        $options = ['' => __('Please select a low stock notification')];
        /** @var Notification $item */
        foreach ($collection as $item){
            try {
                $ruleId = $item->getRuleId();
                /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Rule $rule */
                $rule = \Magento\Framework\App\ObjectManager::getInstance()
                    ->create('Magestore\InventorySuccess\Model\LowStockNotification\RuleRepository')
                    ->get($ruleId);
            } catch (\Exception $e) {
                $rule = '';
            }
            $label = '';
            if ($rule) {
                $label .= '['.$rule->getRuleName().']';
                $label .= '-['.date('Y-d-m', strtotime($item->getCreatedAt())).']';
            }
            if ($item->getWarehouseName()) {
                $label .= '-['.__('Warehouse: ').$item->getWarehouseName().']';
            } else {
                $label .= '-['.__('Global').']';
            }

            $options[$item->getId()] = $label;//$item->getId();
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
        return $this->getLowStockList();
    }
}