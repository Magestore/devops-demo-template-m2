<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\FulfilSuccess\Model\ResourceModel\Package\Grid;

use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Magestore\FulfilSuccess\Api\Data\PackageInterface;
use Magestore\FulfilSuccess\Service\Location\LocationServiceInterface;


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
        $mainTable = 'os_fulfilsuccess_package',
        $resourceModel = 'Magestore\FulfilSuccess\Model\ResourceModel\Package\Package'
    ) {
        $this->locationService = $locationService;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _initSelect()
    {   
        /* warehouse filter */
        $warehouseId = $this->locationService->getCurrentWarehouseId();
        if($warehouseId) {
            $this->addFieldToFilter(PackageInterface::WAREHOUSE_ID, $warehouseId);
        }      
        
        $this->getSelect()->joinLeft(['sales_shipment_track' => $this->getTable('sales_shipment_track')],
            'main_table.track_id = sales_shipment_track.entity_id',
            [
                '*'
            ]);
        $this->getSelect()->joinLeft(['sales_shipment_grid' => $this->getTable('sales_shipment_grid')],
            'main_table.shipment_id = sales_shipment_grid.entity_id',
            [
                '*'
            ]);
        $this->getSelect()->from(['main_table' => $this->getMainTable()]);  
        
        return $this;
    }

    /**
     * rewrite add field to filters from collection
     *
     * @return array
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'actions') {
            $field = 'sales_shipment_track.track_number';
            $condition = array('eq' => trim($condition['like'],' %'));
        }
        return parent::addFieldToFilter($field, $condition);
    }
}