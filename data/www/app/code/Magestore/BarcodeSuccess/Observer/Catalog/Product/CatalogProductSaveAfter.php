<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\BarcodeSuccess\Observer\Catalog\Product;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CatalogProductSaveAfter implements ObserverInterface
{
    /**
     * @var \Magestore\BarcodeSuccess\Model\BarcodeFactory
     */
    protected $barcodeFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magestore\BarcodeSuccess\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * CatalogProductSaveAfter constructor.
     * @param \Magestore\BarcodeSuccess\Model\BarcodeFactory $barcodeFactory
     */
    public function __construct(
        \Magestore\BarcodeSuccess\Model\BarcodeFactory $barcodeFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\BarcodeSuccess\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->barcodeFactory = $barcodeFactory;
        $this->request = $request;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $barcode = $this->request->getParam('os_barcode');
        if(isset($barcode) && !empty($barcode)){
            $product = $observer->getData('product');
            $barcodeModel = $this->barcodeFactory->create();
            $barcodeId = $barcodeModel->getCollection()
                ->addFieldToFilter('barcode', $barcode)
                ->addFieldToFilter('product_id', ['neq' => $product->getId()])
                ->setPageSize(1)
                ->setCurPage(1)
                ->getFirstItem()
                ->getId();
            if($barcodeId)
                return $this->messageManager->addErrorMessage(__('Barcode has been existed.'));
            $barcodeModel->getResource()->load($barcodeModel, $product->getId(), 'product_id');
            if($barcode!=$barcodeModel->getBarcode()) {
                if ($barcodeModel->getId()) {
                    $barcodeModel->setBarcode($barcode);
                } else {
                    $historyId = $this->saveHistory();
                    $barcodeData = [
                        'product_id' => $product->getId(),
                        'barcode' => $barcode,
                        'qty' => 1,
                        'product_sku' => $product->getSku(),
                        'supplier_code' => '',
                        'history_id' => $historyId
                    ];
                    $barcodeModel->addData($barcodeData);
                }
                try{
                    $barcodeModel->getResource()->save($barcodeModel);
                }catch (\Exception $e){
                    $this->helper->addLog($e->getMessage());
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        }
    }

    /**
     * @param $totalQty
     * @param string $reason
     * @return string
     */
    protected function saveHistory(){
        $historyId = '';
        $history = $this->helper->getModel('Magestore\BarcodeSuccess\Api\Data\HistoryInterface');
        $historyResource = $this->helper->getModel('Magestore\BarcodeSuccess\Model\ResourceModel\History');
        $adminSession = $this->helper->getModel('Magento\Backend\Model\Auth\Session');
        try{
            $admin = $adminSession->getUser();
            $adminId = ($admin)?$admin->getId():0;
            $history->setData('type',\Magestore\BarcodeSuccess\Model\History::GENERATED);
            $history->setData('reason',__('Create barcode for product'));
            $history->setData('created_by',$adminId);
            $history->setData('total_qty',1);
            $historyResource->save($history);
            $historyId =  $history->getId();
        }catch (\Exception $e){
            $this->helper->addLog($e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $historyId;
    }
}