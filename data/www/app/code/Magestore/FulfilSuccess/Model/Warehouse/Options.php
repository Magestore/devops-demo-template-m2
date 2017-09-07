<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\FulfilSuccess\Model\Warehouse;

use Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface;

abstract class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var null|array
     */
    protected $options;
    
    /**
     * @var bool 
     */
    protected $showAnonymousRow = false;
    
    /**
     * @var \Magestore\FulfilSuccess\Service\Location\LocationServiceInterface
     */
    protected $locationService;

    /**
     * 
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory $collectionFactory,
        \Magestore\FulfilSuccess\Service\Location\LocationServiceInterface $locationService
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->locationService = $locationService;
    }
    
    /**
     * 
     * @return \Magestore\InventorySuccess\Model\Warehouse\Options
     */
    public function showDummyRow()
    {
        $this->showAnonymousRow = true;
        return $this;
    }
    
    /**
     * Get permission of current task
     * 
     * @return string
     */
    abstract protected function getPermission();

    /**
     * @return array|null
     */
    public function toOptionArray()
    {
        $allowedIds = $this->locationService->getAllowedWarehouses($this->getPermission());
        $collection = $this->collectionFactory->create()
                           ->addFieldToFilter(WarehouseInterface::WAREHOUSE_ID, ['in' => $allowedIds]);
        if (null == $this->options) {
            if($this->showAnonymousRow) {
                $this->options = [['value' => 0, 'label' => __('Select Warehouse')]];
            } else {
                $this->options = [];
            }       
            $this->options = array_merge($this->options , $collection->toOptionArray());
        }
        
        return $this->options;
    }
}