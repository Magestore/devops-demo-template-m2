<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Model\Inventory;

use \Magento\Framework\ObjectManagerInterface as ObjectManagerInterface;
use \Magestore\Webpos\Model\ResourceModel\Inventory\Stock\Item as StockItemResource;
use \Magento\CatalogInventory\Model\Stock\Item as StockItemModel;
use \Magento\CatalogInventory\Api\StockItemRepositoryInterface as StockItemRepositoryInterface;
use \Magento\Framework\Api\SortOrder;
use \Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;

/**
 * Class StockItemRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockItemRepository implements \Magestore\Webpos\Api\Inventory\StockItemRepositoryInterface {

    /**
     *
     * @var \Magestore\Webpos\Model\ResourceModel\Inventory\Stock\Item
     */
    protected $resource;

    /**
     *
     * @var \Magento\CatalogInventory\Model\Stock\Item  
     */
    protected $stockItemModel;

    /**
     *
     * @var \Magento\Framework\ObjectManagerInterface  
     */
    protected $objectManager;

    /**
     *
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepositoryInterface  
     */
    protected $stockItemRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;   
    
    /**
     *
     * @var StockRegistryProviderInterface 
     */
    protected $stockRegistryProvider;
    
    /**
     * @var \Magento\Framework\Event\ManagerInterface 
     */
    protected $eventManager;
    
    /**
     * @var \Magestore\Webpos\Helper\Permission 
     */
    protected $permissionManager;

    /**
     * StockItemRepository constructor.
     * @param StockItemResource $resource
     * @param StockItemModel $stockItemModel
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StockItemResource $resource,
        StockItemModel $stockItemModel,
        StockItemRepositoryInterface $stockItemRepository,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        StockRegistryProviderInterface $stockRegistryProvider,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magestore\Webpos\Helper\Permission $permissionManager
    ) {
        $this->resource = $resource;
        $this->stockItemModel = $stockItemModel;
        $this->stockItemRepository = $stockItemRepository;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->eventManager = $eventManager;
        $this->permissionManager = $permissionManager;
    }

    /**
     * @inheritdoc
     */
    public function getStockItems(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria) {
        $collection = $this->objectManager->get('\Magestore\Webpos\Model\ResourceModel\Catalog\Product\Collection');
        $storeId = $this->storeManager->getStore()->getId();
        $collection->addAttributeToSelect('name');
        $collection->getSelect()->group('e.entity_id');
        $collection->setStoreId($storeId)->addStoreFilter($storeId);
        $collection = $this->resource->addStockDataToCollection($collection, false);

        /** Integrate webpos **/
        $this->eventManager->dispatch('webpos_inventory_stockitem_getstockitems', [
            'collection' => $collection, 
            'location' => $this->permissionManager->getCurrentLocation()
        ]);
        
        /** End integrate webpos **/

        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        foreach ((array) $searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                    $field, ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();

        $searchResult = $this->objectManager->get('Magento\Framework\Api\Search\SearchResultFactory')->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
    \Magento\Framework\Api\Search\FilterGroup $filterGroup, \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ) {
        $fields = [];
        $i = 0;
        foreach ($filterGroup->getFilters() as $filter) {
            $conditionType = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $condition = $this->convertCondition($conditionType);
            $value = is_array($filter->getValue()) ? "('" . implode("','", $filter->getValue()) . "')" : $filter->getValue();

            if (in_array($condition, ['IN', 'NOT IN'])) {
                $value = '(' . $value . ')';
            } else {
                $value = "'" . $value . "'";
            }

            if ($i == 0) {
                $collection->getSelect()->where($filter->getField() . ' ' . $condition . ' ' . $value);
            } else {
                $collection->getSelect()->orWhere($filter->getField() . ' ' . $condition . ' ' . $value);
            }
            $i++;
        }

        //if ($fields) {
        //$collection->addFieldToFilter('updated_at', ['gteq' => '2016-07-13']);
        //$collection->getSelect()->where("qty > ?", 1);
        //}
    }

    /**
     * Convert sql condition from Magento to Zend Db Select
     * 
     * @param string $type
     * @return string
     */
    protected function convertCondition($type) {
        switch ($type) {
            case 'gt':
                return '>';
            case 'gteq':
                return '>=';
            case 'lt':
                return '<';
            case 'lteq':
                return '=<';
            case 'eq':
                return '='; 
            case 'in':
                return 'IN';
            case 'nin':
                return 'NOT IN';
            case 'neq':
                return '!=';
            case 'like':
                return 'LIKE';
            default:
                return '=';
        }
    }

    /**
     * 
     * @param array $stockItems
     * @return bool
     */
    public function massUpdateStockItems($stockItems) {
        if (count($stockItems)) {
            foreach ($stockItems as $stockItem) {
                if (!$stockItem->getItemId())
                    continue;
                $this->updateStockItem($stockItem->getItemId(), $stockItem);
            }
        }
        return true;
    }

    /**
     * 
     * @param string $itemId
     * @param \Magestore\Webpos\Api\Data\Inventory\StockItemInterface $stockItem
     * @return int
     */
    public function updateStockItem($itemId, \Magestore\Webpos\Api\Data\Inventory\StockItemInterface $stockItem) 
    {
        $origStockItem = $this->stockItemModel->load($itemId);
        $changeQty = $stockItem->getQty() - $origStockItem->getQty();
        $data = $stockItem->getData();
        if ($origStockItem->getItemId()) {
            unset($data['item_id']);
        }
        $origStockItem->addData($data);
        
        $stockItem = $this->stockItemRepository->save($origStockItem);
        
        $this->eventManager->dispatch('webpos_inventory_stockitem_update', [
            'stock_item' => $stockItem,
            'change_qty' => $changeQty,
            'location' => $this->permissionManager->getCurrentLocation(),
            'user' => $this->permissionManager->getCurrentStaffModel()->getUsername(),
        ]);
        
        return $stockItem->getItemId();
    }
    
    /**
     * @return StockConfigurationInterface
     *
     * @deprecated
     */
    private function getStockConfiguration()
    {
        if ($this->stockConfiguration === null) {
            $this->stockConfiguration = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\CatalogInventory\Api\StockConfigurationInterface');
        }
        return $this->stockConfiguration;
    }     

}
