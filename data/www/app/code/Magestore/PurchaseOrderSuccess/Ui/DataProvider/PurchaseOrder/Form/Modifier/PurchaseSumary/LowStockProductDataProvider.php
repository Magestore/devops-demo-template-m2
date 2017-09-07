<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Ui\DataProvider\PurchaseOrder\Form\Modifier\PurchaseSumary;

use Magestore\SupplierSuccess\Api\Data\SupplierProductInterface;
use Magestore\PurchaseOrderSuccess\Api\Data\PurchaseOrderItemInterface;

/**
 * Class LowStockProductDataProvider
 * @package Magestore\PurchaseOrderSuccess\Ui\DataProvider\PurchaseOrder\Form\Modifier\PurchaseSumary
 */
class LowStockProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magestore\SupplierSuccess\Model\ResourceModel\Supplier\Product\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magestore\SupplierSuccess\Service\Supplier\ProductService
     */
    protected $supplierProductService;

    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\ItemService
     */
    protected $purchaseItemService;

    /**
     * LowStockProductDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magestore\SupplierSuccess\Service\Supplier\ProductService $supplierProductService
     * @param \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\ItemService $purchaseItemService
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\SupplierSuccess\Service\Supplier\ProductService $supplierProductService,
        \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\ItemService $purchaseItemService,
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->supplierProductService = $supplierProductService;
        $this->purchaseItemService = $purchaseItemService;
        $this->collection = $this->getLowStockProductCollection();
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
        return $items;
    }

    /**
     * @return \Magestore\SupplierSuccess\Model\ResourceModel\Supplier\Product\Collection $collection
     */
    public function getLowStockProductCollection()
    {
        $purchaseId = $this->request->getParam('purchase_id', null);
        $supplierId = $this->request->getParam('supplier_id', null);
        $notificationId = $this->request->getParam('notification_id', null);
        if(!$notificationId) {
            $collection = $this->supplierProductService->getProductsBySupplierId($supplierId, [null]);
            return $collection;
        }
        $collection = $this->supplierProductService->getProductsBySupplierId($supplierId);
        if ($purchaseId) {
            $collection->getSelect()->joinInner(
                array('lowstock_product' => $collection->getTable('os_lowstock_notification_product')),
                'main_table.product_id = lowstock_product.product_id 
                AND lowstock_product.notification_id = '. $notificationId,
                ['current_qty']
            );
            
            $productIds = $this->purchaseItemService->getProductsByPurchaseOrderId($purchaseId)
                ->getColumnValues(PurchaseOrderItemInterface::PRODUCT_ID);
            if (!empty($productIds))
                $collection->addFieldToFilter('main_table.'.SupplierProductInterface::PRODUCT_ID, ['nin' => $productIds]);
        }
        return $collection;
    }

    /**
     * Set Query limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     */
    public function setLimit($offset, $size)
    {
        $this->getCollection()->setPageSize($size);
        $this->getCollection()->setCurPage($offset);
    }

    /**
     * @inheritdoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if(in_array($filter->getField(),['product_id', 'product_sku', 'product_supplier_sku', 'product_name']))
            $filter->setField('main_table.'.$filter->getField());
        return parent::addFilter($filter);
    }
}