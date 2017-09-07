<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

/**
 * Created by PhpStorm.
 * User: steve
 * Date: 06/06/2016
 * Time: 13:42
 */

namespace Magestore\Webpos\Model\Shift;
use Magestore\Webpos\Api\Data\Shift\CashTransactionInterface;
use Magestore\Webpos\Api\Data\Shift\ShiftInterface;
use Magento\Framework\Exception\CouldNotSaveException;


class CashTransactionRepository implements \Magestore\Webpos\Api\Shift\CashTransactionRepositoryInterface
{
    /** @var  $cashTransactionResource \Magestore\Webpos\Model\ResourceModel\Shift\CashTransaction */
    protected $cashTransactionResource;

    /** @var  \Magestore\Webpos\Model\Shift\ShiftFactory */
    protected $_shiftFactory;

    /** @var  \Magestore\Webpos\Helper\Shift */
    protected $_shiftHelper;


    public function __construct(
        \Magestore\Webpos\Model\ResourceModel\Shift\CashTransaction $cashTransactionResource,
        \Magestore\Webpos\Model\Shift\ShiftFactory $shiftFactory,
        \Magestore\Webpos\Helper\Shift $shiftHelper
    ) {
        $this->cashTransactionResource = $cashTransactionResource;
        $this->_shiftFactory = $shiftFactory;
        $this->_shiftHelper = $shiftHelper;
    }

    /**
     * @param \Magestore\Webpos\Api\Data\Shift\CashTransactionInterface $cashTransactionInterface $cashTransactionInterface
     * @return \Magestore\Webpos\Api\Data\Shift\CashTransactionInterface $cashTransactionInterface
     * @throws CouldNotSaveException
     */
    public function save(CashTransactionInterface $cashTransactionInterface)
    {
        $shiftId = $cashTransactionInterface->getShiftId();
        $shiftModel = $this->_shiftFactory->create();
        $shiftModel->load($shiftId, "shift_id");

        if(!$shiftModel->getShiftId()){
            return;
        }

        try {
            $shiftData = $shiftModel->recalculateData($cashTransactionInterface);
            $cashTransactionInterface->setBalance($shiftData['balance']);
            $cashTransactionInterface->setShiftId($shiftData['shift_id']);
            $cashTransactionInterface->setBaseBalance($shiftData['base_balance']);
            $this->cashTransactionResource->save($cashTransactionInterface);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        $shiftData = $this->_shiftHelper->prepareOfflineShiftData($shiftData['shift_id']);
        $response[] = $shiftData;

        return $response;
    }
}