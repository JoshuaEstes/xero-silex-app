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
    require_once __DIR__ . '/vendor/bitpay-php-client/bp_lib.php';
    $posData = array(
        'i' => $invoiceNumber,
        's' => $shortCode,
    );
    $response = bpCreateInvoice(
        $invoiceNumber,
        $amountDue,
        array(
            'i' => $invoiceNumber,
            's' => $shortCode,
        ),
        array(
            'orderId'           => $invoiceNumber,
            'itemDesc'          => '',
            'itemCode'          => '',
            'notificationEmail' => '',
            'notificationURL'   => $app['url_generator']->generate('bitpay-ipn', array(), true),
            'redirectURL'       => '',
            'currency'          => $currency,
            'physical'          => '',
            'fullNotifications' => '',
            'transactionSpeed'  => '',
            'buyerName'         => '',
            'buyerAddress1'     => '',
            'buyerAddress2'     => '',
            'buyerCity'         => '',
            'buyerState'        => '',
            'buyerZip'          => '',
            'buyerEmail'        => '',
            'buyerPhone'        => '',
            'apiKey'            => $app['bitpay.api_key'],
        )
    );

    /**
     * Use the invoice URL and redirect the customer to the bitpay invoice URL
     */

    $app['monolog']->addDebug(json_encode($response));

    return new RedirectResponse($response['url']);
});

/**
 * An IPN will be received here which will update the invoice in xero with the
 * correct amounts.
 */
$app->post('/bitpay/ipn', function (Request $request) use ($app) {
    /**
     * Apply payment to invoice
     */
    $app['monolog']->addDebug((string) $request);

    $content       = json_decode($request->getContent(), true);
    $posData       = json_decode($content['posData'], true);
    $invoiceNumber = $posData['posData']['i'];
    $shortCode     = $posData['posData']['s'];

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
            $client->url('Invoices/' . $invoiceNumber, 'core'),
            array()
        );

    $invoice = $client->parseResponse($response['response'], $response['format'])->Invoices[0]->Invoice;

    $tmpl = <<<XML
<Payments>
  <Payment>
    <Invoice>
      <InvoiceID>%InvoiceID%</InvoiceID>
      <InvoiceNumber>%InvoiceNumber%</InvoiceNumber>
    </Invoice>
    <Account>
        <AccountID>%AccountID%</AccountID>
    </Account>
    <Reference>%Reference%</Reference>
    <Amount>%Amount%</Amount>
  </Payment>
</Payments>
XML;
    $xml = strtr(
        $tmpl,
        array(
            '%InvoiceID%'     => $invoice->InvoiceID,
            '%InvoiceNumber%' => $invoice->InvoiceNumber,
            '%AccountID%'     => 'CEEF66A5-A545-413B-9312-78A53CAADBC4',
            '%Reference%'     => $content['id'],
            '%Amount%'        => $content['price'],
        )
    );

    $response = $client
        ->request(
            'PUT',
            $client->url('Payments', 'core'),
            array(),
            $xml
        );

    $payment = $client->parseResponse($response['response'], $response['format'])->Payments[0]->Payment;

    $app['monolog']->addDebug($content);

    return new Response();
})->bind('bitpay-ipn');

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
