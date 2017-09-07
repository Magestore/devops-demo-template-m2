<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\BarcodeSuccess\Ui\DataProvider\Barcode\Form;

use Magestore\BarcodeSuccess\Ui\DataProvider\Barcode\DataProvider as ParentBarcodeDataProvider;

/**
 * Class BarcodeDataProvider
 * @package Magestore\BarcodeSuccess\Ui\DataProvider\History
 */
class PrintDataProvider extends ParentBarcodeDataProvider
{

    /**
     * BarcodeDataProvider constructor.
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param ProductFactory $productFactory
     * @param ImageHelper $imageHelper
     * @param StockStateInterface $stockStateInterface
     * @param StockRegistryInterface $stockRegistry
     * @param LocatorInterface $registryLocator
     * @param CatalogLocator $catalogLocator
     * @param Helper $helper
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

        $printIds = $this->locator->get('current_barcode_ids_to_print');
        if(!empty($printIds)){
            $this->collection->addFieldToFilter('id',['in' => $printIds]);
        }
    }

    public function getData()
    {
        $data = parent::getData();
        $items = $this->locator->get('print_inline_edit_qty');
        if(!empty($items) && isset($data['items'])){
            foreach ($data['items'] as $key => $item){
                if(isset($items[$item['id']]['qty'])){
                    $data['items'][$key]['qty'] = $items[$item['id']]['qty'];
                }
            }
        }
        return $data;
    }
}