<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\OrderSuccess\Block\Adminhtml\Order\Button;
use Magestore\OrderSuccess\Api\PermissionManagementInterface;

class CanceledOrderButton extends ButtonAbstract
{

    /**
     * @return array
     */
    protected function prepareButtonData()
    {
        if(!$this->isActive()) {
            return [];
        }
                
        $data = [
            'name' => __('canceled_order'),
            'label' => __('Canceled Orders'),
            'class' => 'primary',
            'url' => 'ordersuccess/canceled',
            'sort_order' => 50,
        ];
        return $data;
    }  
    
    /**
     * 
     * @return bool
     */
    protected function isActive()
    {
        $permissionManager = $this->objectManager->get('\Magestore\OrderSuccess\Api\PermissionManagementInterface');
        if($permissionManager->checkPermission(PermissionManagementInterface::CANCELED_ORDER_LIST)) {
            return true;
        }
        return false;
    }      
    
    /**
     * is current this step
     * 
     * @return bool
     */
    protected function isCurrent()
    {
        if(strtolower($this->request->getControllerName()) == 'canceled') {
            return true;
        }
        return false;
    }
    
}
