<?php

// configure your app for the production environment

$app->register(new \Silex\Provider\SessionServiceProvider());
$app->register(new \Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../var/logs/prod.log',
));
$app->register(new \Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_sqlite',
        'path'   => __DIR__ . '/app.db',
    )
));

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');

/**
 * Xero Configuration
 */
// Consumer Key
$app['xero.consumer_key']    = 'PUZJBQYTDKTY0I5CLRM4NGVQG3JFCG';
// Consumer Secret
$app['xero.consumer_secret'] = 'KC2MCOXVFCBKUGCGNCPB6B2BKPDUPT';
$app['xero.private_key']     = __DIR__ . '/certs/privatekey.pem';
$app['xero.public_key']      = __DIR__ . '/certs/publickey.cer';


/**
 * BitPay Configuration
 */
$app['bitpay.api_key'] = 'TCGluOrORmEtUudFPxy30DGuDGF7otFBEHP0UUyYBI';
