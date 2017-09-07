<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Controller\Adminhtml\Pos;

/**
 * class \Magestore\Webpos\Controller\Adminhtml\Pos\Save
 * 
 * Save location
 * Methods:
 *  execute
 * 
 * @category    Magestore
 * @package     Magestore\Webpos\Controller\Adminhtml\Pos
 * @module      Webpos
 * @author      Magestore Developer
 */
class Save extends \Magestore\Webpos\Controller\Adminhtml\Pos\AbstractPos
{

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $modelId = (int)$this->getRequest()->getParam('location_id');
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }
        if ($modelId) {
            $model = $this->posFactory->create()
                ->load($modelId);
        } else {
            $model = $this->posFactory->create();
        }
        $autoJoin = isset($data['auto_join']) ? $data['auto_join'] : 0;
        $model->setData($data);
        $model->setLocationId((int)$data['location_id']);
        try {
            $this->posRepository->save($model);
            $this->messageManager->addSuccessMessage(__('Pos was successfully saved'));
        }catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return  $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }

        if($autoJoin) {
            $this->posRepository->autoJoinAllStaffs($model->getId());
        }

        if ($this->getRequest()->getParam('back') == 'edit') {
            return  $resultRedirect->setPath('*/*/edit', ['id' =>$model->getId()]);
        }
        return $resultRedirect->setPath('*/*/');
    }


}