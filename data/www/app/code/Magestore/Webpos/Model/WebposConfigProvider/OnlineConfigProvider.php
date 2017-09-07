<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Model\WebposConfigProvider;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class OnlineConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected $_webposSession;

    protected $_storeManager;

    protected $_objectManager;

    protected $_permissionHelper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * OnlineConfigProvider constructor.
     * @param \Magestore\Webpos\Model\WebPosSession $webPosSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magestore\Webpos\Helper\Permission $permissionHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magestore\Webpos\Model\WebPosSession $webPosSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Webpos\Helper\Permission $permissionHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_webposSession = $webPosSession;
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_permissionHelper = $permissionHelper;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $quote = false;
        $session = $this->_permissionHelper->getCurrentSession();
        if($session){
            $storeId = $this->_permissionHelper->getCurrentStoreId();
            $quoteId = $this->_permissionHelper->getCurrentQuoteId();
            $tillId = $this->_permissionHelper->getCurrentShiftId();
            if($quoteId){
                $quote = $this->quoteRepository->get($quoteId);
            }
        }
        $data = array(
            \Magestore\Webpos\Api\Data\Cart\QuoteInterface::STORE_ID => ($session && $storeId)?$storeId:$this->_storeManager->getStore(true)->getId(),
            \Magestore\Webpos\Api\Data\Cart\QuoteInterface::TILL_ID => ($session)?(($tillId)?$tillId:''):0,
            \Magestore\Webpos\Api\Data\Cart\QuoteInterface::QUOTE_ID => ($quote && $quote->getId())?$quote->getId():'',
            \Magestore\Webpos\Api\Data\Cart\QuoteInterface::CURRENCY_ID => ($quote && $quote->getId())?$quote->getQuoteCurrencyCode():$this->_storeManager->getStore()->getCurrentCurrency()->getCode(),
            \Magestore\Webpos\Api\Data\Cart\QuoteInterface::CUSTOMER_ID => ($quote && $quote->getId() && $quote->getCustomerId())?$quote->getCustomerId():0
        );
        $sections = $this->_scopeConfig->getValue('webpos/online/sections', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $useCustomOrderId = $this->_scopeConfig->getValue('webpos/online/use_custom_order_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $defaultShipping = $this->_scopeConfig->getValue('webpos/shipping/defaultshipping', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $defaultPayment = $this->_scopeConfig->getValue('webpos/payment/defaultpayment', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $data['sections'] = $sections;
        $data['use_custom_order_id'] = $useCustomOrderId;
        $output = ['online_data' => $data, 'default_shipping' => $defaultShipping, 'default_payment' => $defaultPayment];
        $configObject = new \Magento\Framework\DataObject();
        $configObject->setData($output);
        $output = $configObject->getData();
        return $output;
    }
}
