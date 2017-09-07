<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Model\Payment\Online\Paypal;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\Generic;
use Magestore\Webpos\Model\Payment\Online\Paypal\Payflow\Service\Request\SecureToken;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Quote\Model\Quote;
use Magento\Paypal\Model\Payflowlink;

class Payflowpro extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Generic
     */
    private $sessionTransparent;

    /**
     * @var SecureToken
     */
    private $secureTokenService;

    /**
     * @var Transparent
     */
    private $transparent;

    /**
     * Payflowpro constructor.
     * @param JsonFactory $resultJsonFactory
     * @param Generic $sessionTransparent
     * @param SecureToken $secureTokenService
     * @param Transparent $transparent
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        Generic $sessionTransparent,
        SecureToken $secureTokenService,
        Transparent $transparent
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sessionTransparent = $sessionTransparent;
        $this->secureTokenService = $secureTokenService;
        $this->transparent = $transparent;
    }

    /**
     * @param Quote $quote
     * @return array
     */
    public function requestSecureToken($quote)
    {
        if (!$quote or !$quote instanceof Quote) {
            return $this->getErrorResponse();
        }

        $this->sessionTransparent->setQuoteId($quote->getId());
        try {
            $token = $this->secureTokenService->requestToken($quote);
            if (!$token->getData('securetoken')) {
                throw new \LogicException();
            }

            return [
                'url' => Payflowlink::TRANSACTION_PAYFLOW_URL,
                'params' => $token->getData(),
                'success' => true,
                'error' => false
            ];
        } catch (\Exception $e) {
            return $this->getErrorResponse($e->getMessage());
        }
    }

    /**
     * @return array
     */
    private function getErrorResponse($message = '')
    {
        return [
            'success' => false,
            'error' => true,
            'error_messages' => ($message)?$message:__('Your payment has been declined. Please try again.')
        ];
    }
}