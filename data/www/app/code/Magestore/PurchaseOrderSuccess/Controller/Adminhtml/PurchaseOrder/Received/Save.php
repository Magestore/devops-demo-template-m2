<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Controller\Adminhtml\PurchaseOrder\Received;

/**
 * Class Save
 * @package Magestore\PurchaseOrderSuccess\Controller\Adminhtml\Quotation
 */
class Save extends \Magestore\PurchaseOrderSuccess\Controller\Adminhtml\AbstractAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magestore_PurchaseOrderSuccess::received_purchase_order';

    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\PurchaseOrderService
     */
    protected $purchaseOrderService;

    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\Received\ReceivedService
     */
    protected $receivedService;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    public function __construct(
        \Magestore\PurchaseOrderSuccess\Controller\Adminhtml\Context $context,
        \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\PurchaseOrderService $purchaseOrderService,
        \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\Received\ReceivedService $receivedService,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ){
        parent::__construct($context);
        $this->purchaseOrderService = $purchaseOrderService;
        $this->receivedService = $receivedService;
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
        if(!isset($params['purchase_order_id'])){
            $this->messageManager->addErrorMessage(__('Please select a purchase order to received product'));
            return $resultRedirect->setPath('*/purchaseOrder/');
        }
        if(!isset($params['dynamic_grid'])){
            $this->messageManager->addErrorMessage(__('Please receive at least one product.'));
            return $resultRedirect->setPath('*/purchaseOrder/view',['id'=>$params['purchase_order_id']]);
        }
        $receivedData = $this->receivedService->processReceivedData($params['dynamic_grid']);
        try {
            $user = $this->_auth->getUser();
            $this->purchaseOrderService->receiveProducts(
                $params['purchase_order_id'], $receivedData, $params['received_at'], $user->getUserName()
            );
            $this->messageManager->addSuccessMessage(__('Receive products successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('*/purchaseOrder/view', ['id' => $params['purchase_order_id']]);
    }

}