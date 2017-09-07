<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\WebposPaypal\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class GetPaymentAfter
 * @package Magestore\WebposPaypal\Observer
 */
class GetPaymentAfter implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $paypalHelper = \Magento\Framework\App\ObjectManager::getInstance()
                         ->create('Magestore\WebposPaypal\Helper\Data');
        $payments = $observer->getData('payments');
        $paymentList = $payments->getList();
        $isPaypalEnable = $paypalHelper->isEnablePaypal();
        if($isPaypalEnable) {
            $paypalPayment = $this->addWebposPaypal();
            $paymentList[] = $paypalPayment->getData();
        }
        $isAllowPaypalHere = $paypalHelper->isAllowPaypalHere();
        if($isAllowPaypalHere) {
            $paypalPayment = $this->addWebposPaypalHere();
            $paymentList[] = $paypalPayment->getData();
        }
        $payments->setList($paymentList);
    }

    /**
     * @return \Magestore\Webpos\Model\Payment\Payment
     */
    public function addWebposPaypal()
    {
        $paymentHelper = \Magento\Framework\App\ObjectManager::getInstance()
                            ->create('Magestore\Webpos\Helper\Payment');
        $helper = \Magento\Framework\App\ObjectManager::getInstance()
                        ->create('Magestore\Webpos\Helper\Data');
        $isSandbox = $helper->getStoreConfig('webpos/payment/paypal/is_sandbox');
        $clientId = $helper->getStoreConfig('webpos/payment/paypal/client_id');
        $isDefault = ('paypal_integration' == $paymentHelper->getDefaultPaymentMethod()) ?
            \Magestore\Webpos\Api\Data\Payment\PaymentInterface::YES :
            \Magestore\Webpos\Api\Data\Payment\PaymentInterface::NO;
        $paymentModel = \Magento\Framework\App\ObjectManager::getInstance()
                            ->create('Magestore\Webpos\Model\Payment\Payment');
        $paymentModel->setCode('paypal_integration');
        $paymentModel->setIconClass('paypal_integration');
        $paymentModel->setTitle(_('Web POS - Paypal Integration'));
        $paymentModel->setInformation('');
        $paymentModel->setType(2);
        $paymentModel->setTypeId(2);
        $paymentModel->setIsDefault($isDefault);
        $paymentModel->setIsReferenceNumber(0);
        $paymentModel->setIsPayLater(0);
        $paymentModel->setMultiable(1);
        $paymentModel->setClientId($clientId);
        $paymentModel->setIsSandbox($isSandbox);
        return $paymentModel;
    }

    /**
     * @return \Magestore\Webpos\Model\Payment\Payment
     */
    public function addWebposPaypalHere()
    {
        $paymentHelper = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magestore\Webpos\Helper\Payment');
        $helper = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magestore\Webpos\Helper\Data');
        $isSandbox = $helper->getStoreConfig('webpos/payment/paypal/is_sandbox');
        $isDefault = ('paypal_integration' == $paymentHelper->getDefaultPaymentMethod()) ?
            \Magestore\Webpos\Api\Data\Payment\PaymentInterface::YES :
            \Magestore\Webpos\Api\Data\Payment\PaymentInterface::NO;
        $paymentModel = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magestore\Webpos\Model\Payment\Payment');
        $paymentModel->setCode('paypal_here');
        $paymentModel->setIconClass('paypal_here');
        $paymentModel->setTitle(_('Web POS - Paypal Here'));
        $paymentModel->setInformation('');
        $paymentModel->setType(2);
        $paymentModel->setIsDefault($isDefault);
        $paymentModel->setIsReferenceNumber(1);
        $paymentModel->setIsPayLater(0);
        $paymentModel->setMultiable(1);
        $paymentModel->setIsSandbox($isSandbox);
        $accessToken = $helper->getStoreConfig('webpos/payment/paypal/access_token');
        if($accessToken) {
            $paymentModel->setAccessToken($accessToken);
        }
        return $paymentModel;
    }
}