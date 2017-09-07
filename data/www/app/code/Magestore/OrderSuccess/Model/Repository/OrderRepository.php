<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\OrderSuccess\Model\Repository;

use Magestore\OrderSuccess\Api\Data\OrderInterface;
use Magestore\OrderSuccess\Api\Data\BatchInterface;
use Magestore\OrderSuccess\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Api\Data\OrderSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magestore\OrderSuccess\Model\OrderFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magestore\OrderSuccess\Model\Db\QueryProcessorInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

/**
 * Class OrderRepository
 * @package Magestore\OrderSuccess\Model\Repository
 */
class OrderRepository extends \Magento\Sales\Model\OrderRepository
                      implements OrderRepositoryInterface
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    protected $resource;

    /**
     * @var \Magestore\OrderSuccess\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magestore\OrderSuccess\Model\Db\QueryProcessorInterface
     */
    protected $queryProcessor;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * OrderRepository constructor.
     * @param Metadata $metadata
     * @param SearchResultFactory $searchResultFactory
     * @param OrderResource $resource
     * @param OrderFactory $orderFactory
     * @param QueryProcessorInterface $queryProcessor
     * @param OrderCollectionFactory $orderCollectionFactory
     */
    public function __construct(
        Metadata $metadata,
        SearchResultFactory $searchResultFactory,
        OrderResource $resource,
        OrderFactory $orderFactory,
        QueryProcessorInterface $queryProcessor,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        parent::__construct($metadata, $searchResultFactory);
        $this->resource = $resource;
        $this->orderFactory = $orderFactory;
        $this->queryProcessor = $queryProcessor;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * create a new batch
     * 
     * @return BatchInterface
     */
    public function newBatch()
    {
        $batch = $this->batchFactory->create();
        $batch->setUserId($this->session->getUser()->getId());
        return $this->save($batch);
    }

    /**
     * Mass update order
     *
     * @param string $actionKey
     * @param string $actionValue
     * @param array $orderIds
     */
    public function massUpdate($orderIds, $actionKey, $actionValue)
    {
        if(!count($orderIds)){
            return;
        }
        $this->queryProcessor->start('massUpdateBatch');

        $this->queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' =>  [$actionKey => $actionValue],
            'condition' => [OrderInterface::ENTITY_ID. ' IN (?)' => $orderIds],
            'table' => $this->resource->getMainTable()
        ], 'massUpdateBatch');
        $this->queryProcessor->process('massUpdateBatch');
    }

    /**
     * Mass update batch Id of orders
     *
     * @param array $orderIds
     * @param int $batchId
     */
    public function massUpdateBatch($orderIds, $batchId)
    {
        $this->massUpdate($orderIds, OrderInterface::BATCH_ID, $batchId);
    }

    /**
     * Mass veriy orders
     *
     * @param array $orderIds
     * @param boolean $isVerify
     */
    public function massVerify($orderIds, $isVerify)
    {
        $this->massUpdate($orderIds, OrderInterface::IS_VERIFIED, $isVerify);
    }

    /**
     * Mass update tag orders
     *
     * @param array $orderIds
     * @param boolean $isVerify
     */
    public function massUpdateTag($orderIds, $tag)
    {
        $this->massUpdate($orderIds, OrderInterface::TAG_COLOR, $tag);
    }

    /**
     * Retrieve Sales.
     *
     * @param int $orderId
     * @return \Magestore\OrderSuccess\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($orderId)
    {
        $order = $this->orderFactory->create();
        $this->resource->load($order, $orderId);
        if (!$order->getId()) {
            throw new NoSuchEntityException(__('The order with ID "%1" does not exist.', $orderId));
        }
        return $order;
    }

    /**
     * Retrieve requets in a Batch
     * 
     * @param array $batchIds
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function getOrderListFromBatch($batchIds)
    {
        $orders = $this->orderCollectionFactory->create()
            ->addFieldToFilter('batch_id', ['in'=>$batchIds]);
        return $orders;
    }

}

