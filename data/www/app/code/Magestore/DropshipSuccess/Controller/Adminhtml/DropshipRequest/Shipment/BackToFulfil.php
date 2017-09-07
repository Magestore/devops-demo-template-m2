<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\DropshipSuccess\Controller\Adminhtml\DropshipRequest\Shipment;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;

/**
 * Class BackToFulfil
 * @package Magestore\DropshipSuccess\Controller\Adminhtml\DropshipRequest\Shipment
 */
class BackToFulfil extends \Magento\Shipping\Controller\Adminhtml\Order\Shipment\Save
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magestore_DropshipSuccess::back_dropship';

    /**
     * @var \Magestore\DropshipSuccess\Service\DropshipRequestService
     */
    protected $dropshipRequestService;
    
    /**
     * @param Action\Context $context
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     * @param \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator
     * @param ShipmentSender $shipmentSender
     */
    public function __construct(
        Action\Context $context,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator,
        ShipmentSender $shipmentSender,
        \Magestore\DropshipSuccess\Service\DropshipRequestService $dropshipRequestService
    ) {
        parent::__construct($context, $shipmentLoader, $labelGenerator, $shipmentSender);
        $this->dropshipRequestService = $dropshipRequestService;
    }
    
    /**
     * View order detail
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $requestId = $this->getRequest()->getParam('dropship_request_id');
        if (!$requestId) {
            $this->messageManager->addErrorMessage(__('Cannot cancel the dropship request.'));
            return $resultRedirect->setPath('dropshipsuccess/dropshiprequest/edit', ['_current' => true]);
        }
        try {
            $this->dropshipRequestService->backToPrepareFulfil($requestId);
            $this->messageManager->addSuccessMessage(__('The dropship request has been canceled'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->messageManager->addErrorMessage(__('Cannot cancel the dropship request.'));
        }
        return $resultRedirect->setPath('dropshipsuccess/dropshiprequest/edit', ['_current' => true]);
    }
}
