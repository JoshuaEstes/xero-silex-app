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
$app->get('/xero/invoice', function () use ($app) {
    $invoiceNumber = null;
    $currency      = null;
    $amountDue     = null;
    $shortCode     = null;
    /**
     * Validate the request using the shortcode and the organisation ShortCodes
     * match up.
     * Pull the invoice by invoiceNumber and amountDue from zero to validate
     * Request
     */

    /**
     * Create the BitPay invoice
     */

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
