<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Ui\DataProvider\PurchaseOrder\Form\Modifier\PurchaseSumary;

use Magestore\PurchaseOrderSuccess\Api\Data\PurchaseOrderItemInterface;
use Magestore\SupplierSuccess\Api\Data\SupplierProductInterface;

/* Add by Kai - fix bug export on Modal */
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
/* End by Kai - fix bug export on Modal */

class SupplyNeedProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    const SUPPLY_NEED_SALES_PERIOD = 'purchaseordersuccess/supply_need/sale_period';
    const SUPPLY_NEED_FORECAST_PERIOD = 'purchaseordersuccess/supply_need/forecast_period';

    /**
     * @var \Magestore\SupplierSuccess\Model\ResourceModel\Supplier\Product\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\ItemService
     */
    protected $purchaseItemService;


    /* Add by Kai - fix bug export on Modal */
    protected $searchCriteria;
    protected $searchCriteriaBuilder;
    protected $reporting;
    /* End by Kai - fix bug export on Modal */

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,

        /* Add by Kai - fix bug export on Modal */
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        /* End by Kai - fix bug export on Modal */

        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\ItemService $purchaseItemService,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        /* Add by Kai - fix bug export on Modal */
        $this->reporting = $reporting;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        /* End by Kai - fix bug export on Modal */

        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->purchaseItemService = $purchaseItemService;
        $this->collection = $this->getSupplyNeedProductCollection();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();
        return [

            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }

    /**
     * @return \Magestore\SupplierSuccess\Model\ResourceModel\Supplier\Product\Collection $collection
     */
    public function getSupplyNeedProductCollection(){
        $topFilter = $this->prepareSupplyNeedParams();
        $collection = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magestore\InventorySuccess\Model\SupplyNeeds\SupplyNeedsManagement')
            ->getProductSupplyNeedsCollection($topFilter, null, null);
        $supplierId = $this->request->getParam('supplier_id', null);
        $purchaseId = $this->request->getParam('purchase_id', null);
        $conditions = 'e.entity_id = supplier_product.product_id';
        if($supplierId)
            $conditions .= ' AND supplier_product.supplier_id = '.$supplierId;
        $collection->getSelect()->joinInner(
            array('supplier_product' => $collection->getTable('os_supplier_product')),
            $conditions,
            '*'
        );
        if($purchaseId){
            $productIds = $this->purchaseItemService->getProductsByPurchaseOrderId($purchaseId)
                ->getColumnValues(PurchaseOrderItemInterface::PRODUCT_ID);
            if(!empty($productIds))
                $collection->addFieldToFilter('entity_id', ['nin' => $productIds]);
        }
        if( ($this->request->getActionName() == 'render')){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
            $exportession = $objectManager->get('\Magento\Newsletter\Model\Session');
            $exportession->setExportData($collection->getData());
        }
        return $collection;
    }

    /**
     * Prepare supply need params
     *
     * @return array
     */
    public function prepareSupplyNeedParams(){
        $params = [
            'warehouse_ids' => $this->request->getParam('warehouse_ids'),
            'sales_period' => $this->request->getParam(
                'sales_period',
                'last_7_days'
            ),
            'from_date' => $this->request->getParam('from_date'),
            'to_date' => $this->request->getParam('to_date'),
            'forecast_date_to' => $this->request->getParam('forecast_date_to'),
        ];
        return base64_encode(serialize($params));
    }

    /**
     * @inheritdoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if(in_array($filter->getField(),['product_id', 'product_sku', 'product_supplier_sku', 'product_name'])){
            $resultCondition = $this->_translateCondition(
                'supplier_product.'.$filter->getField(),
                [$filter->getConditionType() => $filter->getValue()]
            );
            $this->getCollection()->getSelect()->where(
                $resultCondition, null, \Magento\Framework\DB\Select::TYPE_CONDITION
            );
        }else {
            return parent::addFilter($filter);
        }
    }

    /**
     * Build sql where condition part
     *
     * @param   string|array $field
     * @param   null|string|array $condition
     * @return  string
     */
    protected function _translateCondition($field, $condition)
    {
        return $this->_getConditionSql($this->getCollection()->getConnection()->quoteIdentifier($field), $condition);
    }

    protected function _getConditionSql($fieldName, $condition)
    {
        return $this->getCollection()->getConnection()->prepareSqlCondition($fieldName, $condition);
    }


    /* Add by Kai - fix bug export on Modal */
    public function getSearchCriteria()
    {
        if (!$this->searchCriteria) {
            $this->searchCriteria = $this->searchCriteriaBuilder->create();
            $this->searchCriteria->setRequestName($this->name);
        }
        return $this->searchCriteria;

    }
    public function getSearchResult()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $exportession = $objectManager->get('\Magento\Newsletter\Model\Session');
        $collection = $exportession->getExportData();
        //$collection = $this->collection;
        $count = count($collection);
        $this->collection->setPageSize($count);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        /** @var \Magento\Framework\Search\EntityMetadata $entityMetadata */
        $entityMetadata = $objectManager->create('Magento\Framework\Search\EntityMetadata', ['entityId' => 'id']);
        $idKey = $entityMetadata->getEntityId();
        /** @var \Magento\Framework\Search\Adapter\Mysql\DocumentFactory $documentFactory */
        $documentFactory = $objectManager->create(
            'Magento\Framework\Search\Adapter\Mysql\DocumentFactory',
            ['entityMetadata' => $entityMetadata]
        );
        /** @var \Magento\Framework\Api\Search\Document[] $documents */
        $documents = [];
        foreach($collection as $value){
            $value['ids'] = $value['entity_id'];
            $documents[] = $documentFactory->create($value);
        }
        $obj = new \Magento\Framework\DataObject();
        $obj->setItems($documents);
        $obj->setTotalCount($count);
        return $obj;
        //return $this->reporting->search($this->getSearchCriteria());
    }
    /*  End by Kai - fix bug export odalon Modal */
}