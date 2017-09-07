<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\Transferred;

use Magestore\PurchaseOrderSuccess\Api\PurchaseOrderRepositoryInterface;
use Magestore\PurchaseOrderSuccess\Api\PurchaseOrderItemRepositoryInterface;
use Magestore\PurchaseOrderSuccess\Api\PurchaseOrderItemTransferredRepositoryInterface;
use Magestore\PurchaseOrderSuccess\Api\Data\PurchaseOrderInterface;
use Magestore\PurchaseOrderSuccess\Api\Data\PurchaseOrderItemInterface;
use Magestore\PurchaseOrderSuccess\Model\PurchaseOrder\Item\TransferredFactory;
use Magestore\PurchaseOrderSuccess\Model\ResourceModel\PurchaseOrder\Item\Transferred;

/**
 * Class TransferredService
 * @package Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\Transferred
 */
class TransferredService 
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    
    /**
     * @var PurchaseOrderRepositoryInterface
     */
    protected $purchaseOrderRepository;
    
    /**
     * @var PurchaseOrderItemRepositoryInterface
     */
    protected $itemRepository;

    /**
     * @var PurchaseOrderItemReturnedRepositoryInterface
     */
    protected $transferredRepository;

    /**
     * @var TransferredFactory
     */
    protected $transferredFactory;

    /**
     * TransferredService constructor.
     * @param PurchaseOrderItemRepositoryInterface $itemRepository
     * @param PurchaseOrderItemTransferredRepositoryInterface $transferredRepository
     * @param TransferredFactory $transferredFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        PurchaseOrderRepositoryInterface $purchaseOrderRepository,
        PurchaseOrderItemRepositoryInterface $itemRepository,
        PurchaseOrderItemTransferredRepositoryInterface $transferredRepository,
        TransferredFactory $transferredFactory
    ){
        $this->objectManager = $objectManager;
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->itemRepository = $itemRepository;
        $this->transferredRepository = $transferredRepository;
        $this->transferredFactory = $transferredFactory;
    }
    
    /**
     * @param array $params
     * @return array
     */
    public function processTransferredData($params = []){
        $result = [];
        foreach ($params as $item){
            if(isset($item['transferred_qty']) &&  $item['transferred_qty'] > 0)
                $result[$item['id']] = $item;
        }
        return $result;
    }

    /**
     * Create an empty transfer stock
     * 
     * @param array $param
     * @param string $userName
     * @return \Magestore\InventorySuccess\Model\TransferStock
     */
    public function createTransferStock($param = [], $userName){
        $warehouse = $this->objectManager->create('Magestore\InventorySuccess\Model\Warehouse')
            ->load($param['warehouse_id']);
        $purchaseOrder = $this->purchaseOrderRepository->get($param['purchase_order_id']);
        $purchaseCode = $purchaseOrder->getPurchaseCode();
        return $this->objectManager->create('Magestore\InventorySuccess\Model\TransferStock')
            ->setData('transferstock_code',
                $this->objectManager->create('Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement')
                    ->generateCode())
            ->setData('external_location', __('Purchase order #%1', $purchaseCode))
            ->setData('des_warehouse_id', $warehouse->getWarehouseId())
            ->setData('des_warehouse_code', $warehouse->getWarehouseCode())
            ->setData('reason', __('Transfer stock from purchase order #%1', $purchaseCode))
            ->setData('notifier_emails', '')
            ->setData('status', 'pending')
            ->setData('type', 'from_external')
            ->setData('created_by', $userName)
            ->setData('created_at', $param['transferred_at'])
            ->setId(null)
            ->save();
    }

    /**
     * @param \Magestore\InventorySuccess\Model\TransferStock $transferStock
     * @param array $transferredData
     * @param array $params
     * @return \Magestore\InventorySuccess\Model\TransferStock
     */
    public function saveTransferStockData($transferStock, $transferredData = [], $params = []
    ){
        $data = $this->reformatPostData($transferStock, $transferredData, $params);
        $this->objectManager->create('Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement')
            ->saveTransferStockProduct($transferStock->getId(), $data);
        $this->objectManager->create('Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement')
            ->updateStock($transferStock, true);
        $transferStock->setData('status', 'completed');
        return $transferStock->save();
    }

    /**
     * @param \Magestore\InventorySuccess\Model\TransferStock $transferStock
     * @param array $transferredData
     * @param array $params
     * @return array
     */
    public function reformatPostData($transferStock, $transferredData = [], $params = []
    ){
        $id = $transferStock->getId();
        $newData = [];
        foreach ($transferredData as $data){
            $item = [];
            $item['transferstock_id'] = $id;
            $item['product_id'] = $data['id'];
            $item['product_name'] = $data['product_name'];
            $item['product_sku'] = $data['product_sku'];
            $item['qty'] =  $data['transferred_qty'];
            $newData[$data['id']] = $item;
        }
        return $newData;
    }

    /**
     * @param PurchaseOrderItemInterface $purchaseItem
     * @param null $returnedQty
     * @return float|null
     */
    public function setQtyTransferred(PurchaseOrderItemInterface $purchaseItem, $transferData = []){
        $qty = $purchaseItem->getQtyReceived() - $purchaseItem->getQtyTransferred() - $purchaseItem->getQtyReturned();
        if(!isset($transferData['transferred_qty']) || $transferData['transferred_qty'] > $qty)
            $transferData['transferred_qty'] = $qty;
        return $transferData;
    }

    /**
     * @param PurchaseOrderItemInterface $purchaseItem
     * @param array $transferData
     * @param array $params
     * @param null $createdBy
     * @return \Magestore\PurchaseOrderSuccess\Model\PurchaseOrder\Item\Transferred
     */
    public function prepareItemTransferred(
        PurchaseOrderItemInterface $purchaseItem, $transferData = [], $params = [], $createdBy = null
    ){
        $transferData = $this->setQtyTransferred($purchaseItem, $transferData);
        return $this->transferredFactory->create()
            ->setPurchaseOrderItemId($purchaseItem->getPurchaseOrderItemId())
            ->setQtyTransferred($transferData['transferred_qty'])
            ->setWarehouseId($params['warehouse_id'])
            ->setTransferredAt($params['transferred_at'])
            ->setCreatedBy($createdBy);
    }

    /**
     * @param PurchaseOrderInterface $purchaseOrder
     * @param PurchaseOrderItemInterface $purchaseItem
     * @param null $transferData
     * @param array $params
     * @param null $createdBy
     * @return bool|\Magestore\PurchaseOrderSuccess\Model\PurchaseOrder\Item\Transferred
     */
    public function transferItem(
        PurchaseOrderInterface $purchaseOrder, PurchaseOrderItemInterface $purchaseItem, $transferData = null, $params = [], $createdBy = null
    ){
        $transferData = $this->setQtyTransferred($purchaseItem, $transferData);
        $itemTransferred = $this->prepareItemTransferred($purchaseItem, $transferData, $params, $createdBy);
        try{
            $this->transferredRepository->save($itemTransferred);
            $purchaseItem->setQtyTransferred($purchaseItem->getQtyTransferred()+$transferData['transferred_qty']);
            $this->itemRepository->save($purchaseItem);
            $purchaseOrder->setTotalQtyTransferred($purchaseOrder->getTotalQtyTransferred()+$transferData['transferred_qty']);
        }catch (\Exception $e){
            return false;
        }
        return $transferData;    
    }
}