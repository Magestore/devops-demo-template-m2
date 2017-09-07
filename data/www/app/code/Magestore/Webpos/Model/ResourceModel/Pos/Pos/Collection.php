<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Model\ResourceModel\Pos\Pos;


use Magestore\Webpos\Api\Data\Pos\PosSearchResultsInterface;

/**
 * Class Collection
 * @package Magestore\Webpos\Model\ResourceModel\Pos\Pos
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
    implements PosSearchResultsInterface
{
    /**
     *
     * @var string
     */
    protected $_idFieldName = 'pos_id';
    /**
     * Initialize collection resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\Webpos\Model\Pos\Pos',
                'Magestore\Webpos\Model\ResourceModel\Pos\Pos');
    }

    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        return parent::_toOptionArray('pos_id','pos_name');
    }

    /**
     * @param array|string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'pos_id') {
            $field = 'main_table.pos_id';
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Get Available Staff
     *
     * @param string $posId
     * @return $this
     */
    public function getAvailabeStaff($posId)
    {
        $collection = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get('Magestore\Webpos\Model\ResourceModel\Staff\Staff\Collection');
        $config = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get('Magestore\Webpos\Helper\Data');
        if($config->getStoreConfig('webpos/general/enable_session')) {
            $posCollection = $this;
            if ($posId != 0) {
                $posCollection->addFieldToFilter('pos_id', array('nin' => array($posId)))
                    ->addFieldToFilter('staff_id', array('neq' => null));
            }
            $staffIds = array();
            if ($posCollection->getSize() > 0) {
                foreach ($posCollection as $pos) {
                    $staffIds[] = $pos->getStaffId();
                }
            }
            if (count($staffIds) > 0) {
                $collection->addFieldToFilter('staff_id', array('nin' => array($staffIds)));
            }
        }
        return $collection;
    }

    /**
     * Get Available Staff
     *
     * @param string $staffId
     * @return $this
     */
    public function getAvailablePos($staffId = null)
    {
        $config = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magestore\Webpos\Helper\Data');
        if($config->getStoreConfig('webpos/general/enable_session')) {
            if ($staffId) {
                $staff = \Magento\Framework\App\ObjectManager::getInstance()
                    ->create('Magestore\Webpos\Model\Staff\StaffFactory')->create()->load($staffId);
                $staffIds = array($staffId, NULL, 0);
                $posIds = $staff->getPosIds();
                if ($posIds) {
                    $posIds = explode(',', $posIds);
                    $this->addFieldToFilter('pos_id', array('in' => array($posIds)));
                }
                $this->addFieldToFilter('staff_id', array(
                        array('in' => array($staffIds)),
                        array('null' => true)
                    )
                );
            }
        }
        $this->addFieldToFilter('status', 1);
        return $this;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        $this->searchCriteria = $searchCriteria;
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        if (!$items) {
            return $this;
        }
        foreach ($items as $item) {
            $this->addItem($item);
        }
        return $this;
    }

}