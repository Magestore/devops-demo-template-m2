<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Model\Cart\Quote;

class Discount extends \Magento\SalesRule\Model\Quote\Discount
{
    /**
     * @var \Magestore\Webpos\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magestore\Webpos\Helper\Currency
     */
    protected $helperCurrency;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magestore\Webpos\Helper\Data $helper
     * @param \Magestore\Webpos\Helper\Currency $helperCurrency
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magestore\Webpos\Helper\Data $helper,
        \Magestore\Webpos\Helper\Currency $helperCurrency
    ) {
        $this->setCode('discount');
        $this->eventManager = $eventManager;
        $this->calculator = $validator;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->helper = $helper;
        $this->helperCurrency = $helperCurrency;
    }

    /**
     * Collect address discount amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $type = $quote->getData('webpos_cart_discount_type');
        $discountValue = $quote->getData('webpos_cart_discount_value');
        $discountName = $quote->getData('webpos_cart_discount_name');
        if(isset($type) && isset($discountValue) && $discountValue > 0){
            if($type == '%')
            {
                $quote->setWebposDiscountAmount(0)
                    ->setWebposDiscountPercent($discountValue)
                    ->setWebposDiscountDesc($discountName);
            }
            else{
                $quote->setWebposDiscountAmount($discountValue)
                    ->setWebposDiscountPercent(0)
                    ->setWebposDiscountDesc($discountName);
            }
        }else{
            return $this;
        }

        $address = $shippingAssignment->getShipping()->getAddress();
        if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
            return $this;
        }

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        $maxPercent = 100;
        $quoteCurrency = $quote->getQuoteCurrencyCode();
        $baseCurrency = $quote->getBaseCurrencyCode();
        $showItemPriceInclTax = $this->helper->getStoreConfig('tax/cart_display/price');
        if ($type == '%') {
            $discountPercent = ($maxPercent > $discountValue)?$discountValue:$maxPercent;
            foreach ($items as $item) {
                if ($item->getParentItemId()) continue;
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $itemBasePrice = ($showItemPriceInclTax != 1) ? $child->getBasePriceInclTax() : $child->getBasePrice();
                        $baseDiscount = $child->getQty() * $itemBasePrice * $discountPercent / 100;
                        $baseDiscount = min($baseDiscount, $child->getQty() * $itemBasePrice - $child->getBaseDiscountAmount());

                        $itemPrice = ($showItemPriceInclTax != 1) ? $child->getPriceInclTax() : $child->getPrice();
                        $discount = $child->getQty() * $itemPrice * $discountPercent / 100;
                        $discount = min($discount, $child->getQty() * $itemPrice - $child->getDiscountAmount());

//                        $discount = ($baseCurrency == $quoteCurrency)?$baseDiscount:
//                                    $this->helperCurrency->currencyConvert($baseDiscount, $baseCurrency, $quoteCurrency);

                        $child->setDiscountAmount($child->getDiscountAmount() + $discount)
                            ->setBaseDiscountAmount($child->getBaseDiscountAmount() + $baseDiscount);

                        $this->_addAmount(-$discount);
                        $this->_addBaseAmount(-$baseDiscount);
                    }
                } else {
                    $itemBasePrice = ($showItemPriceInclTax != 1) ? $item->getBasePriceInclTax() : $item->getBasePrice();
                    $baseDiscount = $item->getQty() * $itemBasePrice * $discountPercent / 100;
                    $baseDiscount = min($baseDiscount, $item->getQty() * $itemBasePrice - $item->getBaseDiscountAmount());

                    $itemPrice = ($showItemPriceInclTax != 1) ? $item->getPriceInclTax() : $item->getRowTotal()/$item->getQty();
                    $discount = $item->getQty() * $itemPrice * $discountPercent / 100;
                    $discount = min($discount, $item->getQty() * $itemPrice - $item->getDiscountAmount());
//                    $discount = ($baseCurrency == $quoteCurrency)?$baseDiscount:
//                        $this->helperCurrency->currencyConvert($baseDiscount, $baseCurrency, $quoteCurrency);

                    $item->setDiscountAmount($item->getDiscountAmount() + $discount)
                        ->setBaseDiscountAmount($item->getBaseDiscountAmount() + $baseDiscount);
                    $this->_addAmount(-$discount);
                    $this->_addBaseAmount(-$baseDiscount);
                }
            }
            if ($address->getShippingAmount()) {
                $baseDiscount = $address->getBaseShippingAmount() * $discountPercent / 100;
                $baseDiscount = min($baseDiscount, $address->getBaseShippingAmount() - $address->getBaseShippingDiscountAmount());

                $discount = $address->getShippingAmount() * $discountPercent / 100;
                $discount = min($discount, $address->getShippingAmount() - $address->getShippingDiscountAmount());
//                $discount = ($baseCurrency == $quoteCurrency)?$baseDiscount:
//                    $this->helperCurrency->currencyConvert($baseDiscount, $baseCurrency, $quoteCurrency);

                $address->setShippingDiscountAmount($address->getShippingDiscountAmount() + $discount)
                    ->setBaseShippingDiscountAmount($address->getBaseShippingDiscountAmount() + $baseDiscount);

                $this->_addAmount(-$discount);
                $this->_addBaseAmount(-$baseDiscount);
            }
            $this->_addCustomDiscountDescription($address);
            return $this;
        }

        // Calculate items total
        $baseItemsPrice = 0;
        foreach ($items as $item) {
            $base_item_price = ($showItemPriceInclTax != 1) ? $item->getBasePriceInclTax() : $item->getBasePrice();
            if ($item->getParentItemId()) continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $base_child_price = ($showItemPriceInclTax != 1) ? $child->getBasePriceInclTax() : $child->getBasePrice();
                    $baseItemsPrice += $item->getQty() * ($child->getQty() * $base_child_price - $child->getBaseDiscountAmount());
                }
            } else {
                $baseItemsPrice += $item->getQty() * $base_item_price - $item->getBaseDiscountAmount();
            }
        }
        $baseItemsPrice += $address->getBaseShippingAmount() - $address->getBaseShippingDiscountAmount();
        if ($baseItemsPrice < 0.0001) {
            return $this;
        }

        // Calculate custom discount for each item
        $baseDiscountValue = ($baseCurrency == $quoteCurrency)?$discountValue:
                             $this->helperCurrency->currencyConvert($discountValue, $quoteCurrency, $baseCurrency);

        $rate = $baseDiscountValue / $baseItemsPrice;
        if ($rate > 1) $rate = 1;
        if(($rate*100) > $maxPercent){
            $rate = $maxPercent/100;
        }
        foreach ($items as $item) {
            $base_item_price = ($showItemPriceInclTax != 1) ? $item->getBasePriceInclTax() : $item->getBasePrice();

            if ($item->getParentItemId()) continue;
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $base_child_price = ($showItemPriceInclTax != 1) ? $child->getBasePriceInclTax() : $child->getBasePrice();

                    $discount = $rate * ($child->getQty() * $base_child_price - $child->getBaseDiscountAmount());
                    $baseDiscount = ($baseCurrency == $quoteCurrency)?$discount:
                                    $this->helperCurrency->currencyConvert($discount, $quoteCurrency, $baseCurrency);

                    $child->setDiscountAmount($child->getDiscountAmount() + $discount)
                        ->setBaseDiscountAmount($child->getBaseDiscountAmount() + $baseDiscount);

                    $this->_addAmount(-$discount);
                    $this->_addBaseAmount(-$baseDiscount);
                }
            } else {
                $baseDiscount = $rate * ($item->getQty() * $base_item_price - $item->getBaseDiscountAmount());
                $discount = ($baseCurrency == $quoteCurrency)?$baseDiscount:
                    $this->helperCurrency->currencyConvert($baseDiscount, $baseCurrency, $quoteCurrency);

                $item->setDiscountAmount($item->getDiscountAmount() + $discount)
                    ->setBaseDiscountAmount($item->getBaseDiscountAmount() + $baseDiscount);

                $this->_addAmount(-$discount);
                $this->_addBaseAmount(-$baseDiscount);
            }
        }
        if ($address->getShippingAmount()) {
            $discount = $rate * ($address->getShippingAmount() - $address->getShippingDiscountAmount());
            $baseDiscount = ($baseCurrency == $quoteCurrency)?$discount:
                            $this->helperCurrency->currencyConvert($discount, $quoteCurrency, $baseCurrency);

            $address->setShippingDiscountAmount($address->getShippingDiscountAmount() + $discount)
                ->setBaseShippingDiscountAmount($address->getBaseShippingDiscountAmount() + $baseDiscount);

            $this->_addAmount(-$discount);
            $this->_addBaseAmount(-$baseDiscount);
        }
        $this->_addCustomDiscountDescription($address);
        return $this;
    }

    /**
     * add custom discount label
     * @param $address
     */
    protected function _addCustomDiscountDescription($address)
    {
        $description = $address->getDiscountDescriptionArray();

        $label = $address->getQuote()->getWebposDiscountDesc();
        if (!$label) {
            $label = __('Custom Discount');
        }
        $description[0] = $label;

        $address->setDiscountDescriptionArray($description);
        $this->calculator->prepareDescription($address);
    }
}
