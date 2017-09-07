<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Block\Adminhtml\Staff\Role\Edit\Tab;
/**
 * Class Permission
 * @package Magestore\Webpos\Block\Adminhtml\Staff\Role\Edit\Tab
 */
class Permission extends \Magento\User\Block\Role\Tab\Edit
{
    /**
     * @var string
     */
    protected $_template = 'Magento_User::role/edit.phtml';


    /**
     * @var \Magestore\Webpos\Model\Staff\Acl\AclRetriever
     */
    protected $_webposAclRetriever;


    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_aclResourceProvider = $objectManager->get('\Magestore\Webpos\Model\Staff\Acl\AclResource\ProviderInterface');
        $this->_rootResource = $objectManager->get('\Magestore\Webpos\Model\Staff\Acl\RootResource');
        $this->_webposAclRetriever = $objectManager->get('\Magestore\Webpos\Model\Staff\Acl\AclRetriever');
        
        $rid = $this->_request->getParam('id', false);
        $this->setSelectedResources($this->_webposAclRetriever->getAllowedResourcesByRole($rid));
    }

}
