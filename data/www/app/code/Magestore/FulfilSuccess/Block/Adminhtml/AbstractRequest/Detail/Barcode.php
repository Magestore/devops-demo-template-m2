<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\FulfilSuccess\Block\Adminhtml\AbstractRequest\Detail;

class Barcode extends \Magento\Backend\Block\Template
{
    protected function _prepareLayout()
    {
        $this->setTemplate('Magestore_FulfilSuccess::abstractRequest/detail/barcode.phtml');
        parent::_prepareLayout();
    }
}