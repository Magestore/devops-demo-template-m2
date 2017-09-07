<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\FulfilSuccess\Service\Location;

use Magestore\FulfilSuccess\Service\Location\LocationServiceInterface;
use Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface;

class LocationService implements LocationServiceInterface
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;
    
    /**
     * @var array
     */
    protected $allowedWarehouses = [];
    
    /**
     * @var PermissionManagementInterface 
     */
    protected $permissionManagement;
    
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory
     */
    protected $collectionFactory;
    
    public function __construct(
        \Magento\Backend\Model\Session $session,
        PermissionManagementInterface $permissionManagement,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory $collectionFactory
    )
    {
        $this->session = $session;
        $this->permissionManagement = $permissionManagement;
        $this->collectionFactory = $collectionFactory;
    }
    
    /**
     * Get currently Warehouse Id which user is working on
     * 
     * @return int
     */
    public function getCurrentWarehouseId()
    {
        $warehouseId = $this->session->getData(self::CURRENT_WAREHOUSE_SESSION_ID);
        if(!$warehouseId) {
            $allowedWarehouses = $this->getAllowedWarehouses();
            $warehouseId = reset($allowedWarehouses);
        }

        return $warehouseId;
    }
    
    /**
     * Get Ids of allowed Warehouses which user can process pick requests
     * 
     * @param string $permissionResource
     * @return array
     */
    public function getAllowedWarehouses($permissionResource = null)
    {
        if(!count($this->allowedWarehouses)) {
            $permissionResource = $permissionResource ? $permissionResource : \Magestore\FulfilSuccess\Api\PermissionManagementInterface::PICK_ITEM;
            $collection = $this->collectionFactory->create();
            $collection = $this->permissionManagement->filterPermission($collection, $permissionResource);   
            $this->allowedWarehouses = $collection->getAllIds();
        }
        return $this->allowedWarehouses;
    }
    
}