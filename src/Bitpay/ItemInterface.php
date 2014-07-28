<?php

/**
 * Item that was sold
 */
interface Bitpay_ItemInterface
{

    /**
     * @return string
     */
    public function getItemCode();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getPrice();

    /**
     * @return string
     */
    public function getQuantity();

    /**
     * @return boolean
     */
    public function isPhysical();
}
