<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\DropshipSuccess\Service\DropshipRequest;

use Magestore\DropshipSuccess\Api\DropshipRequestItemRepositoryInterface;
use Magestore\DropshipSuccess\Model\DropshipRequest\ItemFactory;
use Magestore\DropshipSuccess\Service\ItemService;

/**
 * Class DropshipRequestItemService
 * @package Magestore\DropshipSuccess\Service\DropshipRequest
 */
class DropshipRequestItemService
{
    /**
     * @var DropshipRequestItemRepositoryInterface
     */
    protected $dropshipRequestItemRepository;

    /**
     * @var ItemFactory
     */
    protected $dropshipRequestItemFactory;

    /**
     * @var ItemService
     */
    protected $itemService;

    /**
     * DropshipRequestItemService constructor.
     * @param DropshipRequestItemRepositoryInterface $dropshipRequestItemRepository
     * @param ItemFactory $dropshipRequestItemFactory
     * @param ItemService $itemService
     * @param \Magestore\DropshipSuccess\Model\ResourceModel\DropshipRequest\Item\CollectionFactory $dropshipRequestItemCollectionFactory
     */
    public function __construct(
        DropshipRequestItemRepositoryInterface $dropshipRequestItemRepository,
        ItemFactory $dropshipRequestItemFactory,
        ItemService $itemService,
        \Magestore\DropshipSuccess\Model\ResourceModel\DropshipRequest\Item\CollectionFactory $dropshipRequestItemCollectionFactory
    ) {
        $this->dropshipRequestItemRepository = $dropshipRequestItemRepository;
        $this->dropshipRequestItemFactory = $dropshipRequestItemFactory;
        $this->itemService = $itemService;
        $this->dropshipRequestItemCollectionFactory = $dropshipRequestItemCollectionFactory;
    }

    /**
     * @param \Magestore\DropshipSuccess\Model\DropshipRequest $dropshipRequest
     * @param \Magento\Sales\Model\Order\Item $item
     * @param $requestQty
     */
    public function addItemToDropshipRequest($dropshipRequest, $item, $requestQty)
    {
        /** @var \Magestore\DropshipSuccess\Model\DropshipRequest\Item $dropshipRequestItem */
        $dropshipRequestItem = $this->dropshipRequestItemFactory->create();
        $dropshipRequestItem->setDropshipRequestId($dropshipRequest->getId());
        $dropshipRequestItem->setParentItemId($item->getParentItemId());
        $dropshipRequestItem->setItemId($item->getId());
        $dropshipRequestItem->setQtyRequested($requestQty);
        $dropshipRequestItem->setItemName($item->getName());
        $dropshipRequestItem->setItemSku($item->getSku());
        $this->dropshipRequestItemRepository->save($dropshipRequestItem);
        /* update qty_prepareship in Order Item */
        $changeQty = $dropshipRequestItem->getQtyRequested();
        $this->itemService->updatePrepareShipQty($item, $changeQty);
        return $dropshipRequestItem;
    }

    /**
     * @param $dropshipRequestId
     * @return \Magestore\DropshipSuccess\Model\ResourceModel\DropshipRequest\Item\Collection
     */
    public function getItemsInDropship($dropshipRequestId)
    {
        /** @var \Magestore\DropshipSuccess\Model\ResourceModel\DropshipRequest\Item\Collection $dropshipRequestItemCollection */
        $dropshipRequestItemCollection = $this->dropshipRequestItemCollectionFactory->create();
        $dropshipRequestItemCollection->addFieldToFilter('dropship_request_id', $dropshipRequestId);
        return $dropshipRequestItemCollection;
    }
}