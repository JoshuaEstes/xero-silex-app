<?php

// configure your app for the production environment

$app->register(new \Silex\Provider\FormServiceProvider());
$app->register(new \Silex\Provider\SessionServiceProvider());
$app->register(new \Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../var/logs/prod.log',
));
$app->register(new \Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'user'   => 'root',
        'dbname' => 'xero',
    )
));

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');

/**
 * Xero Configuration
 */
$app['xero.consumer_key']     = 'PUZJBQYTDKTY0I5CLRM4NGVQG3JFCG';
$app['xero.consumer_secret']  = 'KC2MCOXVFCBKUGCGNCPB6B2BKPDUPT';
$app['xero.private_key']      = __DIR__ . '/certs/privatekey.pem';
$app['xero.public_key']       = __DIR__ . '/certs/publickey.cer';
$app['xero.application_type'] = 'Public';
$app['xero.user_agent']       = 'BitPay Partner App';
$app['xero.core_version']     = '2.0';
$app['xero.payroll_version']  = '1.0';
$app['xero.ssl_cert']         = '';
$app['xero.ssl_password']     = '';
$app['xero.ssl_key']          = '';


/**
 * BitPay Configuration
 */
