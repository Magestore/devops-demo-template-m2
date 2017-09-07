<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\BarcodeSuccess\Ui\DataProvider\Template;

use Magestore\BarcodeSuccess\Ui\DataProvider\AbstractProvider;

/**
 * Class DataProvider
 * @package Magestore\BarcodeSuccess\Ui\DataProvider
 */
class DataProvider extends AbstractProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var string
     */
    protected $type_provider;

    /**
     * @var string
     */
    protected $barcodeTemplate;

    /**
     * Generate constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param BarcodeTemplateInterface $barcodeTemplate
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
        \Magestore\BarcodeSuccess\Model\ResourceModel\Template\CollectionFactory $collectionFactory,
        \Magestore\BarcodeSuccess\Api\Data\BarcodeTemplateInterface $barcodeTemplate,
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
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create();
        if(isset($data['type_provider']) && $data['type_provider']) {
            $this->type_provider = $data['type_provider'];
        }
        $this->barcodeTemplate = $barcodeTemplate;
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        if($this->type_provider == 'form') {
            $items = $this->collection->getItems();
            foreach ($items as $item) {
                $this->loadedData[$item->getId()] = $item->getData();
                $this->loadedData[$item->getId()]['preview'] = $item->getPreviewData();
            }
            if (!empty($data)) {
                $item = $this->collection->getNewEmptyItem();
                $item->setData($data);
                $this->loadedData[$item->getId()] = $item->getData();
                $this->loadedData[$item->getId()]['preview'] = $item->getPreviewData();
            }
            if(count($items) == 0){
                $item = $this->collection->getNewEmptyItem();
                $this->loadedData[$item->getId()] = $item->getData();
                $this->loadedData[$item->getId()]['preview'] = $item->getPreviewData();
            }
        }else{
            $this->loadedData = $this->getCollection()->toArray();
        }
        return $this->loadedData;
    }
}