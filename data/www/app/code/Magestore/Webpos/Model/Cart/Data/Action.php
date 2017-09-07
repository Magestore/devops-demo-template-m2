<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Model\Cart\Data;

/**
 * Class Action
 * @package Magestore\Webpos\Model\Cart\Data
 */
class Action extends \Magento\Framework\Model\AbstractExtensibleModel implements \Magestore\Webpos\Api\Data\Cart\ActionInterface
{
    /**
     * Sets create invoice
     *
     * @param string $createInvoice
     * @return $this
     */
    public function setCreateInvoice($createInvoice)
    {
        return $this->setData(self::CREATE_INVOICE, $createInvoice);
    }

    /**
     * Gets create invoice
     *
     * @return string.
     */
    public function getCreateInvoice()
    {
        return $this->getData(self::CREATE_INVOICE);
    }

    /**
     * Sets create shipment
     *
     * @param string $createShipment
     * @return $this
     */
    public function setCreateShipment($createShipment)
    {
        return $this->setData(self::CREATE_SHIPMENT, $createShipment);
    }

    /**
     * Gets create shipment
     *
     * @return string.
     */
    public function getCreateShipment()
    {
        return $this->getData(self::CREATE_SHIPMENT);
    }

    /**
     * Sets delivery time
     *
     * @param string $deliveryTime
     * @return $this
     */
    public function setDeliveryTime($deliveryTime)
    {
        return $this->setData(self::DELIVERY_TIME, $deliveryTime);
    }

    /**
     * Gets create invoice
     *
     * @return string.
     */
    public function getDeliveryTime()
    {
        return $this->getData(self::DELIVERY_TIME);
    }

}