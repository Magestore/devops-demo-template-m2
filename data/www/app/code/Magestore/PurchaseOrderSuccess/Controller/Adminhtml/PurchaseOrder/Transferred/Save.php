<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Controller\Adminhtml\PurchaseOrder\Transferred;

/**
 * Class Save
 * @package Magestore\PurchaseOrderSuccess\Controller\Adminhtml\PurchaseOrder\Transferred
 */
class Save extends \Magestore\PurchaseOrderSuccess\Controller\Adminhtml\AbstractAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magestore_PurchaseOrderSuccess::transferred_purchase_order';

    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\PurchaseOrderService
     */
    protected $purchaseOrderService;

    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\Transferred\TransferredService
     */
    protected $transferredService;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    public function __construct(
        \Magestore\PurchaseOrderSuccess\Controller\Adminhtml\Context $context,
        \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\PurchaseOrderService $purchaseOrderService,
        \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\Transferred\TransferredService $transferredService,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ){
        parent::__construct($context);
        $this->purchaseOrderService = $purchaseOrderService;
        $this->transferredService = $transferredService;
        $this->timezone = $timezone;
    }
    
    /**
     * Quotation grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        if(!$params['purchase_order_id']){
            $this->messageManager->addErrorMessage(__('Please select a purchase order to transfer received product'));
            return $resultRedirect->setPath('*/purchaseOrder/');
        }
        if(!isset($params['dynamic_grid'])){
            $this->messageManager->addErrorMessage(__('Please transfer at least one product.'));
            return $resultRedirect->setPath('*/purchaseOrder/view',['id'=>$params['purchase_order_id']]);
        }
        $transferredData = $this->transferredService->processTransferredData($params['dynamic_grid']);
        if(empty($transferredData)){
            $this->messageManager->addErrorMessage(__('Please transfer at least one product qty.'));
        }else {
            try {
                $user = $this->_auth->getUser();
                $transferStockItemData = $this->purchaseOrderService->transferProducts(
                   $transferredData, $params, $user->getUserName()
                );
                if(!empty($transferStockItemData)){
                    $transferStock = $this->transferredService->createTransferStock($params, $user->getUserName());
                    $this->transferredService->saveTransferStockData(
                        $transferStock, $transferStockItemData, $params
                    );
                    $this->messageManager->addSuccessMessage(__('Transfer product(s) successfully.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/purchaseOrder/view', ['id' => $params['purchase_order_id']]);
    }
    
}