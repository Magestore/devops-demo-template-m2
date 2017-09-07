<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Block;

/**
 * class \Magestore\Webpos\Block\AbstractBlock
 *
 * Web POS abstract block
 * Methods:
 *
 * @category    Magestore
 * @package     Magestore\Webpos\Block
 * @module      Webpos
 * @author      Magestore Developer
 */


/**
 * Class Login
 * @package Magestore\Webpos\Block
 */
class Login extends \Magento\Framework\View\Element\Template
{
    /**
     *
     */
    const XML_PATH_DESIGN_EMAIL_LOGO = 'design/email/logo';

    /**
     * @var \Magestore\Webpos\Model\WebPosSession
     */
    protected $_webPosSession;

    /**
     * @var \Magestore\Webpos\Model\WebposConfigProvider\CompositeConfigProvider
     */
    protected $_configProvider;

    /**
     * @var \Magestore\Webpos\Helper\Permission
     */
    protected $_permissionHelper;

    /**
     * @var \Magestore\Webpos\Helper\Data
     */
    protected $_webposHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $webposFileStorageHelper;


    /**
     * Login constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Webpos\Model\WebPosSession $webPosSession
     * @param \Magestore\Webpos\Helper\Permission $permissionHelper
     * @param \Magestore\Webpos\Helper\Data $webposHelper
     * @param \Magestore\Webpos\Model\WebposConfigProvider\CompositeConfigProvider $configProvider
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Webpos\Model\WebPosSession $webPosSession,
        \Magestore\Webpos\Helper\Permission $permissionHelper,
        \Magestore\Webpos\Helper\Data $webposHelper,
        \Magestore\Webpos\Model\WebposConfigProvider\CompositeConfigProvider $configProvider,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageHelper
    )
    {
        $this->_webPosSession = $webPosSession;
        $this->_configProvider = $configProvider;
        $this->_permissionHelper = $permissionHelper;
        $this->_webposHelper = $webposHelper;
        $this->_storeManager = $context->getStoreManager();
        $this->webposFileStorageHelper = $fileStorageHelper;
        parent::__construct($context);
    }


    /**
     * @return string
     */
    public function toHtml()
    {
        $isLogin = $this->_permissionHelper->getCurrentUser();
        if (!$isLogin) {
            return parent::toHtml();
        } else {
            return '';
        }
    }

    /**
     * @return array
     */
    public function getWebposConfig()
    {
        return $this->_configProvider->getConfig();
    }

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        $imageUrl = $this->_webposHelper->getWebposLogo();
        if ($imageUrl) {
            return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            .'webpos/logo/'.$imageUrl;
        } else {
            return $this->getStoreLogoUrl();
        }
    }

    /**
     * @return string
     */
    protected function getStoreLogoUrl()
    {
        $uploadFolderName = \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR;
        $webposLogoPath = $this->_scopeConfig->getValue(
            'design/header/logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $path = $uploadFolderName . '/' . $webposLogoPath;
        $logoUrl = $this->_urlBuilder
                ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;

        if ($webposLogoPath !== null && $this->_isFile($path)) {
            $url = $logoUrl;
        } elseif ($this->getLogoFile()) {
            $url = $this->getViewFileUrl($this->getLogoFile());
        } else {
            $url = $this->getViewFileUrl('images/logo.svg');
        }
        return $url;
    }

    /**
     * @param $filename
     * @return bool
     */
    protected function _isFile($filename)
    {
        if ($this->webposFileStorageHelper->checkDbUsage() && !$this->getMediaDirectory()->isFile($filename)) {
            $this->webposFileStorageHelper->saveFileToFilesystem($filename);
        }

        return $this->getMediaDirectory()->isFile($filename);
    }


}
