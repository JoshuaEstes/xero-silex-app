<?php

/**
 */
interface Bitpay_CurrencyInterface
{

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getSymbol();

    /**
     * @return string
     */
    public function getPrecision();

    /**
     * @return string
     */
    public function getExchangePctFee();

    /**
     * @return string
     */
    public function isPayoutEnabled();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getPluralName();

    /**
     * @return string
     */
    public function getAlts();

    /**
     * @return string
     */
    public function getPayoutFields();
}
