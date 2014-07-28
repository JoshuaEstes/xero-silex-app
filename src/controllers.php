<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', array());
})
->bind('homepage');

/**
 * When customer goes to pay the merchant, the request will first be sent to
 * this URL with the invoice details.
 *
 * @see http://developer.xero.com/documentation/api-guides/payment-services-integration-with-xero
 */
$app->get('/xero/invoice', function (Request $request) use ($app) {
    $invoiceNumber = $request->query->get('invoiceNumber');
    $currency      = $request->query->get('currency');
    $amountDue     = $request->query->get('amountDue');
    $shortCode     = $request->query->get('shortCode');
    $data          = null;

    //die(var_dump($request->query->all()));

    /**
     * Validate the request using the shortcode and the organisation ShortCodes
     * match up.
     * Pull the invoice by invoiceNumber and amountDue from zero to validate
     * Request
     */
    $config = array(
        'consumer_key'     => $app['xero.consumer_key'],
        'shared_secret'    => $app['xero.access_token'],
        'core_version'     => '2.0',
        'payroll_version'  => '1.0',
        'rsa_private_key'  => __DIR__ . '/../config/certs/privatekey.pem',
        'rsa_public_key'   => __DIR__ . '/../config/certs/publickey.cer',
        'application_type' => 'Private',
        'oauth_callback'   => 'oob',
        'user_agent'       => 'Private Xero App',
    );

    require_once __DIR__ . '/vendor/xero/lib/XeroOAuth.php';

    $client       = new XeroOAuth($config);
    $initialCheck = $client->diagnostics();
    $numErrors    = count($initialCheck);

    if ($numErrors > 0) {
        foreach ($initialCheck as $error) {
            $data .= '<pre>' . $error . '</pre>' . PHP_EOL;
        }

        return new Response($data);
    }

    $client->config['access_token'] = $app['xero.consumer_key'];
    $client->config['access_token_secret'] = $app['xero.access_token'];

    $response = $client
        ->request(
            'GET',
            $client->url('Organisation', 'core'),
            array('where' => sprintf('ShortCode=%s', $shortCode))
        );

    // Valid Organisation
    $organisation = $client->parseResponse($response['response'], $response['format'])->Organisations[0]->Organisation;

    //die(var_dump($organisation->Name));

    $response = $client
        ->request(
            'GET',
            $client->url('Invoices/' . $invoiceNumber, 'core'),
            array()
        );

    $invoice = $client->parseResponse($response['response'], $response['format']);

    /**
     * Create the BitPay invoice
     */
    require_once __DIR__ . '/vendor/bitpay/api-rest-client-alpha.php';
    $bitpay = new BitPay();

    /**
     * Use the invoice URL and redirect the customer to the bitpay invoice URL
     */

    return new RedirectResponse('http://test.bitpay.com/invoice?id=');
});

/**
 * An IPN will be received here which will update the invoice in xero with the
 * correct amounts.
 */
$app->post('/xero/ipn', function () use ($app) {
    /**
     * Apply payment to invoice
     */
});

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html',
        'errors/'.substr($code, 0, 2).'x.html',
        'errors/'.substr($code, 0, 1).'xx.html',
        'errors/default.html',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
