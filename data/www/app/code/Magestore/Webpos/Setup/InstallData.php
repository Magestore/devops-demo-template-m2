<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \Magestore\Webpos\Model\Staff\RoleFactory
     */
    protected $_roleFactory;
    /**
     * @var \Magestore\Webpos\Model\Staff\AuthorizationRuleFactory
     */
    protected $_authorizationRuleFactory;
    /**
     * @var \Magento\User\Model\ResourceModel\User\CollectionFactory
     */
    protected $_userCollectionFactory;
    /**
     * @var \Magestore\Webpos\Model\Staff\StaffFactory
     */
    protected $_staffFactory;
    /**
     * @var \Magestore\Webpos\Model\Location\LocationFactory
     */
    protected $_locationFactory;

    /**
     *
     */
    const IS_ACTIVE = 1;
    /**
     *
     */
    const NOT_ENCODE_PASSWORD = 1;
    /**
     *
     */
    const DEFAULT_DISCOUNT_PERCENT = 100;
    /**
     *
     */
    const DEFAULT_RESOURCE_ACCESS = 'Magestore_Webpos::all';

    /**
     * InstallData constructor.
     * @param \Magestore\Webpos\Model\Staff\StaffFactory $staffFactory
     * @param \Magestore\Webpos\Model\Staff\RoleFactory $roleFactory
     * @param \Magestore\Webpos\Model\Staff\AuthorizationRuleFactory $authorizationRuleFactory
     * @param \Magestore\Webpos\Model\Location\LocationFactory $locationFactory
     * @param \Magento\User\Model\ResourceModel\User\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magestore\Webpos\Model\Staff\StaffFactory $staffFactory,
        \Magestore\Webpos\Model\Staff\RoleFactory $roleFactory,
        \Magestore\Webpos\Model\Staff\AuthorizationRuleFactory $authorizationRuleFactory,
        \Magestore\Webpos\Model\Location\LocationFactory $locationFactory,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $collectionFactory
    ){
        $this->_roleFactory = $roleFactory;
        $this->_authorizationRuleFactory = $authorizationRuleFactory;
        $this->_userCollectionFactory = $collectionFactory;
        $this->_staffFactory = $staffFactory;
        $this->_locationFactory = $locationFactory;
    }


    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $setup->startSetup();
        $roleData = array(
            'display_name' => 'admin',
            'description' => 'Admin',
            'maximum_discount_percent' => self::DEFAULT_DISCOUNT_PERCENT
        );

        $role = $this->_roleFactory->create()->setData($roleData)->save();
        $roleId = $role->getId();


        $authorizeRule = array(
            'role_id' => $roleId,
            'resource_id' => self::DEFAULT_RESOURCE_ACCESS
        );
        $this->_authorizationRuleFactory->create()->setData($authorizeRule)->save();

        $data = array(
            'display_name' => 'Store Address',
            'address' => 'Store Address',
            'description' => 'Store Address'
        );
        $locationModel = $this->_locationFactory->create()->setData($data)->save();
        $locationId = $locationModel->getId();

        $userModel = $this->_userCollectionFactory->create()->addFieldToFilter('is_active',self::IS_ACTIVE)
            ->getFirstItem();
        if ($userModel->getId()) {
            $username = $userModel->getUsername();
            $email = $userModel->getEmail();
            $password = $userModel->getPassword();
            $name = $userModel->getFirstname(). ' '.$userModel->getLastname();
            $customerGroup = 'all';
            $data = array(
                'username' => $username,
                'password' => $password,
                'display_name' => $name,
                'email' => $email,
                'customer_group' => $customerGroup,
                'role_id' => $roleId,
                'location_id' => $locationId,
                'status' => self::IS_ACTIVE,
                'not_encode' => self::NOT_ENCODE_PASSWORD,
                'can_use_sales_report' => 1
            );
            $this->_staffFactory->create()->setData($data)->save();
        }

        $setup->endSetup();
    }
}
