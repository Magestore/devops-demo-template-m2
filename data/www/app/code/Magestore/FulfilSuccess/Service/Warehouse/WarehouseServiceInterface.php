<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\FulfilSuccess\Service\Warehouse;

interface WarehouseServiceInterface
{
    
    /**
     * get list warehouse to pick items
     * 
     * @param array $productIds
     * @return array
     */
    public function getWarehousesToPick($productIds);
    

}