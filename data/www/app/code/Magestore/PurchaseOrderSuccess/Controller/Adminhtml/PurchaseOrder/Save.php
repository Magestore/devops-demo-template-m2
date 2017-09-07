<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Controller\Adminhtml\PurchaseOrder;

use Magestore\PurchaseOrderSuccess\Model\PurchaseOrder\Option\Type as PurchaseOrderType;

/**
 * Class Save
 * @package Magestore\PurchaseOrderSuccess\Controller\Adminhtml\Quotation
 */
class Save extends \Magestore\PurchaseOrderSuccess\Controller\Adminhtml\AbstractAction
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magestore_PurchaseOrderSuccess::save_purchase_order';
    
    /**
     * @var \Magestore\PurchaseOrderSuccess\Api\PurchaseOrderRepositoryInterface
     */
    protected $purchaseOrderRepository;

    /**
     * @var \Magestore\SupplierSuccess\Api\SupplierRepositoryInterface
     */
    protected $supplierRepository;

    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\PurchaseOrderService
     */
    protected $purchaseService;

    /**
     * @var \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\ItemService
     */
    protected $itemService;

    
    public function __construct(
        \Magestore\PurchaseOrderSuccess\Controller\Adminhtml\Context $context,
        \Magestore\PurchaseOrderSuccess\Api\PurchaseOrderRepositoryInterface $purchaseOrderRepository,
        \Magestore\SupplierSuccess\Api\SupplierRepositoryInterface $supplierRepository,
        \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\PurchaseOrderService $purchaseService,
        \Magestore\PurchaseOrderSuccess\Service\PurchaseOrder\Item\ItemService $itemService
    ){
        parent::__construct($context);
        $this->purchaseOrderRepository = $purchaseOrderRepository;
        $this->supplierRepository = $supplierRepository;
        $this->purchaseService = $purchaseService;
        $this->itemService = $itemService;
    }
    
    /**
     * Quotation grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $id = (isset($params['purchase_order_id']) && $params['purchase_order_id']>0)?$params['purchase_order_id']:null;
        $type = (isset($params['type']) && $params['type']>0)?$params['type']:null;
        if(!$type){
            return $this->redirectGrid(1, __('This item does not exist.'));
        }
        $typeLabel = $this->getTypeLabel($type);
        if($id){
            try{
                $purchaseOrder = $this->purchaseOrderRepository->get($id, $typeLabel);
                if($purchaseOrder->getType()!=$type){
                    $message = $this->purchaseOrderRepository->getNotFoundExeptionMessage($typeLabel);
                    return $this->redirectGrid($type, $message);
                }
            }catch (\Exception $e){
                return $this->redirectGrid($type, $e->getMessage());
            }
        }else{
            $purchaseOrder = $this->_purchaseOrderFactory->create();
        }
        $purchaseOrder->addData($params)->setId($id);
        $canSendEmail = $purchaseOrder->canSendEmail();
        try{
            $purchaseOrder = $this->purchaseOrderRepository->save($purchaseOrder);
            $productsData = $this->itemService->processUpdateProductParams($params);
            if(!empty($productsData)){
                $this->itemService->updateProductDataToPurchaseOrder($purchaseOrder, $productsData);
                $this->purchaseService->updatePurchaseTotal($purchaseOrder);
            }
        }catch (\Exception $e){
            return $this->redirectForm(
                $type, 
                $id, 
                $e->getMessage(),
                \Magento\Framework\Message\MessageInterface::TYPE_ERROR
            );
        }
        if($this->getRequest()->getParam('isConfirm') == 'true'){
            $resultForward = $this->_resultForwardFactory->create();;
            $resultForward->setParams($this->getRequest()->getParams());
            return $resultForward->forward('confirm');
        }
        if($this->getRequest()->getParam('convert') == 'true'){
            $resultForward = $this->_resultForwardFactory->create();;
            $resultForward->setController('quotation');
            $resultForward->setParams($this->getRequest()->getParams());
            return $resultForward->forward('convert');
        }

        if($canSendEmail || (isset($params['sendEmail']) && $params['sendEmail'] == 'true')){
            $supplier = $this->supplierRepository->getById($params['supplier_id']);
            $this->_registry->register('current_purchase_order', $purchaseOrder);
            $this->_registry->register('current_purchase_order_supplier', $supplier);
            $sendSuccess = $this->purchaseService->sendEmailToSupplier($purchaseOrder, $supplier);
            if((isset($params['sendEmail']) && $params['sendEmail'] == 'true')) {
                if ($sendSuccess)
                    return $this->redirectForm($type, $id, __('An email has been sent to supplier'));
                else
                    return $this->redirectForm(
                        $type,
                        $id,
                        __('Could not send email to supplier'),
                        \Magento\Framework\Message\MessageInterface::TYPE_ERROR
                    );
            }
        }
        return $this->redirectForm($type, $purchaseOrder->getPurchaseOrderId(), __('%1 has been saved.', $typeLabel));
    }
    
}