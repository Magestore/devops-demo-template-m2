<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Webpos
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Webpos\Block\Adminhtml\Zreport;

class Prints extends \Magestore\Webpos\Block\Adminhtml\AbstractBlock
{
//    protected function _construct()
//    {
//        die('222');
//    }

    public function getZreportData(){
        $shiftRepository = $this->_objectManager->create('Magestore\Webpos\Model\Shift\ShiftRepository');
        $id = $this->getRequest()->getParam('id');
        $data = $shiftRepository->getInfo($id);
//        $data['sale_by_payments'] = $data['sales_summary'];
//        if(!empty($data['zreport_sales_summary'])){
//            $data['zreport_sales_summary'] = Zend_Json::decode($data['sales_summary']);
//        }
        if(!empty($data['staff_id'])){
            $staff = $this->_objectManager->create('Magestore\Webpos\Model\Staff\StaffFactory')
                ->create()->load($data['staff_id']);
            $data['staff_name'] = ($staff->getDisplayName())?$staff->getDisplayName():$staff->getUsername();
        }else{
            $data['staff_name'] = '';
        }
        return $data;
    }

    public function formatReportPrice($price){
        $helper = $this->_objectManager->get('Magestore\Webpos\Helper\Data');
        return $helper->formatPrice($price);
    }

    public function formatReportDate($date){
        $helper = $this->_objectManager->get('Magestore\Webpos\Helper\Data');
        return $helper->formatDate($date);
    }
}