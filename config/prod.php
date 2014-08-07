<?php

use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

// configure your app for the production environment

$app->register(new \Silex\Provider\FormServiceProvider());
$app->register(new \Silex\Provider\ValidatorServiceProvider());
$app->register(new \Silex\Provider\TranslationServiceProvider(), array(
    'translator.domains' => array(),
));
$app->register(new \Silex\Provider\SessionServiceProvider());
$app->register(new \Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../var/logs/prod.log',
    'monolog.level'   => \Monolog\Logger::DEBUG,
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
 * Session Configuration
 */
$app['pdo.dsn']      = 'mysql:dbname=xero';
$app['pdo.user']     = 'root';
$app['pdo.password'] = '';

$app['session.db_options'] = array(
    'db_table'      => 'session',
    'db_id_col'     => 'session_id',
    'db_data_col'   => 'session_value',
    'db_time_col'   => 'session_time',
);

$app['pdo'] = $app->share(function () use ($app) {
    $pdo = new \PDO(
        $app['pdo.dsn'],
        $app['pdo.user'],
        $app['pdo.password']
    );
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    return $pdo;
});

$app['session.storage.handler'] = $app->share(function () use ($app) {
    $handler =  new PdoSessionHandler(
        $app['pdo'],
        $app['session.db_options'],
        $app['session.storage.options']
    );


    return $handler;
});
