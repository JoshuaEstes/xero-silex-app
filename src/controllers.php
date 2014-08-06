<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

/**
 * Homepage that display the connect to xero button
 */
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

    // Find access tokens by short code
    $accessToken = $app['db']->fetchAssoc(
        'SELECT bitpay_api_key, token, token_secret FROM access_tokens WHERE org_short_code = ?',
        array($shortCode)
    );
    // IF NOT FOUND, throw an error

    /**
     * Validate the request using the shortcode and the organisation ShortCodes
     * match up.
     * Pull the invoice by invoiceNumber and amountDue from zero to validate
     * Request
     */
    $config = array(
        'consumer_key'        => $app['xero.consumer_key'],
        'shared_secret'       => $app['xero.access_token'],
        'core_version'        => $app['xero.core_version'],
        'payroll_version'     => $app['xero.payroll_version'],
        'application_type'    => $app['xero.application_type'],
        'user_agent'          => $app['xero.user_agent'],
        'oauth_callback'      => 'oob', // no callback
        'access_token'        => $accessToken['token'],
        'access_token_secret' => $accessToken['token_secret'],
    );

    require_once __DIR__ . '/vendor/xero/lib/XeroOAuth.php';

    $client       = new XeroOAuth($config);
    $initialCheck = $client->diagnostics();
    $numErrors    = count($initialCheck);

    if ($numErrors > 0) {
        $data = null;
        foreach ($initialCheck as $error) {
            $data .= '<pre>' . $error . '</pre>' . PHP_EOL;
        }

        return new Response($data);
    }

    $response = $client
        ->request(
            'GET',
            $client->url('Invoices/' . $invoiceNumber, 'core'),
            array()
        );
    // assume 200 response

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
        // @TODO Enter more information in based on the invoice
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
            'apiKey'            => $token['bitpay_api_key'],
        )
    );

    /**
     * Use the invoice URL and redirect the customer to the bitpay invoice URL
     */
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
    $content       = json_decode($request->getContent(), true);
    $posData       = json_decode($content['posData'], true);
    $invoiceNumber = $posData['posData']['i'];
    $shortCode     = $posData['posData']['s'];

    $accessToken = $app['db']->fetchAssoc(
        'SELECT account_id, token, token_secret FROM access_tokens WHERE org_short_code = ?',
        array($shortCode)
    );

    $config = array(
        'consumer_key'        => $app['xero.consumer_key'],
        'shared_secret'       => $app['xero.access_token'],
        'core_version'        => $app['xero.core_version'],
        'payroll_version'     => $app['xero.payroll_version'],
        'application_type'    => $app['xero.application_type'],
        'user_agent'          => $app['xero.user_agent'],
        'oauth_callback'      => 'oob', // no callback
        'access_token'        => $accessToken['token'],
        'access_token_secret' => $accessToken['token_secret'],
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

    $response = $client
        ->request(
            'GET',
            $client->url('Invoices/' . $invoiceNumber, 'core'),
            array()
        );
    // Assume status code 200

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
            '%AccountID%'     => $accessToken['account_id'],
            '%Reference%'     => $content['id'],
            '%Amount%'        => $content['price'],
        )
    );

    // Apply payment to invoice
    $response = $client
        ->request(
            'PUT',
            $client->url('Payments', 'core'),
            array(),
            $xml
        );
    // assume 200 status code

    $payment = $client->parseResponse($response['response'], $response['format'])->Payments[0]->Payment;

    return new Response();
})->bind('bitpay-ipn');

/**
 * Starts the oauth dance
 *
 * Sends merchant over to Xero
 */
