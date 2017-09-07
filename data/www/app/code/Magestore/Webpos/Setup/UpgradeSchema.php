<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;
    
    /**
     * UpgradeSchema constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
    
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            //add tax_class_id for sales_order_table table
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_item'),
                'custom_tax_class_id',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable'  => true,
                    'length'    => '11',
                    'comment'   => 'Custom Tax Class Id'
                )
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('catalog_product_entity'),
                'updated_datetime',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_UPDATE],
                'Updated Time'
            );
            /** @var EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create();
            /**
             * Remove attribute webpos_visible
             */
            //Find these in the eav_entity_type table
            $action = \Magento\Framework\App\ObjectManager::getInstance()->get(
                '\Magento\Catalog\Model\ResourceModel\Product\Action'
            );
            $attribute = $action->getAttribute('webpos_visible');
            if($attribute){
                $entityTypeId = \Magento\Framework\App\ObjectManager::getInstance()
                ->create('Magento\Eav\Model\Config')
                ->getEntityType(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)
                ->getEntityTypeId();
                $eavSetup->removeAttribute($entityTypeId, 'webpos_visible');
            }
            
            $eavSetup->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'webpos_visible'
            );
            
            /**
            * Add attributes to the eav/attribute
            */
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'webpos_visible',
                [
                    'group' => 'General',
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Visible on Webpos',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '1',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );
        }
        
        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            //add customer full name for sales_order table
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'customer_fullname',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable'  => true,
                    'length'    => '255',
                    'comment'   => 'Customer Full Name'
                )
            );
        }
        
        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'webpos_init_data',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable'  => true,
                    'comment'   => 'Web POS init data use for on hold order'
                )
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'webpos_cart_discount_type',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable'  => true,
                    'length'    => '5',
                    'comment'   => 'Web POS Discount Type'
                )
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'webpos_cart_discount_value',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable'  => true,
                    'length'    => '12,4',
                    'comment'   => 'Web POS Discount Value'
                )
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'webpos_cart_discount_name',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable'  => true,
                    'length'    => '255',
                    'comment'   => 'Web POS Discount Name'
                )
            );
        }

        if (version_compare($context->getVersion(), '1.1.4', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('webpos_session'),
                'current_shift_id',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable'  => true,
                    'length'    => '255',
                    'comment'   => 'Current Shift ID'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('webpos_session'),
                'current_quote_id',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable'  => true,
                    'comment'   => 'Current Quote ID'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('webpos_session'),
                'current_store_id',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable'  => true,
                    'comment'   => 'Current Store ID'
                )
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'base_gift_voucher_discount',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable'  => true,
                    'length'    => '12,4',
                    'comment'   => 'Base Gifvoucher Discount Value'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'gift_voucher_discount',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable'  => true,
                    'length'    => '12,4',
                    'comment'   => 'Gift Voucher Discount Value'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'giftvoucher_base_hidden_tax_amount',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable'  => true,
                    'length'    => '12,4',
                    'comment'   => 'Gift Voucher Base Hidden Tax Amount'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'giftvoucher_hidden_tax_amount',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable'  => true,
                    'length'    => '12,4',
                    'comment'   => 'Gift Voucher Hidden Tax Amount'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'giftvoucher_base_shipping_hidden_tax_amount',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable'  => true,
                    'length'    => '12,4',
                    'comment'   => 'Gift Voucher Base Shipping Hidden Tax Amount'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'giftvoucher_shipping_hidden_tax_amount',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable'  => true,
                    'length'    => '12,4',
                    'comment'   => 'Gift Voucher Shipping Hidden Tax Amount'
                )
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'rewardpoints_earn',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable'  => true,
                    'length'    => '11',
                    'comment'   => 'Reward Points Earn'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'rewardpoints_spent',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable'  => true,
                    'length'    => '11',
                    'comment'   => 'Reward Points Spent'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'rewardpoints_base_discount',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable'  => false,
                    'default'   => 0,
                    'length'    => '12,4',
                    'comment'   => 'Reward Points Base Discount'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'rewardpoints_base_amount',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable'  => false,
                    'default'   => 0,
                    'length'    => '12,4',
                    'comment'   => 'Reward Points Base Amount'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'rewardpoints_amount',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable'  => false,
                    'default'   => 0,
                    'length'    => '12,4',
                    'comment'   => 'Reward Points Amount'
                )
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'rewardpoints_discount',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable'  => false,
                    'default'   => 0,
                    'length'    => '12,4',
                    'comment'   => 'Reward Points Discount'
                )
            );
        }

        if (version_compare($context->getVersion(), '1.1.5', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('webpos_shift'),
                'pos_id',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable'  => true,
                    'comment'   => 'POS ID'
                )
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('webpos_staff'),
                'pos_ids',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable'  => true,
                    'comment'   => 'POS IDs'
                )
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('webpos_staff'),
                'pin',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable'  => true,
                    'length'    => 6,
                    'default'   => '0000',
                    'comment'   => 'PIN'
                )
            );

            $setup->getConnection()->dropTable($setup->getTable('webpos_pos'));
            $installer = $setup;
            $table = $installer->getConnection()->newTable(
                $installer->getTable('webpos_pos')
            )->addColumn(
                'pos_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'pos_id'
            )->addColumn(
                'pos_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'pos_name'
            )->addColumn(
                'location_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'location_id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'store_id'
            )->addColumn(
                'staff_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'staff_id'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0],
                'status'
            );

            $installer->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
