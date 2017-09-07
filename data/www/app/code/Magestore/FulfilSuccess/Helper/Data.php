<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\FulfilSuccess\Helper;

/**
 * Class Data
 * @package Magestore\FulfilSuccess\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const BARCODE_ATTRIBUTE_CONFIG_PATH = "fulfilsuccess/scanning/barcode";

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param Context $context
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context){
        parent::__construct($context);
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     *
     * @param string $path
     * @return string
     */
    public function getStoreConfig($path){
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get barcode attribute
     * @return mixed
     */
    public function getProductBarcodeAttribute(){
        return $this->getStoreConfig(self::BARCODE_ATTRIBUTE_CONFIG_PATH);
    }

    /**
     * @param $collection
     * @param $barcodeFieldKey
     * @param $productId
     * @return mixed
     */
    public function getProductBarcodes($productId){
        $barcodes = [];
        $barcodeAttribute = $this->getProductBarcodeAttribute();
        if($productId){
            if($barcodeAttribute){
                $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
                $resource = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product');
                $resource->load($product, $productId);
                if($product->getId() && $product->getData($barcodeAttribute)){
                    $barcodes[] = $product->getData($barcodeAttribute);
                }
            }
            if($this->isModuleOutputEnabled('Magestore_BarcodeSuccess')){
                $barcodeCollection = $this->_objectManager->create('Magestore\BarcodeSuccess\Model\ResourceModel\Barcode\Collection');
                if($barcodeCollection){
                    $barcodeCollection->addFieldToFilter(\Magestore\BarcodeSuccess\Api\Data\BarcodeInterface::PRODUCT_ID, $productId);
                    $barcodeCollection->addFieldToSelect(\Magestore\BarcodeSuccess\Api\Data\BarcodeInterface::BARCODE);
                    $osBarcodes = $barcodeCollection->getColumnValues(\Magestore\BarcodeSuccess\Api\Data\BarcodeInterface::BARCODE);
                    if(!empty($osBarcodes)){
                        $barcodes = array_merge_recursive($barcodes, $osBarcodes);
                    }
                }
            }
        }
        return implode('||',$barcodes);
    }
}