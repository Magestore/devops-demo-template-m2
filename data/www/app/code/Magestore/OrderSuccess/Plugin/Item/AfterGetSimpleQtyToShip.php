<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\OrderSuccess\Plugin\Item;

/**
 * Class AfterGetSimpleQtyToShip
 * @package Magestore\OrderSuccess\Plugin\Item
 */
class AfterGetSimpleQtyToShip
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    public function __construct
    (
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->request = $request;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return mixed
     */
    public function afterGetSimpleQtyToShip(\Magento\Sales\Model\Order\Item $item)
    {
        if(($this->request->getModuleName() == 'ordersuccess' && $this->request->getActionName() == 'getItemsGrid')
            ||($this->request->getModuleName() == 'sales' &&
                ($this->request->getActionName() == 'view') || $this->request->getActionName() == 'new')) {
            $qty = $item->getQtyOrdered() - $item->getQtyShipped() - $item->getQtyRefunded()
                - $item->getQtyCanceled() - $item->getQtyBackordered() - $item->getQtyPrepareship();
            return max($qty, 0);
        }
        $qty = $item->getQtyOrdered() - $item->getQtyShipped() - $item->getQtyRefunded()
            - $item->getQtyCanceled() - $item->getQtyBackordered();
        return max($qty, 0);
    }

}
