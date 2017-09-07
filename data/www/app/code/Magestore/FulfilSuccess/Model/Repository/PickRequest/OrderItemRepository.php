<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\FulfilSuccess\Model\Repository\PickRequest;

use Magento\Catalog\Api\Data\ProductOptionExtensionFactory;
use Magento\Catalog\Model\ProductOptionFactory;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Sales\Api\Data\OrderItemSearchResultInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;

use Magestore\FulfilSuccess\Api\OrderItemRepositoryInterface;
use Magestore\OrderSuccess\Api\Data\OrderItemInterface as OrderSuccessOrderItemInterface;

class OrderItemRepository extends \Magento\Sales\Model\Order\ItemRepository implements OrderItemRepositoryInterface
{
    
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * @param DataObjectFactory $objectFactory
     * @param Metadata $metadata
     * @param OrderItemSearchResultInterfaceFactory $searchResultFactory
     * @param ProductOptionFactory $productOptionFactory
     * @param ProductOptionExtensionFactory $extensionFactory
     * @param CollectionFactory $collectionFactory
     * @param array $processorPool
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        Metadata $metadata,
        OrderItemSearchResultInterfaceFactory $searchResultFactory,
        ProductOptionFactory $productOptionFactory,
        ProductOptionExtensionFactory $extensionFactory,
        CollectionFactory $collectionFactory,
        array $processorPool = []
    ) {
        parent::__construct($objectFactory, $metadata, $searchResultFactory, $productOptionFactory, $extensionFactory, $processorPool);
        $this->collectionFactory = $collectionFactory;
    }    
    
    /**
     * Mass Update PrepareShip Qty of Sales Items
     * 
     * @param array $items
     */ 
    public function massUpdatePrepareShipQty($items)
    {
        foreach($items as $itemId => $qtyChange) {
            $qtyPrepareShipChange = isset($qtyChange[OrderSuccessOrderItemInterface::QTY_PREPARESHIP]) 
                                    ? $qtyChange[OrderSuccessOrderItemInterface::QTY_PREPARESHIP] : 0;
            
            if(!$qtyPrepareShipChange) {
                continue;
            }
            
            $orderItem = $this->get($itemId);
            
            /* ignore child of ship-together bundle item */
            if($orderItem->getParentItem() 
                    && $orderItem->getParentItem()->getProductType() != \Magento\Bundle\Model\Product\Type::TYPE_CODE
                    && !$orderItem->isShipSeparately()) {   
                continue;
            }         
            
            if(!$orderItem->getSku()) {
                continue;
            }
            
            $qtyPrepareShip = $orderItem->getData(OrderSuccessOrderItemInterface::QTY_PREPARESHIP);
            $qtyPrepareShip = max(0, ($qtyPrepareShip + $qtyPrepareShipChange));
            $qtyPrepareShip = min($qtyPrepareShip , $orderItem->getQtyToShip());
            
            $orderItem->setData(OrderSuccessOrderItemInterface::QTY_PREPARESHIP, $qtyPrepareShip);

            $this->metadata->getMapper()->save($orderItem);
        }

    }   
    
    
    /**
     * Retrieve order items collection
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    public function getItemsCollection($pickRequestId)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection $itemCollection */
        $itemCollection = $this->collectionFactory->create();
        $itemCollection->getSelect()
            ->join(
                ['pickrequest_item' => $itemCollection->getTable('os_fulfilsuccess_pickrequest_item')],
                'main_table.item_id = pickrequest_item.item_id',
                ['*']
            )
            ->where("pickrequest_item.request_qty > pickrequest_item.picked_qty");

        $itemCollection
            ->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'pickrequest_item.pick_request_id',
                $pickRequestId
            )->load();

        return $itemCollection;
    }

    /**
     * Retrieve order items collection
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    public function getPickedItemsCollection($pickRequestId)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection $itemCollection */
        $itemCollection = $this->collectionFactory->create();
        $itemCollection->getSelect()
            ->join(
                ['pickrequest_item' => $itemCollection->getTable('os_fulfilsuccess_pickrequest_item')],
                'main_table.item_id = pickrequest_item.item_id',
                ['*']
            )
            //->where("pickrequest_item.request_qty = pickrequest_item.picked_qty")
            ->where("pickrequest_item.picked_qty > 0");

        $itemCollection
            ->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'pickrequest_item.pick_request_id',
                $pickRequestId
            )->load();

        return $itemCollection;
    }

}