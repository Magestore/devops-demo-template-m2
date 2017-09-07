<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item;

use Magestore\PurchaseOrderSuccess\Api\Data\PurchaseOrderItemInterface;
use Magestore\SupplierSuccess\Api\Data\SupplierProductInterface;
use Magestore\SupplierSuccess\Service\Supplier\ProductService;

/**
 * Class ItemService
 * @package Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Product
 */
class ImportService 
{
    /**
     * CSV Processor
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var ItemService
     */
    protected $itemService;

    /**
     * @var \Magestore\SupplierSuccess\Service\Supplier\ProductService
     */
    protected $supplierProductService;

    /**
     * @var \Magestore\PurchaseOrderSuccess\Api\PurchaseOrderItemRepositoryInterface
     */
    protected $purchaseOrderItemRepository;

    /**
     * @var \Magestore\PurchaseOrderSuccess\Model\PurchaseOrder\ItemFactory
     */
    protected $purchaseItemFactory;

    /**
     * @var array
     */
    protected $updateFields = [
        PurchaseOrderItemInterface::COST,
        PurchaseOrderItemInterface::TAX,
        PurchaseOrderItemInterface::DISCOUNT,
        PurchaseOrderItemInterface::QTY_ORDERRED
    ];

    /**
     * ProductService constructor.
     * @param \Magestore\PurchaseOrderSuccess\Model\ResourceModel\PurchaseOrder\Item\CollectionFactory $itemCollectioFactory
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        ItemService $itemService,
        \Magestore\SupplierSuccess\Service\Supplier\ProductService $supplierProductService,
        \Magestore\PurchaseOrderSuccess\Api\PurchaseOrderItemRepositoryInterface $purchaseOrderItemRepository,
        \Magestore\PurchaseOrderSuccess\Model\PurchaseOrder\ItemFactory $purchaseItemFactory
    ){
        $this->csvProcessor = $csvProcessor;
        $this->itemService = $itemService;
        $this->supplierProductService = $supplierProductService;
        $this->purchaseOrderItemRepository = $purchaseOrderItemRepository;
        $this->purchaseItemFactory = $purchaseItemFactory;
    }

    /**
     * @param $dataRow
     * @param $purchaseId
     * @param $supplierId
     * @return bool
     */
    public function savePurchaseItem($dataRow, $purchaseId, $supplierId)
    {
        $productSku = $dataRow[0];
        $cost = $dataRow[1];
        $tax = $dataRow[2];
        $discount = $dataRow[3];
        $qtyOrderred = abs($dataRow[4]);
        $productId = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magestore\SupplierSuccess\Service\Supplier\ProductService')
            ->getProductsBySupplierId($supplierId)
            ->addFieldToFilter('product_sku', $productSku)
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem()
            ->getProductId();

        /**
         * @var \Magestore\PurchaseOrderSuccess\Api\Data\PurchaseOrderItemInterface $item
         */
        $item = $this->itemService->getProductsByPurchaseOrderId($purchaseId, [$productId])
            ->getFirstItem();
        if ($item->getId()) {
            $item->setCost($cost)->setTax($tax)->setDiscount($discount)->setQtyOrderred($qtyOrderred);
            try {
                $this->purchaseOrderItemRepository->save($item);
            }catch (\Exception $e){
                return false;
            }
            return true;
        }
        return $this->saveNewItem($purchaseId, $supplierId, $productId, $cost, $tax, $discount, $qtyOrderred);
    }

    /**
     * @param $purchaseId
     * @param $supplierId
     * @param $productId
     * @param $cost
     * @param $tax
     * @param $discount
     * @param $qtyOrderred
     * @return bool
     */
    public function saveNewItem($purchaseId, $supplierId, $productId, $cost, $tax, $discount, $qtyOrderred){
        /**
         * @var \Magestore\SupplierSuccess\Api\Data\SupplierProductInterface $product
         */
        $product = $this->supplierProductService->getProductsBySupplierId($supplierId, [$productId])
            ->getFirstItem();
        if(!$product->getId())
            return false;
        $item = $this->purchaseItemFactory->create();
        $item->setPurchaseOrderId($purchaseId)
            ->setProductId($productId)
            ->setProductSku($product->getProductSku())
            ->setProductName($product->getProductName())
            ->setProductSupplierSku($product->getProductSupplierSku())
            ->setQtyOrderred($qtyOrderred)
            ->setOriginalCost($product->getCost())
            ->setCost($cost)
            ->setTax($tax)
            ->setDiscount($discount)
            ->setId(null);
        try {
            $this->purchaseOrderItemRepository->save($item);
        }catch (\Exception $e){
            return false;
        }
        return true;
    }

    /**
     * @param $file
     * @param int $purchaseId
     * @param int $supplierId
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function import($file, $purchaseId, $supplierId){
        if (!isset($file['tmp_name'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }
        $success = 0;
        $importRawData = $this->csvProcessor->getData($file['tmp_name']);
        $fileFields = $importRawData[0];
        $validFields = $this->filterFileFields($fileFields);
        $invalidFields = array_diff_key($fileFields, $validFields);
        $importData = $this->filterImportData($importRawData, $invalidFields, $validFields);
        foreach ($importData as $rowIndex => $dataRow) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }
            if($this->savePurchaseItem($dataRow, $purchaseId, $supplierId)){
                $success++;
            }
        }
        return $success;
    }

    /**
     * Filter file fields (i.e. unset invalid fields)
     *
     * @param array $fileFields
     * @return string[] filtered fields
     */
    protected function filterFileFields(array $fileFields)
    {
        $filteredFields = $this->getRequiredCsvFields();
        $requiredFieldsNum = count($this->getRequiredCsvFields());
        $fileFieldsNum = count($fileFields);

        // process title-related fields that are located right after required fields with store code as field name)
        for ($index = $requiredFieldsNum; $index < $fileFieldsNum; $index++) {
            $titleFieldName = $fileFields[$index];
            $filteredFields[$index] = $titleFieldName;
        }

        return $filteredFields;
    }

    public function getRequiredCsvFields()
    {
        // indexes are specified for clarity, they are used during import
        return [
            0 => 'PRODUCT_SKU',
            1 => 'COST',
            2 => 'TAX',
            3 => 'DISCOUNT',
            4 => 'QTY_ORDERRED'
        ];
    }

    /**
     * @param array $rawData
     * @param array $invalidFields
     * @param array $validFields
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function filterImportData(array $rawData, array $invalidFields, array $validFields)
    {
        $validFieldsNum = count($validFields);
        foreach ($rawData as $rowIndex => $dataRow) {
            // skip empty rows
            if (count($dataRow) <= 1) {
                unset($rawData[$rowIndex]);
                continue;
            }
            // unset invalid fields from data row
            foreach ($dataRow as $fieldIndex => $fieldValue) {
                if (isset($invalidFields[$fieldIndex])) {
                    unset($rawData[$rowIndex][$fieldIndex]);
                }
            }
            // check if number of fields in row match with number of valid fields
            if (count($rawData[$rowIndex]) != $validFieldsNum) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file format.'));
            }
        }
        return $rawData;
    }
}