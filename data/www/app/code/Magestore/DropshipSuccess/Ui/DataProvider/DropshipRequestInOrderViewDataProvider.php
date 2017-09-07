<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\DropshipSuccess\Ui\DataProvider;

use Magestore\DropshipSuccess\Model\ResourceModel\DropshipRequest\Grid\CollectionFactory;

/**
 * Class ProductDataProvider
 */
class DropshipRequestInOrderViewDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /** @var mixed  */
    protected $collection;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestInterface;

    /**
     * DropshipRequestInOrderViewDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $requestInterface,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->requestInterface = $requestInterface;
        $orderId = $this->requestInterface->getParam('order_id');
        $this->collection = $collectionFactory->create()->addFieldToFilter('order_id', $orderId);
    }
}
