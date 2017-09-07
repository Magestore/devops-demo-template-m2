<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\FulfilSuccess\Model\Repository\PackRequest;

use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Api\Data\OrderItemSearchResultInterfaceFactory;
use Magento\Catalog\Model\ProductOptionFactory;
use Magento\Catalog\Api\Data\ProductOptionExtensionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;

use Magestore\FulfilSuccess\Api\PackRequestOrderItemRepositoryInterface;

class PackRequestOrderItemRepository extends \Magento\Sales\Model\Order\ItemRepository implements PackRequestOrderItemRepositoryInterface
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * PackRequestOrderItemRepository constructor.
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
        array $processorPool = [])
    {
        parent::__construct($objectFactory, $metadata, $searchResultFactory, $productOptionFactory, $extensionFactory, $processorPool);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getNeedToPackItemsCollection($packRequestId)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection $itemCollection */
        $itemCollection = $this->collectionFactory->create();
        $itemCollection->getSelect()
            ->join(
                ['packrequest_item' => $itemCollection->getTable('os_fulfilsuccess_packrequest_item')],
                'main_table.item_id = packrequest_item.item_id',
                ['*']
            )
            ->where("packrequest_item.request_qty > packrequest_item.packed_qty");

        $itemCollection
            ->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'packrequest_item.pack_request_id',
                $packRequestId
            )->load();

        return $itemCollection;
    }

    /**
     * @inheritDoc
     */
    public function getPackedItemsCollection($packRequestId)
    {
        $itemCollection = $this->collectionFactory->create();
        $itemCollection->getSelect()
            ->join(
                ['packrequest_item' => $itemCollection->getTable('os_fulfilsuccess_packrequest_item')],
                'main_table.item_id = packrequest_item.item_id',
                ['*']
            )
            ->where("packrequest_item.request_qty = packrequest_item.packed_qty");

        $itemCollection
            ->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'packrequest_item.pack_request_id',
                $packRequestId
            )->load();

        return $itemCollection;
    }

}