<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\BarcodeSuccess\Helper;

/**
 * Class Data
 * @package Magestore\BarcodeSuccess\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $_random;

    /**
     * @var \Magestore\BarcodeSuccess\Model\ResourceModel\Barcode
     */
    public $resource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     *
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    /**
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrlBuilder;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Math\Random $random,
        \Magestore\BarcodeSuccess\Model\ResourceModel\Barcode $barcodeResource,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Backend\Model\UrlInterface $backendUrlBuilder,
        \Magestore\BarcodeSuccess\Model\Locator\LocatorInterface $locator,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        parent::__construct($context);
        $this->_random = $random;
        $this->resource = $barcodeResource;
        $this->_localeDate = $localeDate;
        $this->_backendUrlBuilder = $backendUrlBuilder;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->locator = $locator;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $string
     * @return mixed
     */
    public function generateCode($pattern = "")
    {
        $pattern = ($pattern) ? $pattern : $this->getStoreConfig('barcodesuccess/general/barcode_pattern');
        $pattern = strtoupper($pattern);
        $barcode = preg_replace_callback('#\[([AN]{1,2})\.([0-9]+)\]#', array($this, 'convertExpression'), $pattern);
        $barcodeModel = $this->getModel('\Magestore\BarcodeSuccess\Api\Data\BarcodeInterface');
        $this->resource->load($barcodeModel, $barcode, 'barcode');
        if ($barcodeModel->getId()) {
            $count = $this->locator->get('barcode_existing_count');
            $count = ($count) ? $count++ : 1;
            $this->locator->add('barcode_existing_count', $count);
            if ($count == 5) {
                $barcode = false;
                $this->locator->remove('barcode_existing_count');
            } else {
                $barcode = $this->generateCode($pattern);
            }
        } else {
            $this->locator->remove('barcode_existing_count');
        }
        return $barcode;
    }

    /**
     * @param $string
     * @return mixed
     */
    public function generateBarcode($pattern = '')
    {
        $pattern = $this->getStoreConfig('barcodesuccess/general/barcode_pattern');
        $pattern = strtoupper($pattern);
        $barcode = preg_replace_callback('#\[([AN]{1,2})\.([0-9]+)\]#', array($this, 'convertExpression'), $pattern);
        $barcodeCollection = $this->_objectManager->create('\Magestore\BarcodeSuccess\Model\ResourceModel\Barcode\Collection');
        $barcodeCollection->addFieldToFilter('barcode', $barcode);
        $generated = $this->locator->get('generated_barcodes');
        $generated = (isset($generated)) ? $generated : [];
        if (count($barcodeCollection) > 0 || (is_array($generated) && in_array($barcode, $generated))) {
            $count = $this->locator->get('barcode_existing_count');
            $count = (isset($count)) ? $count + 1 : 1;
            $this->locator->add('barcode_existing_count', $count);
            if ($count == 5) {
                $barcode = false;
                $this->locator->remove('barcode_existing_count');
            } else {
                $barcode = $this->generateBarcode($pattern);
            }
        } else {
            $generated[] = $barcode;
            $this->locator->remove('barcode_existing_count');
            $this->locator->add('generated_barcodes', $generated);
        }
        return $barcode;
    }

    /**
     * @param $string
     * @return mixed
     */
    public function generateExampleCode($pattern = "")
    {
        $pattern = ($pattern) ? $pattern : $this->getStoreConfig('barcodesuccess/general/barcode_pattern');
        $barcode = preg_replace_callback('#\[([AN]{1,2})\.([0-9]+)\]#', array($this, 'convertExpression'), $pattern);
        return $barcode;
    }

    /**
     * @param $param
     * @return mixed
     */
    public function convertExpression($param)
    {
        $alphabet = (strpos($param[1], 'A')) === false ? '' : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphabet .= (strpos($param[1], 'N')) === false ? '' : '0123456789';
        return $this->_random->getRandomString($param[2], $alphabet);
    }

    /**
     *
     * @param string $path
     * @return string
     */
    public function getStoreConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * string class name
     * @return Model
     */
    public function getModel($class)
    {
        return $this->_objectManager->create($class);
    }

    /**
     * @param $message
     * @param string $type
     */
    public function addLog($message, $type = '')
    {
        switch ($type) {
            case 'info':
                $this->_logger->info($message);
                break;
            case 'debug':
                $this->_logger->debug($message);
                break;
            case 'info':
                $this->_logger->info($message);
                break;
            case 'notice':
                $this->_logger->notice($message);
                break;
            case 'warning':
                $this->_logger->warning($message);
                break;
            case 'error':
                $this->_logger->error($message);
                break;
            case 'emergency':
                $this->_logger->emergency($message);
                break;
            case 'critical':
                $this->_logger->critical($message);
                break;
            case 'alert':
                $this->_logger->alert($message);
                break;
            default:
                $this->_logger->error($message);
                break;
        }
    }

    /**
     * @param $data
     * @param string $format
     * @return mixed
     */
    public function formatDate($data, $format = '')
    {
        $format = ($format == '') ? 'M d,Y H:i:s a' : $format;
        return $this->_localeDate->date(new \DateTime($data))->format($format);
    }

    /**
     *
     * @param string $data
     * @return string
     */
    public function formatPrice($data)
    {
        $helper = $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data');
        return $helper->currency($data, true, false);
    }

    /**
     *
     * @param string $str
     * @return string
     */
    public function htmlEscape($str)
    {
        return htmlspecialchars($str);
    }

    /**
     * @param $path
     * @param array $params
     * @return mixed
     */
    public function getUrl($path, $params = [])
    {
        return $this->_getUrl($path, $params);
    }

    /**
     * @param $path
     * @param array $params
     * @return mixed
     */
    public function getBackendUrl($path, $params = [])
    {
        return $this->_backendUrlBuilder->getUrl($path, $params);
    }


    /**
     * @param $productId
     * @return array
     */
    public function getWarehousesStock($productId)
    {
        $model = $this->getModel('\Magestore\InventorySuccess\Model\Warehouse\WarehouseStockRegistry');
        $stockWarehouses = $model->getStockWarehouses($productId);
        $stocks = [];
        if ($stockWarehouses->getSize() > 0) {
            $totalQty = 0;
            $totalAvailable = 0;
            $totalQtyToShip = 0;
            foreach ($stockWarehouses as $warehouse) {
                $stocks[$warehouse->getData('warehouse_code')]['link'] = $this->getBackendUrl('inventorysuccess/warehouse/edit', ['id' => $warehouse->getData('website_id')]);
                $stocks[$warehouse->getData('warehouse_code')]['name'] = $warehouse->getData('warehouse_name');
                $stocks[$warehouse->getData('warehouse_code')]['total_qty'] = floatval($warehouse->getData('total_qty'));
                $stocks[$warehouse->getData('warehouse_code')]['qty_to_ship'] = floatval($warehouse->getData('qty_to_ship'));
                $stocks[$warehouse->getData('warehouse_code')]['available_qty'] = floatval(floatval($warehouse->getData('total_qty')) - floatval($warehouse->getData('qty_to_ship')));
                $stocks[$warehouse->getData('warehouse_code')]['shelf_location'] = floatval($warehouse->getData('shelf_location'));
                $totalQty += $stocks[$warehouse->getData('warehouse_code')]['total_qty'];
                $totalQtyToShip += $stocks[$warehouse->getData('warehouse_code')]['qty_to_ship'];
                $totalAvailable += $stocks[$warehouse->getData('warehouse_code')]['available_qty'];
            }
            $stocks['all']['name'] = __('All');
            $stocks['all']['total_qty'] = $totalQty;
            $stocks['all']['qty_to_ship'] = $totalQtyToShip;
            $stocks['all']['available_qty'] = $totalAvailable;
        }
        return $stocks;
    }

    /**
     *
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . $file;
    }
}