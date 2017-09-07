<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Api\Integration\Response;

/**
 * Interface GiftcardInterface
 * @package Magestore\Webpos\Api\Integration\Response
 */
interface GiftcardInterface
{

    const EXISTED_CODES = 'existed_codes';
    const USED_CODES = 'used_codes';

    /**
     * Get existed codes
     *
     * @api
     * @return anyType
     */
    public function getExistedCodes();

    /**
     * Set existed codes
     *
     * @api
     * @param anyType $existedCodes
     * @return $this
     */
    public function setExistedCodes($existedCodes);

    /**
     * Get used codes
     *
     * @api
     * @return anyType
     */
    public function getUsedCodes();

    /**
     * Set used codes
     *
     * @api
     * @param anyType $usedCodes
     * @return $this
     */
    public function setUsedCodes($usedCodes);

}
