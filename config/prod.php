<?php

// configure your app for the production environment

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');

/**
 * Xero Configuration
 */
$app['xero.consumer_key'] = 'XSPPQC1T19CTUHBGDR4ZVNRB0CHAN2';
$app['xero.access_token'] = 'AICJBW8LR6KSEMBQCCDUCVP4BD3PBJ';

/**
 * BitPay Configuration
 */
$app['bitpay.api_key'] = 'TCGluOrORmEtUudFPxy30DGuDGF7otFBEHP0UUyYBI';
