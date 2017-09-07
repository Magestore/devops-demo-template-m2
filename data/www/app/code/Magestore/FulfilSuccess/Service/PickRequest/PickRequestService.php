<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\FulfilSuccess\Service\PickRequest;

use Magestore\FulfilSuccess\Api\Data\PickRequestInterface;
use Magestore\FulfilSuccess\Api\Data\PickRequestItemInterface;
use Magestore\FulfilSuccess\Api\Data\PackRequestItemInterface;
use Magestore\FulfilSuccess\Api\PickRequestItemRepositoryInterface;
use Magestore\FulfilSuccess\Api\PackRequestItemRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magestore\OrderSuccess\Api\Data\OrderItemInterface as OrderSuccessOrderItemInterface;
use Magestore\FulfilSuccess\Service\Locator\UserServiceInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class PickRequestService
{
    
    /**
     * @var \Magestore\FulfilSuccess\Api\PickRequestRepositoryInterface
     */
    protected $pickRequestRepository;
    
    /**
     * @var PickRequestItemRepositoryInterface
     */
    protected $pickRequestItemRepository; 
    
    /**
     * @var PackRequestItemRepositoryInterface
     */
    protected $packRequestItemRepository;      
    
    /**
     * @var \Magestore\FulfilSuccess\Api\OrderItemRepositoryInterface
     */
    protected $orderItemRepository;      
    
    /**
     * @var \Magestore\FulfilSuccess\Service\PickRequest\ItemService
     */
    protected $itemService;
    
    /**
     * @var UserServiceInterface 
     */
    protected $userService;
    
    /**
     * @var DateTime 
     */
    protected $dateTime;
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;    
    
    
    public function __construct(
        \Magestore\FulfilSuccess\Api\PickRequestRepositoryInterface $pickRequestRepository,
        PickRequestItemRepositoryInterface $pickRequestItemRepository,
        PackRequestItemRepositoryInterface $packRequestItemRepository,
        \Magestore\FulfilSuccess\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Magestore\FulfilSuccess\Service\PickRequest\ItemService $itemService,
        UserServiceInterface $userService,
        DateTime $dateTime,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->pickRequestRepository = $pickRequestRepository;
        $this->pickRequestItemRepository = $pickRequestItemRepository;
        $this->packRequestItemRepository = $packRequestItemRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->itemService = $itemService;
        $this->userService = $userService;
        $this->dateTime = $dateTime;
        $this->objectManager = $objectManager;
    }

    /**
     * Add Sales Item to PickRequest
     * 
     * @param PickRequestInterface $pickRequest
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @param int $requestQty
     * @param string $parentId
     * @return PickRequestItemInterface
     */
    public function addItemToPickRequest(PickRequestInterface $pickRequest, $item, $requestQty = 0, $parentId = '')
    {
        $qtyToShip = $this->itemService->getQtyToShip($item);
        $requestQty = $requestQty ? min($requestQty, $qtyToShip) : $qtyToShip;
        $pickRequestItem = $this->pickRequestRepository->getPickRequestItem($pickRequest, $item->getItemId());
        $beforeQty = $pickRequestItem->getRequestQty();
        if(!$pickRequestItem->getId()) {
            $pickRequestItem->setItemId($item->getItemId());
            $pickRequestItem->setParentItemId($item->getParentItemId());
            $pickRequestItem->setParentId($parentId);
            $pickRequestItem->setItemName($item->getName());
            $pickRequestItem->setItemSku($item->getSku());
            $pickRequestItem->setProductId($item->getProductId());
            $pickRequestItem->setPickRequestId($pickRequest->getId());
            $pickRequestItem->setPickedQty(0);
            $pickRequestItem->setRequestQty($requestQty);
        } else {
            $requestQty = min($pickRequestItem->getRequestQty() + $requestQty , $qtyToShip);
            $pickRequestItem->setRequestQty($requestQty);
        }
        $this->pickRequestItemRepository->save($pickRequestItem);
        
        /* update total_items in Pick Request */
        $changeQty = $pickRequestItem->getRequestQty() - $beforeQty;
        $pickRequest->setTotalItems(max(0, $pickRequest->getTotalItems() + $changeQty));
        
        /* update qty_prepareship in Sales Item */
        /* ignore child items of ship-together bundle product */
        if(!$item->getParentItem() 
                || $item->getParentItem()->getProductType() != \Magento\Bundle\Model\Product\Type::TYPE_CODE
                || $item->isShipSeparately()) {
            $this->itemService->updatePrepareShipQty($item, $changeQty);
        }
        return $pickRequestItem;
    }
    
    /**
     * Add pack item to Pick Request
     * 
     * @param PickRequestInterface $pickRequest
     * @param PackRequestItemInterface $packItem
     * @return PickRequestItemInterface
     */
    public function addPackItemToPickRequest(PickRequestInterface $pickRequest, PackRequestItemInterface $packItem, $parentId = null)
    {
        $requestQty = $packItem->getRequestQty() - $packItem->getPackedQty();
        $pickRequestItem = $this->pickRequestRepository->getPickRequestItem($pickRequest, $packItem->getItemId());
        $beforeQty = $pickRequestItem->getRequestQty();
        if(!$pickRequestItem->getId()) {
            $pickRequestItem->setParentId($parentId);
            $pickRequestItem->setItemId($packItem->getItemId());
            $pickRequestItem->setParentItemId($packItem->getParentItemId());
            $pickRequestItem->setItemName($packItem->getItemName());
            $pickRequestItem->setItemSku($packItem->getItemSku());
            $pickRequestItem->setProductId($packItem->getProductId());
            $pickRequestItem->setPickRequestId($pickRequest->getId());
            $pickRequestItem->setPickedQty(0);
            $pickRequestItem->setRequestQty($requestQty);
        } else {
            $requestQty = $beforeQty + $requestQty;
            $pickRequestItem->setRequestQty($requestQty);
        }
        $this->pickRequestItemRepository->save($pickRequestItem);
        
        /* update total_items in Pick Request */
        $changeQty = $pickRequestItem->getRequestQty() - $beforeQty;
        $pickRequest->setTotalItems(max(0, $pickRequest->getTotalItems() + $changeQty));

        return $pickRequestItem;        
    }
    
    /**
     * Add Sales Item to PickRequest
     * 
     * @param \Magestore\FulfilSuccess\Api\Data\PickRequestInterface $pickRequest
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     */
    public function removeItemFromPickRequest(PickRequestInterface $pickRequest, OrderItemInterface $item)
    {
        $pickRequestItem = $this->pickRequestRepository->getPickRequestItem($pickRequest, $item->getItemId());
        if($pickRequestItem->getId()) {
            $changeQty = - $pickRequestItem->getRequestQty();
            $this->pickRequestItemRepository->delete($pickRequestItem);
            /* update qty_prepareship in Sales Item */
            $this->itemService->updatePrepareShipQty($item, $changeQty);            
        }
        return $this;
    }    
    
    /**
     * Update Request Qtys in Pick Request
     * 
     * @param \Magestore\FulfilSuccess\Api\Data\PickRequestInterface $pickRequest
     * @param array $changeQtys
     */
    public function updateRequestQtys(PickRequestInterface $pickRequest, $changeQtys)
    {
        $this->updateQtys($pickRequest, $changeQtys);
        return $this;
    }
    
    /**
     * Update picked qtys in PickRequest
     * 
     * @param \Magestore\FulfilSuccess\Api\Data\PickRequestInterface $pickRequest
     * @param array $changeQtys
     */
    public function updatePickedQtys(PickRequestInterface $pickRequest, $changeQtys)
    {
        $this->updateQtys($pickRequest, $changeQtys);
        return $this;
    }
    
    /**
     * Mark Request as Picked all
     * 
     * @param \Magestore\FulfilSuccess\Api\Data\PickRequestInterface $pickRequest
     * @return \Magestore\FulfilSuccess\Service\PickRequest\PickRequestService
     */
    public function markAsPickedAll(PickRequestInterface $pickRequest)
    {
        $this->markAsPicked($pickRequest, [], true);
        return $this;
    }
    
    /**
     * Mark Reuqest as Picked
     * 
     * @param PickRequestInterface $pickRequest
     * @param array $pickItems
     * @param bool $pickAll
     * @return \Magestore\FulfilSuccess\Service\PickRequest\PickRequestService
     * @throws \Exception
     */
    public function markAsPicked(PickRequestInterface $pickRequest, $pickItems = [], $pickAll = false)
    {
        $pickItems = $this->pickRequestRepository->getItemList($pickRequest);
        $totalPickedQty = 0;
        if(count($pickItems)) {
            /* update items in Pick Request */
            foreach($pickItems as $pickItem) {
                $pickedQty = $pickAll ? $pickItem->getRequestQty() : 0;
                if(!$pickedQty) {
                    $pickedQty = isset($pickItems[$pickItem->getItemId()]) ? $pickItems[$pickItem->getItemId()] : 0;
                }
                if(!$pickedQty) {
                    continue;
                }
                $pickItem->setPickedQty($pickedQty);
                $this->pickRequestItemRepository->save($pickItem);
                $totalPickedQty += $pickedQty;
            }
        }
        if(!$totalPickedQty) {
            throw new \Exception(__('There is no item picked!'));
        }
        /* update Pick Request status */
        $pickRequest->setAge($this->getAge($pickRequest));        
        $pickRequest->setStatus(PickRequestInterface::STATUS_PICKED);
        $pickRequest->setUserId($this->userService->getCurrentUserId());
        $this->pickRequestRepository->save($pickRequest);
        /* create pack request here */
        $this->createPackRequest($pickRequest);
        /* */
        return $this;        
    }
    
    /**
     * Cancel a pick request
     * 
     * @param PickRequestInterface $pickRequest
     */
    public function cancel(PickRequestInterface $pickRequest)
    {
        if($pickRequest->getStatus() == PickRequestInterface::STATUS_PICKED) {
            return;
        }
        //$this->moveItemsToNeedToShip($pickRequest);
        $pickRequest->setAge($this->getAge($pickRequest));
        $pickRequest->setStatus(PickRequestInterface::STATUS_CANCELED);
        $this->pickRequestRepository->save($pickRequest);
    }
    
    /**
     * create Pack Request from Pick Request
     * 
     * @param PickRequestInterface $pickRequest
     * @return \Magestore\FulfilSuccess\Api\Data\PackRequestInterface
     */
    public function createPackRequest(PickRequestInterface $pickRequest)
    {
        $packBuilder = $this->objectManager->get('Magestore\FulfilSuccess\Service\PackRequest\BuilderService');
        $packRequest = $packBuilder->createFromPickRequest($pickRequest);
        return $packRequest; 
    }
    
    /**
     * Move items in PickRequest to Prepare Fulfil
     * Change status of Pick Request to Picked
     * 
     * @param \Magestore\FulfilSuccess\Api\Data\PickRequestInterface $pickRequest
     */
    public function moveItemsToNeedToShip(PickRequestInterface $pickRequest)
    {   
        $pickItems = $this->pickRequestRepository->getItemList($pickRequest);
        $moveItems = [];
        if(count($pickItems)) {
            /** 
             * @var \Magestore\FulfilSuccess\Api\Data\PickRequestItemInterface $pickItem 
             */
            $requestQtyChange = 0;
            foreach($pickItems as $pickItem) {
                if($remainQty = $pickItem->getRequestQty() - $pickItem->getPickedQty()) {
                    $moveItems[$pickItem->getItemId()] = [
                        PickRequestItemInterface::ITEM_ID => $pickItem->getItemId(),
                        //PickRequestItemInterface::REQUEST_QTY => -$remainQty,
                        'qty' => $remainQty,
                        OrderSuccessOrderItemInterface::QTY_PREPARESHIP => -$remainQty,
                    ];
                    $requestQtyChange -= $remainQty;
                }
            }
        }
        
        if(count($moveItems)) {
            /* update request_qty of items in Pick Request */
            //$this->updateRequestQtys($pickRequest, $moveItems);
            
            /* update total_items in Pick Request */
            //$pickRequest->setTotalItems(max(0, $pickRequest->getTotalItems() + $requestQtyChange));
            /* update Pick Request status*/
            $pickRequest->setStatus(PickRequestInterface::STATUS_PICKED);
            
            $this->pickRequestRepository->save($pickRequest);
            
            /* update pick_qty of items in Sales Sales */
            $this->orderItemRepository->massUpdatePrepareShipQty($moveItems);
        }
        return $this;
    }
    
    /**
     * Update qtys in PickRequest
     * 
     * @param \Magestore\FulfilSuccess\Api\Data\PickRequestInterface $pickRequest
     * @param array $changeQtys
     */
    public function updateQtys(PickRequestInterface $pickRequest, $changeQtys)
    {
        $changeQtys = $this->itemService->filterQtyData($changeQtys);
        $this->pickRequestRepository->massUpdateItems($pickRequest, $changeQtys);
        return $this;
    }
    
    /**
     * Get total picking qty of Products, grouped by warehouseId
     * 
     * @param int $productId
     * @return array
     */
    public function getPickingQtyProduct($productId)
    {
        return $this->getPickingQtyProducts([$productId]);
    }
    
    /**
     * Get total picking qty of Products, grouped by warehouseId
     * return $pickingQtys[$warehouseId][$itemId]
     * 
     * @param array $productIds
     * @return array
     */
    public function getPickingQtyProducts($productIds)
    {
        $pickingQtys = [];
        
        /* Calculate picking qty */
        $pickRequestItems = $this->pickRequestItemRepository->getPickingList($productIds);
        if(!count($pickRequestItems)) {
            return $pickingQtys;
        }
        foreach($pickRequestItems as $pickRequestItem) {
            $warehouseId = $pickRequestItem->getWarehouseId();
            $productId = $pickRequestItem->getProductId();
            if(!isset($pickingQtys[$warehouseId][$productId])) {
                $pickingQtys[$warehouseId][$productId] = $pickRequestItem->getRequestQty();
            } else {
                $pickingQtys[$warehouseId][$productId] += $pickRequestItem->getRequestQty();
            }
        }
        
        /* Calculate packing qty */
        $packRequestItems = $this->packRequestItemRepository->getPackingList($productIds);
        if(!count($packRequestItems)) {
            return $pickingQtys;
        }
        foreach($packRequestItems as $packRequestItem) {
            $warehouseId = $packRequestItem->getWarehouseId();
            $productId = $packRequestItem->getProductId();
            $packingQty = $packRequestItem->getRequestQty() - $packRequestItem->getPackedQty();
            if(!isset($pickingQtys[$warehouseId][$productId])) {
                $pickingQtys[$warehouseId][$productId] = $packingQty;
            } else {
                $pickingQtys[$warehouseId][$productId] += $packingQty;
            }
        }        

        return $pickingQtys;
    }    
    
    /**
     * Get age (h) of Pick Request
     * 
     * @param PickRequestInterface $pickRequest
     * @return int
     */
    public function getAge(PickRequestInterface $pickRequest)
    {
        if($pickRequest->getAge()) {
            return $pickRequest->getAge();
        }
        if($pickRequest->getStatus() == PickRequestInterface::STATUS_PICKED) {
            return $pickRequest->getAge();
        }
        $age = $this->dateTime->gmtTimestamp() - $this->dateTime->gmtTimestamp($pickRequest->getCreatedAt());
        //$age = intval($age / 3600);
        return $age;
    }
    
    
}