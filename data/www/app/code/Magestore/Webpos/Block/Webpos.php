<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Block;
class Webpos extends \Magestore\Webpos\Block\AbstractBlock
{
    /**
     * @return string
     */
    public function toHtml()
    {
        $isLogin = $this->_permissionHelper->getCurrentUser();
        if ($isLogin) {
            return parent::toHtml();
        } else {
            return '';
        }
        
    }
}
