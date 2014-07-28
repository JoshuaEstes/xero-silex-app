<?php

/**
 * Invoice
 */
interface Bitpay_InvoiceInterface
{

    /**
     * An invoice starts in this state.  When in this state and only in this state, payments
     * to the associated bitcoin address are credited to the invoice.  If an invoice has 
     * received a partial payment, it will still reflect a status of new to the merchant 
     * (from a merchant system perspective, an invoice is either paid or not paid, partial
     * payments and over payments are handled by bitpay.com by either refunding the 
     * customer or applying the funds to a new invoice.
     */
    const STATUS_NEW       = 'new';

    /**
     * As soon as full payment (or over payment) is received, an invoice goes into the
     * paid status.
     */
    const STATUS_PAID      = 'paid';

    /**
     * The transaction speed preference of an invoice determines when an invoice is
     * confirmed.  For the high speed setting, it will be confirmed as soon as full 
     * payment is received on the bitcoin network (note, the invoice will go from a status
     * of new to confirmed, bypassing the paid status).  For the medium speed setting, 
     * the invoice is confirmed after the payment transaction(s) have been confirmed by
     * 1 block on the bitcoin network.  For the low speed setting, 6 blocks on the bitcoin
     * network are required.  Invoices are considered complete after 6 blocks on the 
     * bitcoin network, therefore an invoice will go from a paid status directly to a 
     * complete status if the transaction speed is set to low.
     */
    const STATUS_CONFIRMED = 'confirmed';

    /**
     * When an invoice is complete, it means that BitPay.com has credited the
     * merchant’s account for the invoice.  Currently, 6 confirmation blocks on the
     * bitcoin network are required for an invoice to be complete.  Note, in the future (for
     * qualified payers), invoices may move to a complete status immediately upon 
     * payment, in which case the invoice will move directly from a new status to a
     * complete status.
     */
    const STATUS_COMPLETE  = 'complete';

    /**
     * An expired invoice is one where payment was not received and the 15 minute
     * payment window has elapsed.
     */
    const STATUS_EXPIRED   = 'expired';

    /**
     * An invoice is considered invalid when it was paid, but payment was not confirmed
     * within 1 hour after receipt.  It is possible that some transactions on the bitcoin 
     * network can take longer than 1 hour to be included in a block.  In such 
     * circumstances, once payment is confirmed, BitPay.com will make arrangements 
     * with the merchant regarding the funds (which can either be credited to the 
     * merchant account on another invoice, or returned to the buyer).
     */
    const STATUS_INVALID   = 'invalid';

    /**
     * Code comment for each transaction speed
     */
    const TRANSACTION_SPEED_HIGH   = 'high';
    const TRANSACTION_SPEED_MEDIUM = 'medium';
    const TRANSACTION_SPEED_LOW    = 'low';

    /**
     * @return string
     */
    public function getPrice();

    /**
     * @return Bitpay_CurrencyInterface
     */
    public function getCurrency();

    /**
     * @return Bitpay_ItemInterface
     */
    public function getItem();

    /**
     * @return string
     */
    public function getTransactionSpeed();

    /**
     * @return string
     */
    public function getNotificationEmail();

    /**
     * @return string
     */
    public function getNotificationUrl();

    /**
     * @return string
     */
    public function getRedirectUrl();

    /**
     * @return array|object
     */
    public function getPosData();

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return boolean
     */
    public function isFullNotifications();

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return string
     */
    public function getBtcPrice();

    /**
     * @return DateTime
     */
    public function getInvoiceTime();

    /**
     * @return DateTime
     */
    public function getExpirationTime();

    /**
     * @return DateTime
     */
    public function getCurrentTime();

    /**
     * Wrappers for Bitpay_ItemInterface
     *
     * @deprecated
     */
    public function getOrderId();
    public function getItemDesc();
    public function getItemCode();
    public function isPhysical();

    /**
     * Wrapper functions for Bitpay_BuyerInterface
     *
     * @deprecated
     */
    public function getBuyerName();
    public function getBuyerAddress1();
    public function getBuyerAddress2();
    public function getBuyerCity();
    public function getBuyerState();
    public function getBuyerZip();
    public function getBuyerCountry();
    public function getBuyerEmail();
    public function getBuyerPhone();
}
