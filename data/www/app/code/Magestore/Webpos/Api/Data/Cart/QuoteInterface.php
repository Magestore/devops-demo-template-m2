<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Api\Data\Cart;

interface QuoteInterface
{
    /*
     * quote id.
     */
    const QUOTE_ID = 'quote_id';
    /*
     * customer id.
     */
    const CUSTOMER_ID = 'customer_id';
    /*
     * currency id.
     */
    const CURRENCY_ID = 'currency_id';
    /*
     * till id.
     */
    const TILL_ID = "till_id";
    /*
     * store id.
     */
    const STORE_ID = "store_id";

    /**
     * Sets quote id
     *
     * @param string $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);
    
    /**
     * Gets quote id
     *
     * @return string.
     */
    public function getQuoteId();

    /**
     * Sets customer id
     *
     * @param string $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Gets quote id
     *
     * @return string.
     */
    public function getCustomerId();

    /**
     * Sets currency id
     *
     * @param string $currencyId
     * @return $this
     */
    public function setCurrencyId($currencyId);

    /**
     * Gets currency id
     *
     * @return string.
     */
    public function getCurrencyId();

    /**
     * Sets till id
     *
     * @param string $tillId
     * @return $this
     */
    public function setTillId($tillId);

    /**
     * Gets till id
     *
     * @return string.
     */
    public function getTillId();

    /**
     * Sets store id
     *
     * @param string $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Gets store id
     *
     * @return string.
     */
    public function getStoreId();

}
