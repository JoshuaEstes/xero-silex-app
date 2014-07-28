<?php

/**
 * Creates an access token for the given client
 */
interface Bitpay_AccessTokenInterface
{

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return boolean
     */
    public function isNonceDisabled();
}
