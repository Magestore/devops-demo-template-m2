<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\BarcodeSuccess\Controller\Adminhtml\Index;

use Magento\Ui\Component\MassAction\Filter;

/**
 * Class PrintBarcode
 * @package Magestore\BarcodeSuccess\Controller\Adminhtml\Index
 */
class PrintBarcode extends \Magestore\BarcodeSuccess\Controller\Adminhtml\AbstractIndex
{
    
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magestore_BarcodeSuccess::print_barcode';    
    
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var TemplateResource
     */
    protected $templateResource;

    /**
     * PrintBarcode constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Data $data
     * @param LocatorInterface $locator
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magestore\BarcodeSuccess\Helper\Data $data,
        \Magestore\BarcodeSuccess\Model\Locator\LocatorInterface $locator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magestore\BarcodeSuccess\Model\ResourceModel\Template $templateResource
    ) {
        parent::__construct($context, $resultPageFactory, $data, $locator);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->templateResource = $templateResource;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $defaultTemplateId = $this->helper->getStoreConfig('barcodesuccess/general/default_barcode_template');
        $resultJson = $this->resultJsonFactory->create();
        $templateId = $this->getRequest()->getParam('type', $defaultTemplateId);
        $printQty = $this->getRequest()->getParam('qty');
        $excluded = $this->getRequest()->getParam(Filter::EXCLUDED_PARAM);
        $selectedBarcodeIds = $this->getRequest()->getParam(Filter::SELECTED_PARAM);
        $barcodeCollection = $this->helper->getModel('Magestore\BarcodeSuccess\Model\ResourceModel\Barcode\Collection');
        if ($this->getRequest()->getParam('filters')) {
            $filters = $this->getRequest()->getParam('filters');
            foreach ($filters as $key => $filter){
                $barcodeCollection->addFieldToFilter($key, $filter);
            }
        }

        if($excluded == 'false' || empty($selectedBarcodeIds) || (empty($excluded) && empty($selectedBarcodeIds)) ){
            $selectedBarcodeIds = $this->locator->get('current_barcode_ids_to_print');
        }
        if(is_array($excluded)){
            $barcodeCollection->addFieldToFilter('id', ['nin' => $excluded]);
        }
        $barcodeCollection->addFieldToFilter('id', ['in' => $selectedBarcodeIds]);

        $savedItems = $this->locator->get('print_inline_edit_qty');
        if(!empty($savedItems)){
            foreach ($barcodeCollection as $barcode){
                foreach ($savedItems as $savedItem){
                    if($savedItem['id'] == $barcode->getId()){
                        $barcode->setData('qty', $savedItem['qty']);
                        break;
                    }
                }
            }
        }
        if($printQty){
            foreach ($barcodeCollection as $barcode){
                $barcode->setData('qty', $printQty);
            }
        }
        $html = "";
        $barcodes = [];
        foreach ($barcodeCollection as $barcode){
            $barcodes[$barcode->getId()] = $barcode->getData();
        }
        $block = $this->createBlock('Magestore\BarcodeSuccess\Block\Barcode\Container\Template','','Magestore_BarcodeSuccess::barcode/print/template.phtml');
        $block->setData('barcodes', $barcodes);
        if(isset($templateId)){
            $template = $this->helper->getModel('Magestore\BarcodeSuccess\Api\Data\BarcodeTemplateInterface');
            $this->templateResource->load($template, $templateId);
            $data = $template->getData();
            $block->setData('template_data', $data);
        }
        $html .= $block->toHtml();
        $resultJson->setData([
            'html' => $html,
            'success' => true
        ]);
        return $resultJson;
    }
    
}
