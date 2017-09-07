<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\BarcodeSuccess\Ui\DataProvider\History;

use Magestore\BarcodeSuccess\Ui\DataProvider\Barcode\DataProvider as ParentBarcodeDataProvider;


/**
 * Class BarcodeDataProvider
 * @package Magestore\BarcodeSuccess\Ui\DataProvider\History
 */
class BarcodeDataProvider extends ParentBarcodeDataProvider
{
    /**
     * BarcodeDataProvider constructor.
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param \Magento\Framework\Api\Search\ReportingInterface $reporting
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param UrlInterface $urlBuilder
     * @param Helper $helper
     * @param LocatorInterface $locator
     * @param CollectionFactory $collectionFactory
     * @param ProductFactory $productFactory
     * @param ImageHelper $imageHelper
     * @param StockStateInterface $stockStateInterface
     * @param StockRegistryInterface $stockRegistry
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\Api\Search\ReportingInterface $reporting,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magestore\BarcodeSuccess\Helper\Data $helper,
        \Magestore\BarcodeSuccess\Model\Locator\LocatorInterface $locator,
        \Magestore\BarcodeSuccess\Model\ResourceModel\Barcode\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\CatalogInventory\Api\StockStateInterface $stockStateInterface,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $urlBuilder,
            $helper,
            $locator,
            $collectionFactory,
            $productFactory,
            $imageHelper,
            $stockStateInterface,
            $stockRegistry,
            $meta,
            $data
        );
        $historyId = $this->locator->getCurrentBarcodeHistory();
        if($historyId !== false){
            $this->collection->addFieldToFilter('history_id',$historyId);
        }
    }
}