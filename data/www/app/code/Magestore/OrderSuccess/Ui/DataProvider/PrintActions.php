<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\OrderSuccess\Ui\DataProvider;

use Magento\Framework\UrlInterface;

/**
 * Class PrintActions
 * @package Magestore\OrderSuccess\Ui\DataProvider
 */
class PrintActions
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var string
     */
    protected $printAllUrl = 'sales/order/pdfdocs';

    /**
     * @var string
     */
    protected $printInvoiceUrl = 'sales/order/pdfinvoices';

    /**
     * @var string
     */
    protected $printShipmentUrl = 'sales/order/pdfshipments';

    /**
     * @var string
     */
    protected $printCreditmemosUrl = 'sales/order/pdfcreditmemos';

    /**
     * @var string
     */
    protected $printShippingLabelUrl = 'ordersuccess/order/massPrintShippingLabel';

    /**
     * PrintActions constructor.
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder
    ){
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * get actions
     * 
     * @return array
     */
    public function getActions($posistion)
    {
        $actions = [];
        $actions = [
            [
                'type' => 'pdfinvoices_order',
                'label' => __('Print Invoices'),
                'url' => $this->urlBuilder->getUrl($this->printInvoiceUrl,
                    [
                        'order_position' => $posistion
                    ]),
            ],
            [
                'type' => 'pdfshipments_order',
                'label' => __('Print Packing Slips'),
                'url' => $this->urlBuilder->getUrl($this->printShipmentUrl,
                    [
                        'order_position' => $posistion
                    ]),
            ],
            [
                'type' => 'pdfcreditmemos_order',
                'label' => __('Print Credit Memos'),
                'url' => $this->urlBuilder->getUrl($this->printCreditmemosUrl,
                    [
                        'order_position' => $posistion
                    ]),
            ],
            [
                'type' => 'print_shipping_label',
                'label' => __('Print Shipping Labels'),
                'url' => $this->urlBuilder->getUrl($this->printShippingLabelUrl,
                    [
                        'order_position' => $posistion
                    ]),
            ],
            [
                'type' => 'pdfdocs_order',
                'label' => __('Print All'),
                'url' => $this->urlBuilder->getUrl($this->printAllUrl,
                    [
                        'order_position' => $posistion
                    ]),
            ]
        ];
        return $actions;
    }
}