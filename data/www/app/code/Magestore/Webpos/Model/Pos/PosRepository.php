<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Model\Pos;

use Magento\Framework\Exception\StateException;
use Magestore\Webpos\Api\Data\Pos\PosSearchResultsInterfaceFactory as SearchResultFactory;
use Magento\Framework\Api\SortOrder;

/**
 * Class PosRepository
 * @package Magestore\Webpos\Model\Pos
 */
class PosRepository implements \Magestore\Webpos\Api\Pos\PosRepositoryInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magestore\Webpos\Model\Pos\PosFactory
     */
    protected $posFactory;

    /**
     * @var  \Magestore\Webpos\Model\ResourceModel\Pos\Pos
     */
    protected $posResourceModel;

    /**
     * @var \Magestore\Webpos\Model\ResourceModel\Pos\Pos\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magestore\Webpos\Model\ResourceModel\Staff\Staff\CollectionFactory
     */
    protected $staffCollectionFactory;

    /**
     * @var  \Magestore\Webpos\Helper\Permission
     */
    protected $permissionHelper;

    /**
     * @var  SearchResultFactory
     */
    protected $searchResultFactory;

    /**
     * PosRepository constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magestore\Webpos\Model\ResourceModel\Pos\Pos\CollectionFactory $collectionFactory
     * @param \Magestore\Webpos\Model\Pos\PosFactory $posFactory
     * @param \Magestore\Webpos\Model\ResourceModel\Pos\Pos $posResourceModel
     * @param \Magestore\Webpos\Helper\Permission $permissionHelper
     * @param \Magestore\Webpos\Model\ResourceModel\Staff\Staff\CollectionFactory $staffCollectionFactory
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magestore\Webpos\Model\Pos\PosFactory $posFactory,
        \Magestore\Webpos\Model\ResourceModel\Pos\Pos\CollectionFactory $collectionFactory,
        \Magestore\Webpos\Model\ResourceModel\Pos\Pos $posResourceModel,
        \Magestore\Webpos\Helper\Permission $permissionHelper,
        \Magestore\Webpos\Model\ResourceModel\Staff\Staff\CollectionFactory $staffCollectionFactory,
        SearchResultFactory $searchResultFactory
    ) {
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->posFactory = $posFactory;
        $this->posResourceModel = $posResourceModel;
        $this->permissionHelper = $permissionHelper;
        $this->staffCollectionFactory = $staffCollectionFactory;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * get list Pos
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria
     * @return \Magestore\Webpos\Api\Data\Pos\PosSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        /** @var \Magestore\Webpos\Api\Data\Pos\PosSearchResultsInterface $searchResult */
        $searchResult =  $this->searchResultFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $this->addFilterGroupToCollection($filterGroup, $searchResult);
//            $collection = $searchResult->getAvailablePos($staff);
        }

        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders === null) {
            $sortOrders = [];
        }
        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $searchResult->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }
        if($searchCriteria ->getCurrentPage()) {
            $searchResult->setCurPage($searchCriteria->getCurrentPage());
        }
        if($searchCriteria ->getPageSize()) {
            $searchResult->setPageSize($searchCriteria->getPageSize());
        }
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Magestore\Webpos\Api\Data\Pos\PosSearchResultsInterface $searchResult
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magestore\Webpos\Api\Data\Pos\PosSearchResultsInterface $searchResult
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $conditions[] = [$condition => $filter->getValue()];
            if($filter->getField() == 'staff_id'){
                $searchResult->getAvailablePos($filter->getValue());
            } else {
                $fields[] = $filter->getField();
            }
        }
        if ($fields && count($fields) > 0) {
            $searchResult->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * get Pos
     *
     * @return \Magestore\Webpos\Api\Data\Pos\PosInterface
     */
    public function get($posId)
    {
        $pos = $this->posFactory->create()->load($posId);
        if($pos->getId()) {
            return $pos;
        }
        return null;
    }

    /**
     * save Pos
     *
     * @param \Magestore\Webpos\Api\Data\Pos\PosInterface $pos
     * @return \Magestore\Webpos\Api\Data\Pos\PosInterface
     * @throws \Exception
     */
    public function save(\Magestore\Webpos\Api\Data\Pos\PosInterface $pos)
    {
        try {
            $this->posResourceModel->save($pos);
        }catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save pos'));
        }
        return $pos;
    }

    /**
     * assign staff for pos
     *
     * @param string $posId
     * @param string $staffId
     * @return boolean
     * @throws StateException
     */
    public function assignStaff($posId, $staffId)
    {
        $posList = $this->collectionFactory->create()
                    ->addFieldToFilter('staff_id', $staffId);
        if($posList->getSize()) {
            foreach ($posList as $pos) {
                $pos->setStaffId(null);
                try {
                    $pos->save();
                }catch (\Exception $e) {

                }
            }
        }
        $pos = $this->posFactory->create()->load($posId);
        $config = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magestore\Webpos\Helper\Data');
        if($config->getStoreConfig('webpos/general/enable_session')) {
            if ($pos->getStaffId() && $pos->getStaffId() != $staffId) {
                throw new StateException(__('Can not connect to the pos'));
            }
        }
        $pos->setStaffId($staffId);
        try {
            $pos->save();
        }catch (\Exception $e) {
            throw new StateException(__('Can not connect to the pos'));
        }
        if($pos->getId() || $pos->getId() == 0) {

        }
        return true;
    }

    /**
     * unassign staff in pos
     * @param string $posId
     *
     * @return mixed
     */
    public function unassignStaff($posId)
    {
        $pos = $this->posFactory->create()->load($posId);
        $pos->setStaffId(null);
        try {
            $pos->save();
        }catch (\Exception $e) {

        }
    }

    /**
     * auto join all staff for pos
     * @param string $posId
     *
     * @return mixed
     */
    public function autoJoinAllStaffs($posId)
    {
        $staffCollection = $this->staffCollectionFactory->create();
        foreach ($staffCollection as $staff){
            $posIds = $staff->getPosIds();
            $posIds = explode(',', $posIds);
            $posIds[] = $posId;
            $posIds = array_unique($posIds);
            $posIds = implode(',', $posIds);
            $staff->setPosIds($posIds);
            try {
                $staff->save();
            } catch (\Exception $e) {

            }
        }
    }
}