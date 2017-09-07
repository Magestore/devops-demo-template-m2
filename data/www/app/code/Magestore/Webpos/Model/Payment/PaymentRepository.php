<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Model\Payment;
/**
 * class \Magestore\Webpos\Model\Payment\PaymnetRepository
 *
 * Methods:
 *
 * @category    Magestore
 * @package     Magestore_Webpos
 * @module      Webpos
 * @author      Magestore Developer
 */
class PaymentRepository implements \Magestore\Webpos\Api\Payment\PaymentRepositoryInterface
{
    /**
    * webpos payment source model
    *
    * @var \Magestore\Webpos\Model\Source\Adminhtml\Payment
    */
    protected $_paymentModelSource;

    /**
    * webpos payment result interface
    *
    * @var \Magestore\Webpos\Api\Data\Payment\PaymentResultInterfaceFactory
    */
    protected $_paymentResultInterface;

    /**
     * @param \Magestore\Webpos\Model\Source\Adminhtml\Payment $paymentModelSource
     * @param \Magestore\Webpos\Api\Data\Payment\PaymentResultInterfaceFactory $paymentResultInterface
     */
    public function __construct(
        \Magestore\Webpos\Model\Source\Adminhtml\Payment $paymentModelSource,
        \Magestore\Webpos\Api\Data\Payment\PaymentResultInterfaceFactory $paymentResultInterface
    ) {
        $this->_paymentModelSource = $paymentModelSource;
        $this->_paymentResultInterface = $paymentResultInterface;
    }

    /**
     * Get payments list
     *
     * @api
     * @return array|null
     */
    public function getList() {
        $paymentList = $this->_paymentModelSource->getPosPaymentMethods();
        $payments = $this->_paymentResultInterface->create();
        $payments->setItems($paymentList);
        $payments->setTotalCount(count($paymentList));
        return $payments;
    }
}