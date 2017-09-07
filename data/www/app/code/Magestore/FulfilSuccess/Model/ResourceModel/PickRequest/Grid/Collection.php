<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\FulfilSuccess\Model\ResourceModel\PickRequest\Grid;

use Magestore\FulfilSuccess\Api\Data\PickRequestInterface;
use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Magestore\FulfilSuccess\Service\Location\LocationServiceInterface;
use Magestore\FulfilSuccess\Service\Locator\BatchServiceInterface;
use Magestore\FulfilSuccess\Service\Locator\UserServiceInterface;


class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @inheritdoc
     */
    protected $document = Document::class;
    
    /**
     * @var LocationServiceInterface
     */
    protected $locationService;
    
    /**
     * @var BatchServiceInterface 
     */
    protected $batchService;
    
    /**
     * @var UserServiceInterface 
     */
    protected $userService;

    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        LocationServiceInterface $locationService,
        BatchServiceInterface $batchService,
        UserServiceInterface $userService,
        $mainTable = 'os_fulfilsuccess_pickrequest',
        $resourceModel = 'Magestore\FulfilSuccess\Model\ResourceModel\PickRequest\PickRequest'
    ) {
        $this->locationService = $locationService;
        $this->batchService = $batchService;
        $this->userService = $userService;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * prepare collection
     *
     * @return array
     */
    protected function _initSelect()
    {
        $this->getSelect()
                ->from(['main_table' => $this->getMainTable()])
                ->where('main_table.'. PickRequestInterface::STATUS . '= ?', PickRequestInterface::STATUS_PICKING);
        
        $warehouseId = $this->locationService->getCurrentWarehouseId();
        if($warehouseId) {
            $this->addFieldToFilter(PickRequestInterface::WAREHOUSE_ID, $warehouseId);
        }
        
        $batchId = $this->batchService->getCurrentBatchId();
        if($batchId) {
            $this->addFieldToFilter(PickRequestInterface::BATCH_ID, $batchId);
        }
        
        $this->getSelect()->join(['order' => $this->getTable('sales_order_grid')], 
                                    'main_table.order_id = order.entity_id', 
                                    [
                                        'increment_id',                                        
                                        'shipping_name',
                                        'customer_email',
                                        'purchased_at' => 'order.created_at',
                                        'base_grand_total',
                            ]);
        
        $this->getSelect()->columns(
                [
                    'age' => new \Zend_Db_Expr('TIMESTAMPDIFF(SECOND, main_table.created_at, NOW())')
                ]);        

        return $this;
    }
    /**
     * rewrite add field to filters from collection
     *
     * @return array
     */
    public function addFieldToFilter($field, $condition = null)
    {
        return parent::addFieldToFilter($field, $condition);
    }
}