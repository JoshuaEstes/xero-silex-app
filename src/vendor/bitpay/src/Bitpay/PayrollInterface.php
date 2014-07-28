<?php

interface Bitpay_PayoutInterface
{

    /**
     * @return string
     */
    public function getId();

    /**
     * @return array
     */
    public function getInstructions();

    /**
     * @return string
     */
    public function getAmount();

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @return string
     */
    public function getEffectiveDate();

    /**
     * @return string
     */
    public function getReference();

    /**
     * @return string
     */
    public function getPricingMethod();

    /**
     * @return string
     */
    public function getNotificationEmail();

    /**
     * @return string
     */
    public function getNotificationUrl();

    /**
     * @return string
     */
    public function getStatus();
}
