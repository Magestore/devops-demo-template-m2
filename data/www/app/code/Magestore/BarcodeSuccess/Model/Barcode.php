<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\BarcodeSuccess\Model;

use Magestore\BarcodeSuccess\Api\Data\BarcodeInterface;

class Barcode  extends \Magento\Framework\Model\AbstractModel implements BarcodeInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\BarcodeSuccess\Model\ResourceModel\Barcode');
    }

    /**
     * Barcode id
     *
     * @return int|null
     */
    public function getId(){
        return $this->_getData(self::ID);
    }

    /**
     * Set product id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id){
        return $this->setData(self::ID, $id);
    }

    /**
     * barcode
     *
     * @return string
     */
    public function getBarcode(){
        return $this->_getData(self::BARCODE);
    }

    /**
     * Set barcode
     *
     * @param string $barcode
     * @return $this
     */
    public function setBarcode($barcode){
        return $this->setData(self::BARCODE, $barcode);
    }

    /**
     * barcode name
     *
     * @return string|null
     */
    public function getQty(){
        return $this->_getData(self::QTY);
    }

    /**
     * Set barcode qty
     *
     * @param string $qty
     * @return $this
     */
    public function setQty($qty){
        return $this->setData(self::QTY, $qty);
    }

    /**
     * barcode product id
     *
     * @return string|null
     */
    public function getProductId(){
        return $this->_getData(self::PRODUCT_ID);
    }

    /**
     * Set barcode product id
     *
     * @param string $productId
     * @return $this
     */
    public function setProductId($productId){
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * barcode product sku
     *
     * @return string|null
     */
    public function getProductSku(){
        return $this->_getData(self::PRODUCT_SKU);
    }

    /**
     * Set barcode product sku
     *
     * @param string $productSku
     * @return $this
     */
    public function setProductSku($productSku){
        return $this->setData(self::PRODUCT_SKU, $productSku);
    }

    /**
     * barcode supplier id
     *
     * @return string|null
     */
    public function getSupplierId(){
        return $this->_getData(self::SUPPLIER_ID);
    }

    /**
     * Set barcode supplier id
     *
     * @param string $supplierId
     * @return $this
     */
    public function setSupplierId($supplierId){
        return $this->setData(self::SUPPLIER_ID, $supplierId);
    }

    /**
     * barcode supplier code
     *
     * @return string|null
     */
    public function getSupplierCode(){
        return $this->_getData(self::SUPPLIER_CODE);
    }

    /**
     * Set barcode supplier code
     *
     * @param string $supplierCode
     * @return $this
     */
    public function setSupplierCode($supplierCode){
        return $this->setData(self::SUPPLIER_CODE, $supplierCode);
    }

    /**
     * barcode $purchasedId
     *
     * @return string|null
     */
    public function getPurchasedId(){
        return $this->_getData(self::PURCHASED_ID);
    }

    /**
     * Set barcode $purchasedId
     *
     * @param string $purchasedId
     * @return $this
     */
    public function setPurchasedId($purchasedId){
        return $this->setData(self::PURCHASED_ID, $purchasedId);
    }

    /**
     * barcode $purchasedTime
     *
     * @return string|null
     */
    public function getPurchasedTime(){
        return $this->_getData(self::PURCHASED_TIME);
    }

    /**
     * Set barcode $purchasedTime
     *
     * @param string $purchasedTime
     * @return $this
     */
    public function setPurchasedTime($purchasedTime){
        return $this->setData(self::PURCHASED_TIME, $purchasedTime);
    }


    /**
     * barcode $historyId
     *
     * @return string|null
     */
    public function getCreatedAt(){
        return $this->_getData(self::CREATE_AT);
    }

    /**
     * Set barcode $createdAt
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt){
        return $this->setData(self::CREATE_AT, $createdAt);
    }
}