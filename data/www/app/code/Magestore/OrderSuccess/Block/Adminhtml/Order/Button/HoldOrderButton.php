<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\OrderSuccess\Block\Adminhtml\Order\Button;
use Magestore\OrderSuccess\Api\PermissionManagementInterface;

class HoldOrderButton extends ButtonAbstract
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
            'name' => __('hold_order'),
            'label' => __('Hold Orders'),
            'class' => 'primary',
            'url' => 'ordersuccess/hold',
            'sort_order' => 30,
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
        if($permissionManager->checkPermission(PermissionManagementInterface::ONHOLD_ORDER_LIST)) {
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
        if(strtolower($this->request->getControllerName()) == 'hold') {
            return true;
        }
        return false;
    }
    
}
