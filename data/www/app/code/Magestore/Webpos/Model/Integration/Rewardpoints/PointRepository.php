<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Model\Integration\Rewardpoints;

/**
 * Class PointRepository
 * @package Magestore\Webpos\Model\Integration\Rewardpoints
 */
class PointRepository implements \Magestore\Webpos\Api\Integration\Rewardpoints\PointRepositoryInterface
{
    /**
     * @var \Magestore\Webpos\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magestore\Webpos\Api\Data\Integration\Rewardpoints\SearchResultsInterfaceFactory
     */
    protected $searchResultsInterface;

    /**
     * PointRepository constructor.
     * @param \Magestore\Webpos\Helper\Data $helperData
     * @param \Magestore\Webpos\Api\Data\Integration\Rewardpoints\SearchResultsInterfaceFactory $searchResultsInterface
     */
    public function __construct(
        \Magestore\Webpos\Helper\Data $helperData,
        \Magestore\Webpos\Api\Data\Integration\Rewardpoints\SearchResultsInterfaceFactory $searchResultsInterface
    ) {
        $this->_helper = $helperData;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->searchResultsInterface = $searchResultsInterface;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getBalance($customerId){
        $data = [];
        if($customerId){
            $model = $this->_objectManager->create('Magestore\Rewardpoints\Model\Customer');
            $resource = $this->_objectManager->create('Magestore\Rewardpoints\Model\ResourceModel\Customer');
            $resource->load($model, $customerId, 'customer_id');
            if($model->getId() > 0){
                $data['balance'] = floatval($model->getPointBalance());
            }else{
                $data['balance'] = floatval(0);
            }
            $data['success'] = true;
        }else{
            $data['message'] = __('Please choose customer account');
            $data['error'] = true;
        }
        return \Zend_Json::encode($data);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getList(){
        $items = [];
        $collection = $this->_objectManager->create('Magestore\Rewardpoints\Model\ResourceModel\Rewardcustomer\Collection');
        $collection->load();
        if($collection->getSize() > 0){
            $rewardCustomer = $collection->getItems();
            foreach ($rewardCustomer as $customer){
                $items[] = [
                    'reward_id' => $customer->getData('entity_id'),
                    'customer_id' => $customer->getData('entity_id'),
                    'point_balance' => $customer->getData('point_balance')
                ];
            }
        }
        $result = $this->searchResultsInterface->create();
        $result->setItems($items);
        $result->setTotalCount(count($items));
        return $result;
    }
}