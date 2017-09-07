<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Controller\Index;
/**
 * Class Index
 * @package Magestore\Webpos\Controller\Index
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magestore\Webpos\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Store\Api\StoreCookieManagerInterface
     */
    protected $storeCookieManager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magestore\Webpos\Helper\Data $helper
     * @param \Magento\Store\Api\StoreCookieManagerInterface $storeCookieManager
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magestore\Webpos\Helper\Data $helper,
        \Magento\Store\Api\StoreCookieManagerInterface $storeCookieManager,
        \Magento\Framework\App\Http\Context $httpContext
    ){
        $this->_resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->storeCookieManager = $storeCookieManager;
        $this->httpContext = $httpContext;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $store = $this->helper->getPosStore();
        if($store && $store->getId()) {
            $storeId = $store->getId();
            $storeManager = $this->helper->getStoreManager();
            $defaultStoreView = $storeManager->getDefaultStoreView();
            if ($defaultStoreView->getId() == $storeId) {
                $this->storeCookieManager->deleteStoreCookie($store);
            } else {
                $this->httpContext->setValue(\Magento\Store\Model\Store::ENTITY, $store->getCode(), $defaultStoreView->getCode());
                $this->storeCookieManager->setStoreCookie($store);
            }
            $storeManager->setCurrentStore($store->getId());
        }
        $resultLayout = $this->_resultPageFactory->create();
        $resultLayout->getLayout()->getUpdate()->removeHandle('default');
        return $resultLayout;
    }
}
