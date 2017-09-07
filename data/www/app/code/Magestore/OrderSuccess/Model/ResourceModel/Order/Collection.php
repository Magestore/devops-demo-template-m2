<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\OrderSuccess\Model\ResourceModel\Order;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 * @package Magestore\OrderSuccess\Model\ResourceModel\Sales
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
{
    /**
     * \Magestore\OrderSuccess\Helper\Data
     */
    protected $helper;
    /**
     * Initialize dependencies.
     *
     *  \Magestore\OrderSuccess\Helper\Data $helper
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        \Magestore\OrderSuccess\Helper\Data $helper,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_order_grid',
        $resourceModel = '\Magento\Sales\Model\ResourceModel\Order'
    ) {
        $this->helper = $helper;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()])
            ->columns(
                [
                    'age' => new \Zend_Db_Expr('TIMESTAMPDIFF(SECOND, main_table.created_at, NOW())')
                ]);
        $this->getSelect()->joinLeft(
            ['order_history' => $this->getTable('sales_order_status_history')],
            'main_table.entity_id = order_history.parent_id',
            [
                'note' => new \Zend_Db_Expr(
                    'GROUP_CONCAT(
                                    CONCAT(
                                        "\n",
                                        DATE_FORMAT(order_history.created_at,\'%b %d %Y %h:%i %p\'),
                                        ": ",
                                        order_history.comment
                                    )
                                    SEPARATOR "\n"
                                 )'
                ),
                'order_history_id' => 'order_history.entity_id'
            ]
        );
        $this->getSelect()->joinLeft(
            ['sales_order' => $this->getTable('sales_order')],
            'main_table.entity_id = sales_order.entity_id',
            [
                'tag_color' => 'sales_order.tag_color',
                'is_verified' => 'sales_order.is_verified',
                'total_due' => 'sales_order.total_due',
                'batch_id' => 'sales_order.batch_id'
            ]
        );
        $this->getSelect()->group('main_table.entity_id');
        $this->addCondition();

        return $this;
    }

    /**
     * rewrite add field to filters from collection
     *
     * @return array
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'increment_id') {
            $field = 'main_table.increment_id';
        }
        if ($field == 'status') {
            $field = 'main_table.status';
        }
        if ($field == 'created_at') {
            $field = 'main_table.created_at';
        }
        if ($field == 'grand_total') {
            $field = 'main_table.grand_total';
        }
        if ($field == 'age') {
            $field = new \Zend_Db_Expr('TIMESTAMPDIFF(SECOND, main_table.created_at, NOW())');
        }
        if ($field == 'tag_color') {
            $field =  'sales_order.tag_color';
            $condition =  array('like' => '%'.$condition['eq'].'%');
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Get current page.
     *
     * @return int|null
     */
    public function getCurrentPage(){
        return $this->getCurPage();
    }

    /**
     * Set current page.
     *
     * @param int $currentPage
     * @return $this
     */
    public function setCurrentPage($currentPage){
        return $this->setCurPage($currentPage);
    }

    /**
     * add condition.
     *
     * @param
     * @return $this
     */
    public function addCondition(){
        return $this;
    }
}
