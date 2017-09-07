<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Api\Data\Checkout;


/**
 * Interface CartItemInterface
 * @package Magestore\Webpos\Api\Data\Checkout
 */
interface CartItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for field names
     */
    const KEY_ID = 'id';
    const KEY_QTY = 'qty';
    const KEY_DISCOUNT_AMOUNT = 'discount_amount';
    const KEY_BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    const KEY_TAX_AMOUNT = 'tax_amount';
    const KEY_BASE_TAX_AMOUNT = 'base_tax_amount';
    const KEY_CUSTOM_PRICE = 'custom_price';
    const KEY_SUPER_ATTRIBUTE = 'super_attribute';
    const KEY_SUPER_GROUP = 'super_group';
    const KEY_BUNDLE_OPTION = 'bundle_option';
    const KEY_BUNDLE_OPTION_QTY = 'bundle_option_qty';
    const KEY_CUSTOM_OPTION = 'options';
    const KEY_IS_CUSTOM_SALE = 'is_custom_sale';
    const KEY_EXTENSION_DATA = 'extension_data';

    const KEY_QTY_TO_SHIP = 'qty_to_ship';
    const KEY_CUSTOM_SALE_ID = 'customsale';
    const CUSTOM_SALE_PRODUCT_SKU = 'webpos-customsale';
    const CUSTOM_SALE_TAX_CLASS_ID = 0;
    const CUSTOMERCREDIT_AMOUNT = 'amount';
    const CUSTOMERCREDIT_PRICE_AMOUNT = 'credit_price_amount';

    const ITEM_ID = 'item_id';
    const USE_DISCOUNT = 'use_discount';
    /**#@-*/

    /**
     * Returns the product id.
     *
     * @return string|int id. Otherwise, null.
     */
    public function getId();

    /**
     * Sets the product id.
     *
     * @param string|int $id
     * @return $this
     */
    public function setId($id);
    
    /**
     * Returns the item quantity.
     *
     * @return float Qty. Otherwise, null.
     */
    public function getQty();
    
    /**
     * Sets the item quantity.
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);
    
    /**
     * Returns the item custom price.
     *
     * @return float.
     */
    public function getCustomPrice();
    
    /**
     * Sets the item custom price.
     *
     * @param float $customPrice
     * @return $this
     */
    public function setCustomPrice($customPrice);
    
    /**
     * Sets the item supper attribute.
     *
     * @param \Magestore\Webpos\Api\Data\Checkout\CartItemOptionInterface[] $super_attribute
     * @return $this
     */
    public function setSuperAttribute($super_attribute);
    
    /**
     * Returns the item supper attribute.
     *
     * @return \Magestore\Webpos\Api\Data\Checkout\CartItemOptionInterface[] super attribute. Otherwise, null.
     */
    public function getSuperAttribute();
    
    /**
     * Sets the item supper group.
     *
     * @param \Magestore\Webpos\Api\Data\Checkout\CartItemOptionInterface[] $super_group
     * @return $this
     */
    public function setSuperGroup($super_group);
    
    /**
     * Returns the item supper group.
     *
     * @return \Magestore\Webpos\Api\Data\Checkout\CartItemOptionInterface[] super group. Otherwise, null.
     */
    public function getSuperGroup();
    
    /**
     * Sets the item custom options.
     *
     * @param \Magestore\Webpos\Api\Data\Checkout\CartItemOptionInterface[] $options
     * @return $this
     */
    public function setOptions($options);
    
    /**
     * Returns the item custom options.
     *
     * @return \Magestore\Webpos\Api\Data\Checkout\CartItemOptionInterface[] options. Otherwise, null.
     */
    public function getOptions();
    
    /**
     * Sets the item bundle option.
     *
     * @param \Magestore\Webpos\Api\Data\Checkout\CartItemOptionInterface[] $bundle_option
     * @return $this
     */
    public function setBundleOption($bundle_option);
    
    /**
     * Returns the item bundle option.
     *
     * @return \Magestore\Webpos\Api\Data\Checkout\CartItemOptionInterface[] bundle option. Otherwise, null.
     */
    public function getBundleOption();
    
    /**
     * Sets the item bundle option qty.
     *
     * @param \Magestore\Webpos\Api\Data\Checkout\CartItemOptionInterface[] $bundle_option_qty
     * @return $this
     */
    public function setBundleOptionQty($bundle_option_qty);
    
    /**
     * Returns the item bundle option qty.
     *
     * @return \Magestore\Webpos\Api\Data\Checkout\CartItemOptionInterface[] bundle option qty. Otherwise, null.
     */
    public function getBundleOptionQty();
    
    /**
     * Sets is custom sale item
     *
     * @param boolean $isCustomSale
     * @return $this
     */
    public function setIsCustomSale($isCustomSale);
    
    /**
     * Returns is custom sale item.
     *
     * @return boolean is custom sale. Otherwise, null.
     */
    public function getIsCustomSale();
    
    /**
     * Sets qty to ship
     *
     * @param float $qtyToShip
     * @return $this
     */
    public function setQtyToShip($qtyToShip);
    
    /**
     * Returns qty to ship.
     *
     * @return float qty to ship. Otherwise, null.
     */
    public function getQtyToShip();

    /**
     * get all data.
     *
     * @param string $key
     * @return \Magestore\Webpos\Api\Data\Checkout\DataObjectInterface[]
     */
    public function getItemData($key = false);
    
    /**
     * set item data.
     *
     * @param anyType $data
     * @return $this
     */
    public function setItemData($data);

    /**
     * get item extension data.
     *
     * @return \Magestore\Webpos\Api\Data\Checkout\ExtensionDataInterface[]
     */
    public function getExtensionData();

    /**
     * set item extension data.
     *
     * @param \Magestore\Webpos\Api\Data\Checkout\ExtensionDataInterface[] $data
     * @return $this
     */
    public function setExtensionData($extensionData);
    
    /**
     * get item extension data.
     *
     * @return string
     */
    public function getAmount();

    /**
     * set item extension data.
     *
     * @param string
     * @return $this
     */
    public function setAmount($amount);
    
    /**
     * get item extension data.
     *
     * @return string
     */
    public function getCreditPriceAmount();

    /**
     * set item extension data.
     *
     * @param string
     * @return $this
     */
    public function setCreditPriceAmount($amount);


    /**
     * Returns the item id.
     *
     * @return string|int id. Otherwise, null.
     */
    public function getItemId();

    /**
     * Sets the item id.
     *
     * @param string|int $itemId
     * @return $this
     */
    public function setItemId($itemId);


    /**
     * Returns the use discount.
     *
     * @return string|int id. Otherwise, null.
     */
    public function getUseDiscount();

    /**
     * Sets the use discount.
     *
     * @param string|int $useDiscount
     * @return $this
     */
    public function setUseDiscount($useDiscount);
}
