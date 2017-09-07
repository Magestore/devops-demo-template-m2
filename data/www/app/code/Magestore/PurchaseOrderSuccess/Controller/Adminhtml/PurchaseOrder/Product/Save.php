<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Controller\Adminhtml\PurchaseOrder\Product;

/**
 * Class Save
 * @package Magestore\PurchaseOrderSuccess\Controller\Adminhtml\PurchaseOrder\Product
 */
class Save extends \Magestore\PurchaseOrderSuccess\Controller\Adminhtml\AbstractAction
{
    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\ItemService
     */
    protected $itemService;

    /**
     * @var \Magestore\SupplierSuccess\Service\Supplier\ProductService
     */
    protected $supplierProductService;
    
    public function __construct(
        \Magestore\PurchaseOrderSuccess\Controller\Adminhtml\Context $context,
        \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\ItemService $itemService,
        \Magestore\SupplierSuccess\Service\Supplier\ProductService $supplierProductService
    ) {
        parent::__construct($context);
        $this->itemService = $itemService;
        $this->supplierProductService = $supplierProductService;
    }
    
    /**
     * Save product to purchase order
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $productIds = $this->itemService->processIdsProductModal($params);
        $suppplierProductCollection = $this->supplierProductService
            ->getProductsBySupplierId($params['supplier_id'], $productIds);
        $supplierProductsData = $this->mappingSupplyneedQty($suppplierProductCollection->getData(),$params);
        $this->itemService->addProductToPurchaseOrder($params['purchase_id'],$supplierProductsData);
    }
    public function mappingSupplyneedQty($suppplierProducts,$params){
        if(!isset($params['supplyneed'])){
            return $suppplierProducts;
        }
        if(!$data = $this->prepareSupplyneedData()){
            return $suppplierProducts;
        }else{
            foreach($suppplierProducts as &$product){
                $product[\Magestore\PurchaseOrderSuccess\Api\Data\PurchaseOrderItemInterface::QTY_ORDERRED] =
                    isset($data[$product[\Magestore\PurchaseOrderSuccess\Api\Data\PurchaseOrderItemInterface::PRODUCT_ID]])
                        ?$data[$product[\Magestore\PurchaseOrderSuccess\Api\Data\PurchaseOrderItemInterface::PRODUCT_ID]]
                        :0;
            }
        }
        return $suppplierProducts;
    }
    public function prepareSupplyneedData(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $exportession = $objectManager->get('\Magento\Newsletter\Model\Session');
        $data = $exportession->getExportData();
        if(!$data){
            return false;
        }
        $result = array();
        foreach ($data as $item){
            $result[$item['entity_id']] = $item['supply_needs'];
        }
        return $result;
    }
}