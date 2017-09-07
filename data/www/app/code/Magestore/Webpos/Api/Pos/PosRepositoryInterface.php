<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Api\Pos;


/**
 * Interface PosRepositoryInterface
 * @package Magestore\Webpos\Api\Pos
 */
interface PosRepositoryInterface
{
    /**
     * get list Pos
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Magestore\Webpos\Api\Data\Pos\PosSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);

    /**
     * assign staff for pos
     *
     * @param string $posId
     * @param string $staffId
     * @return boolean
     */
    public function assignStaff($posId, $staffId);

}