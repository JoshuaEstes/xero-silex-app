<?php

/**
 * Sends request(s) to bitpay server
 */
interface ClientInterface
{
    const TESTNET = '0x6F';
    const LIVENET = '0x00';

    /**
     * These can be changed/updated so when the request is sent to BitPay it
     * gives insight into what is making the calls.
     */
    const NAME    = 'BitPay PHP-Client';
    const VERSION = '0.0.0';

    public function createApplication(Bitpay_ApplicationInterface $application);

    public function createBill(Bitpay_BillInterface $bill);
    public function getBills($status = null);
    public function getBill($billId);
    public function updateBill(Bitpay_BillInterface $bill);

    public function createAccessToken(Bitpay_AccessTokenInterface $accessToken);
    public function getAccessTokens();
    public function getAccessToken($keyId);

    public function getCurrencies();

    public function createInvoice(Bitpay_InvoiceInterface $invoice);
    public function getInvoices();
    public function getInvoice($invoiceId);

    public function getLedgers();
    public function getLedger(Bitpay_CurrencyInterface $currency);

    public function getOrgs();
    public function getOrg($orgId);
    public function updateOrg(Bitpay_OrgInterface $org);

    public function createPayout(Bitpay_PayoutInterface $payout);
    public function getPayouts($status = null);
    public function getPayout($payoutId);
    public function updatePayout(Bitpay_PayoutInterface $payout);

    public function getRates(Bitpay_CurrencyInterface $currency = null);

    public function getTokens();

    public function getUser();
    public function updateUser(Bitpay_UserInterface $user);
}