$app->get('/xero/oauth', function (Request $request) use ($app) {
    $config = array(
        'consumer_key'     => $app['xero.consumer_key'],
        'shared_secret'    => $app['xero.consumer_secret'],
        'core_version'     => $app['xero.core_version'],
        'payroll_version'  => $app['xero.payroll_version'],
        'application_type' => $app['xero.application_type'],
        'oauth_callback'   => $app['url_generator']->generate('xero-oauth', array(), true),
        'user_agent'       => $app['xero.user_agent'],
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

    $params = array(
        'oauth_callback' => $app['url_generator']->generate('xero-oauth-verifier', array(), true),
    );
    $response = $client->request('GET', $client->url('RequestToken', ''), $params);
    // assume 200 status code

    $parsedResponse         = $client->extract_params($response['response']);
    $oauthToken             = $parsedResponse['oauth_token'];
    $oauthTokenSecret       = $parsedResponse['oauth_token_secret'];
    //$oauthCallbackConfirmed = $parsedResponse['oauth_callback_confirmed'];

    //$app['db']->insert('access_tokens', array(
    //    'token'        => $oauthToken,
    //    'token_secret' => $oauthTokenSecret,
    //));

    // Insert some information into the session
    $app['session']->set('oauth_token', $oauthToken);
    $app['session']->set('oauth_token_secret', $oauthTokenSecret);

    $authorizeUrl = sprintf(
        '%s?oauth_token=%s', // scope can be added here
        $client->url('Authorize', ''),
        $oauthToken
    );

    return new RedirectResponse($authorizeUrl);
})->bind('xero-oauth');

/**
 * Xero sends merchant back, merchant needs to configure a few things and then
 * they are sent back to xero.
 *
 * Merchant needs to select either 1) an Account to use or 2) Create a new account
 * for the user.
 */
$app->get('/xero/oauth/verifier', function (Request $request) use ($app) {
    //$oauthToken    = $request->query->get('oauth_token');
    //$oauthVerifier = $request->query->get('oauth_verifier');
    //$org           = $request->query->get('org'); // org API Key

    $config = array(
        'consumer_key'     => $app['xero.consumer_key'],
        'shared_secret'    => $app['xero.consumer_secret'],
        'core_version'     => $app['xero.core_version'],
        'payroll_version'  => $app['xero.payroll_version'],
        'application_type' => $app['xero.application_type'],
        'user_agent'       => $app['xero.user_agent'],
        'oauth_callback'   => $app['url_generator']->generate('xero-oauth', array(), true),
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

    // Error if session variables are not found
    $client->config['access_token']        = $app['session']->get('oauth_token');
    $client->config['access_token_secret'] = $app['session']->get('oauth_token_secret');

    $code = $client->request('GET', $client->url('AccessToken', ''), array(
        'oauth_verifier' => $request->query->get('oauth_verifier'),
        'oauth_token'    => $request->query->get('oauth_token'),
    ));
    // assume 200 status code

    $response         = $client->extract_params($code['response']);
    $oauthToken       = $response['oauth_token'];
    $oauthTokenSecret = $response['oauth_token_secret'];
    $oauthExpiresIn   = $response['oauth_expires_in'];
    $orgApiKey        = $response['xero_org_muid']; // org API Key
    $date             = new \DateTime();
    $date->modify(sprintf('+%s seconds', $oauthExpiresIn));

    // Updates the tokens used to sign requests
    $client->config['access_token']        = $oauthToken;
    $client->config['access_token_secret'] = $oauthTokenSecret;

    // get the short code for the Organisation
    $response = $client
        ->request(
            'GET',
            $client->url('Organisations', 'core'),
            array('where' => sprintf('APIKey=%s', $orgApiKey))
        );
    // assume status code 200

    $org       = $client->parseResponse($response['response'], $response['format']);
    $shortCode = $org->Organisations[0]->Organisation->ShortCode;
    // Error if short code not found?

    // Update database with new access token and access token secret. Need
    // to use the info in session for this;
    $app['db']->insert(
        'access_tokens',
        array(
            'org_short_code' => $shortCode,
            'org_api_key'    => $orgApiKey,
            'token'          => $oauthToken,
            'token_secret'   => $oauthTokenSecret,
            'expires_at'     => $date->format('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        )
    );

    // Set some new session variables
    $app['session']->set('oauth_token', $oauthToken);
    $app['session']->set('short_code', $shortCode);

    return new RedirectResponse(
        $app['url_generator']->generate('xero-setup', array(), true)
    );
})->bind('xero-oauth-verifier');

/**
 * Merchant has connected xero and bitpay, all that is left to do is to
 * configure the application
 */
$app->get('/xero/setup', function (Request $request) use ($app) {
    return $app['twig']->render(
        'setup.html',
        array()
    );
})->bind('xero-setup');

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
