<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\BarcodeSuccess\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Cms module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->addAttributeField($setup);
        }
    }

    /**
     * Add store_id
     *
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */
    protected function addAttributeField(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('os_barcode_template'), 'product_attribute_show_on_barcode')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_barcode_template'),
                'product_attribute_show_on_barcode',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Attributes'
                ]
            );
        }
        return $this;
    }
}
