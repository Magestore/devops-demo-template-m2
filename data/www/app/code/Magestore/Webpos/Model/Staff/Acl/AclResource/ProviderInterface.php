<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Model\Staff\Acl\AclResource;

/**
 * Interface ProviderInterface
 * @package Magestore\Webpos\Model\Staff\Acl\AclResource
 */
interface ProviderInterface
{
    /**
     * @return mixed
     */
    public function getAclResources();
}
