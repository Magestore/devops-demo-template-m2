<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\OrderSuccess\Model\ResourceModel\Order\NeedShip;

/**
 * Class Collection
 * @package Magestore\OrderSuccess\Model\ResourceModel\Sales\NeedShip
 */
class Collection extends \Magestore\OrderSuccess\Model\ResourceModel\Order\Collection
{
    /**
     * add condition.
     *
     * @param
     * @return $this
     */
    public function addCondition(){
        if($this->helper->getOrderConfig('verify')){
            $this->addFieldToFilter('is_verified', 1);
        }
        $this->addFieldToFilter('sales_order.is_virtual', 0);
        $this->addFieldToFilter('main_table.status', array(
            'nin'=> array(
                'holded',
                'canceled',
                'closed',
                'payment_review',
                'complete'
            )
        ));
        $this->getSelect()->join(
            ['sales_order_item' => $this->getTable('sales_order_item')],
            'main_table.entity_id = sales_order_item.order_id 
             && sales_order_item.parent_item_id IS NULL
             && ( sales_order_item.is_virtual IS NULL || sales_order_item.is_virtual = 0)
             && sales_order_item.locked_do_ship IS NULL
            ',
            [
                'qty_ordered' =>  new \Zend_Db_Expr('
                                    (SUM(sales_order_item.qty_ordered)
                                    - SUM(sales_order_item.qty_shipped)
                                    - SUM(sales_order_item.qty_refunded)
                                    - SUM(sales_order_item.qty_canceled)
                                    - SUM(COALESCE(sales_order_item.qty_backordered, 0))
                                    - SUM(COALESCE(sales_order_item.qty_prepareship, 0))
                                    )
                                    *COUNT(DISTINCT sales_order_item.item_id)/COUNT(sales_order_item.item_id)
                                    ')
            ]
        );
        $this->addFieldToFilter('qty_ordered', array('gt'=>0));
    }

    /**
     * rewrite add field to filters from collection
     *
     * @return array
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'qty_ordered') {
            $field = new \Zend_Db_Expr('
                                    sales_order_item.qty_ordered
                                    - sales_order_item.qty_shipped
                                    - sales_order_item.qty_refunded
                                    - sales_order_item.qty_canceled
                                    - COALESCE(sales_order_item.qty_backordered, 0)
                                    - COALESCE(sales_order_item.qty_prepareship, 0)
                                    ');
        }
        return parent::addFieldToFilter($field, $condition);
    }

}
