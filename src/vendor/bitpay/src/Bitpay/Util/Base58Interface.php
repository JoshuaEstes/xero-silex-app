<?php

interface Bitpay_Util_Base58Interface
{
    public function encode($data);
    public function decode($data);
}
