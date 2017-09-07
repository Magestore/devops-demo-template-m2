<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\PurchaseOrderSuccess\Block\Adminhtml\PurchaseOrder\Edit\Button;

use Magestore\PurchaseOrderSuccess\Model\PurchaseOrder\Option\Type;
use Magestore\PurchaseOrderSuccess\Model\PurchaseOrder\Option\Status;

/**
 * Class Confirm
 * @package Magestore\PurchaseOrderSuccess\Block\Adminhtml\PurchaseOrder\Edit\Button
 */
class Confirm extends \Magestore\PurchaseOrderSuccess\Block\Adminhtml\Button\AbstractButton
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $purchaseOrder = $this->registry->registry('current_purchase_order');
        $purchaseOrderId = $purchaseOrder->getId();
        $type = $purchaseOrder->getType();
        $status = $purchaseOrder->getStatus();
        if($purchaseOrderId && $type == Type::TYPE_PURCHASE_ORDER && $status == Status::STATUS_PENDING){
//            $url = $this->getUrl('purchaseordersuccess/purchaseOrder/confirm', [
//                'purchase_order_id' => $purchaseOrderId, 'type' => $type]);
            return [
                'label' => __('Confirm Purchase Sales'),
                'class' => 'save primary',
//                'on_click' =>  sprintf("deleteConfirm(
//                        'Are you sure you want to confirm this purchase order?',
//                        '%s'
//                    )", $url)
                'on_click' => '',
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'os_purchase_order_form.os_purchase_order_form',
                                    'actionName' => 'save',
                                    'params' => [
                                        true,
                                        ['isConfirm' => true]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ];
        }
        return [];
    }
}
