<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Observer\InventorySuccess;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\Webpos\Model\Checkout\Data\ExtensionData;

class NewOrderWarehouse implements ObserverInterface
{
    
    /**
     *
     * @var \Magento\Framework\ObjectManagerInterface  
     */
    protected $_objectManager;
    
    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;    
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * 
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(  
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $coreRegistry;
        $this->logger = $logger;
    }
    
    /**
     * Load linked Warehouse from Location of WebPOS Order
     * 
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        $warehouse = $observer->getEvent()->getWarehouse();
        $locationId = 0;
        /* get current location */
        $extraOrderData = $this->_coreRegistry->registry('webpos_extra_order_data');
        if(count($extraOrderData) > 0){
            foreach($extraOrderData as $data){
               if($data[ExtensionData::KEY_FIELD_KEY] == 'location_id') {
                   $locationId = $data[ExtensionData::KEY_FIELD_VALUE];
               }
            }
        }        

        if(!$locationId) {
            return $this;
        }
        /* get warehouse which is linked to current location */
        $locationMapping = $this->_objectManager->get('\Magestore\InventorySuccess\Api\Warehouse\Location\MappingManagementInterface');
        $warehouseId = $locationMapping->getWarehouseIdByLocationId($locationId);
        if($warehouseId) {
            $warehouse->load($warehouseId);
        }
    }    

}