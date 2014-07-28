<?php

// configure your app for the production environment

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');

/**
 * Xero Configuration
 */
$app['xero.consumer_key'] = 'MJRMYWE1ODIWMMEZNGRKMGI5MDG0ZD';
$app['xero.access_token'] = 'V41GRVMZN0OGIXOHNANNEIYK7APAGD';

/**
 * BitPay Configuration
 */
$app['bitpay.api_key'] = 'TCGluOrORmEtUudFPxy30DGuDGF7otFBEHP0UUyYBI';
