<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\FulfilSuccess\Model\ResourceModel\PackRequest\Grid;

use Magestore\FulfilSuccess\Api\Data\PackRequestInterface;
use Magento\Customer\Ui\Component\DataProvider\Document;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Magestore\FulfilSuccess\Service\Location\LocationServiceInterface;
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
        UserServiceInterface $userService,            
        $mainTable = 'os_fulfilsuccess_packrequest',
        $resourceModel = 'Magestore\FulfilSuccess\Model\ResourceModel\PackRequest\PackRequest'
    )
    {
        $this->locationService = $locationService;
        $this->userService = $userService;        
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()
            ->from(['main_table' => $this->getMainTable()])
            ->where('main_table.'. PackRequestInterface::STATUS . ' NOT IN (\'' . implode("','", [PackRequestInterface::STATUS_PACKED, PackRequestInterface::STATUS_CANCELED]) . '\')');
//            ->where('main_table.'. PackRequestInterface::STATUS . ' NOT IN (?)', implode('","', [PackRequestInterface::STATUS_PACKED, PackRequestInterface::STATUS_CANCELED]));
        $warehouseId = $this->locationService->getCurrentWarehouseId();
        if($warehouseId) {
            $this->addFieldToFilter(PackRequestInterface::WAREHOUSE_ID, $warehouseId);
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
     * Rewrite add field to filters from collection
     *
     * @param array|string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        return parent::addFieldToFilter($field, $condition);
    }
}