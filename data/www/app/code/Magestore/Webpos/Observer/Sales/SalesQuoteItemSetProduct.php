<?php
/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

namespace Magestore\Webpos\Observer\Sales;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SalesQuoteItemSetProduct implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if (strpos($product->getSku(), 'webpos-customsale') === false) {
            return;
        }
        $tax_class_id = $product->getCustomOption('tax_class_id');
        if ($tax_class_id && $tax_class_id->getValue()) {
            $item = $observer->getEvent()->getQuoteItem();
            $item->getProduct()->setTaxClassId($tax_class_id->getValue());
        }
        $name = $product->getCustomOption('name');
        if ($name && $name->getValue()) {
            $item = $observer->getEvent()->getQuoteItem();
            $item->setName($name->getValue());
        }
    }
}