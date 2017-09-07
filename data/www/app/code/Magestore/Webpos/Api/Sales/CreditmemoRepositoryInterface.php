<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Api\Sales;

interface CreditmemoRepositoryInterface extends \Magento\Sales\Api\CreditmemoRepositoryInterface
{
    /**
     * Performs persist operations for a specified credit memo.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $entity The credit memo.
     * @return \Magestore\Webpos\Api\Data\Sales\OrderInterface Order interface.
     */
    public function saveCreditmemo(\Magento\Sales\Api\Data\CreditmemoInterface $entity);
}
