<?php

interface Bitpay_ConfigInterface
{

    public function set($key, $value);
    public function get($key);
}
