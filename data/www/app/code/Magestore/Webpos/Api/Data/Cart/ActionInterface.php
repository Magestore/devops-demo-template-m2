<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Api\Data\Cart;

interface ActionInterface
{
    const CREATE_INVOICE = 'create_invoice';
    const CREATE_SHIPMENT = 'create_shipment';
    const DELIVERY_TIME = 'delivery_time';

    /**
     * Sets create invoice
     *
     * @param string $createInvoice
     * @return $this
     */
    public function setCreateInvoice($createInvoice);
    
    /**
     * Gets create invoice
     *
     * @return string.
     */
    public function getCreateInvoice();

    /**
     * Sets create shipment
     *
     * @param string $createShipment
     * @return $this
     */
    public function setCreateShipment($createShipment);

    /**
     * Gets create shipment
     *
     * @return string.
     */
    public function getCreateShipment();

    /**
     * Sets delivery time
     *
     * @param string $deliveryTime
     * @return $this
     */
    public function setDeliveryTime($deliveryTime);

    /**
     * Gets create invoice
     *
     * @return string.
     */
    public function getDeliveryTime();

}
