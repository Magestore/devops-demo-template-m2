<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\OrderSuccess\Api;

/**
 * Interface OrderRepositoryInterface
 * @package Magestore\OrderSuccess\Api
 */
interface OrderRepositoryInterface
{
    /**
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface Order search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria);

    /**
     * Loads a specified order.
     *
     * @param int $id The order ID.
     * @return \Magestore\OrderSuccess\Api\Data\OrderInterface Sales interface.
     */
    public function get($id);

    /**
     * Deletes a specified order.
     *
     * @param \Magestore\OrderSuccess\Api\Data\OrderInterface $entity The order ID.
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\OrderInterface $entity);

    /**
     * Performs persist operations for a specified order.
     *
     * @param \Magestore\OrderSuccess\Api\Data\OrderInterface $entity The order ID.
     * @return \Magestore\OrderSuccess\Api\Data\OrderInterface Order interface.
     */
    public function save(\Magento\Sales\Api\Data\OrderInterface $entity);

    /**
     * Retrieve Order.
     *
     * @param int $orderId
     * @return \Magestore\OrderSuccess\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($orderId);

    /**
     * Mass update batch Id of Orders
     *
     * @param array $orderIds
     * @param int $batchId
     */
    public function massUpdateBatch($orderIds, $batchId);

    /**
     * Mass update batch Id of Orders
     *
     * @param array $orderIds
     * @param string $tag
     */
    public function massUpdateTag($orderIds, $tag);

}