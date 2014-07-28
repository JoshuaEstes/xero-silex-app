<?php

/**
 * Exchange rate
 */
interface Bitpay_RateInterface
{

    /**
     * @return Bitpay_Currency
     */
    public function getCurrency();

    /**
     * The full display name of the currency.
     *
     * @return string
     */
    public function getCode();

    /**
     * The three letter code for the currency, in all caps.
     *
     * @return string
     */
    public function getName();

    /**
     * The numeric exchange rate of this currency provided by the
     * BitPay server.
     *
     * @return string
     */
    public function getRate();
}
