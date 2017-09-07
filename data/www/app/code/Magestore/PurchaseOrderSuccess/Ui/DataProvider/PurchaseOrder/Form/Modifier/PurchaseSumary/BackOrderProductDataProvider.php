<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Ui\DataProvider\PurchaseOrder\Form\Modifier\PurchaseSumary;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magestore\PurchaseOrderSuccess\Api\Data\PurchaseOrderItemInterface;
use Magestore\SupplierSuccess\Api\Data\SupplierProductInterface;

class BackOrderProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;
    
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var \Magestore\SupplierSuccess\Service\Supplier\ProductService
     */
    protected $supplierProductService;
    
    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\ItemService
     */
    protected $purchaseItemService;
    
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\App\RequestInterface $request,
        CollectionFactory $productCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $itemCollectionFactory,
        \Magestore\SupplierSuccess\Service\Supplier\ProductService $supplierProductService,
        \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\ItemService $purchaseItemService,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->moduleManager = $moduleManager;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->supplierProductService = $supplierProductService;
        $this->purchaseItemService = $purchaseItemService;
        $this->collection = $this->getBackOrderProductCollection();
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
        //if(!$this->request->getParam('purchase_id'))
            return $items;
//        return [
//            'totalRecords' => $this->getCollection()->getSize(),
//            'items' => array_values($items),
//        ];
        
    }

    /**
     * @return \Magestore\SupplierSuccess\Model\ResourceModel\Supplier\Product\Collection $collection
     */
    public function getBackOrderProductCollection(){
        $supplierId = $this->request->getParam('supplier_id', null);
        $purchaseId = $this->request->getParam('purchase_id', null);
        if(!$purchaseId) {
            $collection = $this->productCollectionFactory->create()
            ->setPageSize(0)->setCurPage(1);
        }else {
            $collection = $this->productCollectionFactory->create()
                ->addFieldToSelect('entity_id');
            if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
                $collection->joinField(
                    'qty',
                    'cataloginventory_stock_item',
                    'qty',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'inner'
                )->getSelect()->where('at_qty.qty < 0');
            }
            $conditions = 'e.entity_id = supplier_product.product_id';
            if($supplierId)
                $conditions .= ' AND supplier_product.supplier_id = '.$supplierId;
            $collection->getSelect()->joinInner(
                array('supplier_product' => $collection->getTable('os_supplier_product')),
                $conditions,
                ['product_id', 'product_sku', 'product_supplier_sku', 'product_name', 'cost']
            );
            
            if($purchaseId){
                $productIds = $this->purchaseItemService->getProductsByPurchaseOrderId($purchaseId)
                    ->getColumnValues(PurchaseOrderItemInterface::PRODUCT_ID);
                if(!empty($productIds))
                    $collection->getSelect()
                        ->where("e.entity_id NOT IN ('" . implode("','", $productIds) . "')");
            }

            $viewModel = clone $collection->getSelect();
            $collection = $this->supplierProductService->getProductsBySupplierId($supplierId);
            $collection->getSelect()->reset()
                ->from(array('main_table' => new \Zend_Db_Expr('(' . $viewModel->__toString() . ')')));
        }
        return $collection;
    }
}