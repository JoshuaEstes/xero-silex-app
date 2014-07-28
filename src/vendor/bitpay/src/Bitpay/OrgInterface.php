<?php

/**
 */
interface Bitpay_OrgInterface
{

    /**
     * @return string
     */
    public function getPlanId();

    /**
     * @return string
     */
    public function getSmsNumber();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getRegion();

    /**
     * @return string
     */
    public function getContactName();

    /**
     * @return string
     */
    public function getCartPosSoftware();

    /**
     * @return string
     */
    public function getPricingCurrency();

    /**
     * @return string
     */
    public function getPayoutCurrency();

    /**
     * @return string
     */
    public function getPayoutPercentange();

    /**
     * @return string
     */
    public function getOrderEmail();

    /**
     * @return string
     */
    public function getTransactionSpeed();

    /**
     * @return boolean
     */
    public function getNotifyOnPaid();

    /**
     * @return boolean
     */
    public function getNotifyOnComplete();

    /**
     * @return string
     */
    public function getName();

    /**
     * $address = array($lineOne, $lineTwo);
     *
     * @return array
     */
    public function getAddress();

    /**
     * @return string
     */
    public function getCity();

    /**
     * @return string
     */
    public function getZip();

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @return boolean
     */
    public function isNonProfit();

    /**
     * @return string
     */
    public function getUsTaxId();

    /**
     * @return string
     */
    public function getIndustry();

    /**
     * @return string
     */
    public function getWebsite();

    /**
     * @return string
     */
    public function getCartPos();

    /**
     * @return string
     */
    public function getAffiliateOid();
}
