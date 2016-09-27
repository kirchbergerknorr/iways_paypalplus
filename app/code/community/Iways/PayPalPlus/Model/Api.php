<?php

/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com and you will be sent a copy immediately.
 *
 * Created on 02.03.2015
 * Author Robert Hillebrand - hillebrand@i-ways.de - i-ways sales solutions GmbH
 * Copyright i-ways sales solutions GmbH © 2015. All Rights Reserved.
 * License http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Address;
use PayPal\Api\WebProfile;
use PayPal\Api\Presentation;
use PayPal\Api\Payment;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\InputFields;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\PayerInfo;
use PayPal\Api\ShippingAddress;
use PayPal\Api\PatchRequest;
use PayPal\Api\Patch;
use PayPal\Api\PaymentExecution;

/**
 * Iways PayPal Rest Api wrapper
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Iways_PayPalPlus_Model_Api
{
    /**
     * Webhook url already exists error code
     */
    const WEBHOOK_URL_ALREADY_EXISTS = 'WEBHOOK_URL_ALREADY_EXISTS';

    const PATCH_ADD = 'add';
    const PATCH_REPLACE = 'replace';

    /**
     * @var null|ApiContext
     */
    protected $_apiContext = null;

    /**
     * @var mixed|null
     */
    protected $_mode = null;

    /**
     * Prepare PayPal REST SDK ApiContent
     */
    public function __construct()
    {
        $this->setApiContext(null);
    }

    /**
     * Set api context
     *
     * @param $website
     * @return $this
     */
    public function setApiContext($website = null)
    {
        $this->_apiContext = new ApiContext(
            new OAuthTokenCredential(
                Mage::getStoreConfig('iways_paypalplus/api/client_id', $website),
                Mage::getStoreConfig('iways_paypalplus/api/client_secret', $website)
            )
        );

        $this->_mode = Mage::getStoreConfig('iways_paypalplus/api/mode', $website);
        $this->_apiContext->setConfig(
            array(
                'http.ConnectionTimeOut' => 30,
                'http.Retry' => 1,
                'mode' => $this->_mode,
                'log.LogEnabled' => Mage::getStoreConfig('dev/log/active', $website),
                'log.FileName' => Mage::getBaseDir('log') . DS . 'PayPal.log',
                'log.LogLevel' => 'INFO'
            )
        );

        $this->_apiContext->addRequestHeader('PayPal-Partner-Attribution-Id',
            Mage::getSingleton('iways_paypalplus/partner_config')->getPartnerId());
        return $this;
    }

    /**
     * Get ApprovalLink for curretn Quote
     *
     * @return string
     */
    public function getPaymentExperience()
    {
        $paymentExperience = Mage::registry('payment_experience');
        if ($paymentExperience === null) {
            $webProfile = $this->buildWebProfile();
            if ($webProfile) {
                $payment = $this->createPayment($webProfile, $this->getQuote());
                $paymentExperience = $payment ? $payment->getApprovalLink() : false;
            } else {
                $paymentExperience = false;
            }
            Mage::register('payment_experience', $paymentExperience);
        }
        return $paymentExperience;
    }

    /**
     * Get a payment
     *
     * @param string $paymentId
     * @return Payment
     */
    public function getPayment($paymentId)
    {
        return Payment::get($paymentId, $this->_apiContext);
    }

    /**
     * Create payment for curretn quote
     *
     * @param WebProfile $webProfile
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function createPayment($webProfile, $quote, $taxFailure = false)
    {
        $payer = $this->buildPayer($quote);

        $itemList = $this->buildItemList($quote, $taxFailure);

        $amount = $this->buildAmount($quote);

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setItemList($itemList);

        $baseUrl = Mage::getBaseUrl();
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($baseUrl . 'paypalplus/index/success')
            ->setCancelUrl(Mage::helper('checkout/url')->getCheckoutUrl());

        $payment = new Payment();
        $payment->setIntent("sale")
            ->setExperienceProfileId($webProfile->getId())
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        try {
            $response = $payment->create($this->_apiContext);
            Mage::getSingleton('customer/session')->setPayPalPaymentId($response->getId());
            Mage::getSingleton('customer/session')->setPayPalPaymentPatched(null);
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            if (!$taxFailure) {
                return $this->createPayment($webProfile, $quote, true);
            }
            Mage::helper('iways_paypalplus')->handleException($ex);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        return $response;
    }

    /**
     * Adding shipping address to an existing payment.
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function patchPayment($quote)
    {
        if (Mage::getSingleton('customer/session')->getPayPalPaymentId()) {
            $payment = Payment::get(Mage::getSingleton('customer/session')->getPayPalPaymentId(), $this->_apiContext);
            $patchRequest = new PatchRequest();

            $shippingAddress = $this->buildShippingAddress($quote);
            $addressPatch = new Patch();
            $addressPatch->setOp(self::PATCH_ADD);
            $addressPatch->setPath('/transactions/0/item_list/shipping_address');
            $addressPatch->setValue($shippingAddress);
            $patchRequest->addPatch($addressPatch);

            $payerInfo = $this->buildBillingAddress($quote);
            $payerInfoPatch = new Patch();
            $payerInfoPatch->setOp(self::PATCH_ADD);
            $payerInfoPatch->setPath('/potential_payer_info/billing_address');
            $payerInfoPatch->setValue($payerInfo);
            $patchRequest->addPatch($payerInfoPatch);

            $amount = $this->buildAmount($quote);
            $amountPatch = new Patch();
            $amountPatch->setOp(self::PATCH_REPLACE);
            $amountPatch->setPath('/transactions/0/amount');
            $amountPatch->setValue($amount);
            $patchRequest->addPatch($amountPatch);
            $response = $payment->update(
                $patchRequest,
                $this->_apiContext
            );

            return $response;
        }
        return false;
    }


    /**
     * Patches invoice number to PayPal transaction
     * (Magento order increment id)
     *
     * @param $paymentId
     * @param $invoiceNumber
     * @return bool
     */
    public function patchInvoiceNumber($paymentId, $invoiceNumber)
    {
        $payment = Payment::get($paymentId, $this->_apiContext);

        $patchRequest = new PatchRequest();

        $invoiceNumberPatch = new Patch();
        $invoiceNumberPatch->setOp('add');
        $invoiceNumberPatch->setPath('/transactions/0/invoice_number');
        $invoiceNumberPatch->setValue($invoiceNumber);
        $patchRequest->addPatch($invoiceNumberPatch);

        $response = $payment->update($patchRequest,
            $this->_apiContext);

        return $response;
    }

    /**
     * Execute an existing payment
     *
     * @param string $paymentId
     * @param string $payerId
     * @return boolean
     */
    public function executePayment($paymentId, $payerId)
    {
        try {
            $payment = $this->getPayment($paymentId);
            $paymentExecution = new PaymentExecution();
            $paymentExecution->setPayerId($payerId);
            return $payment->execute($paymentExecution, $this->_apiContext);
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            Mage::helper('iways_paypalplus')->handleException($ex);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        return false;
    }

    /**
     * Refund a payment
     *
     * @param type $paymentId
     * @param type $amount
     * @return type
     */
    public function refundPayment($paymentId, $amount)
    {
        $transactions = $this->getPayment($paymentId)->getTransactions();
        $relatedResources = $transactions[0]->getRelatedResources();
        $sale = $relatedResources[0]->getSale();
        $refund = new \PayPal\Api\Refund();

        $ppAmount = new Amount();
        $ppAmount
            ->setCurrency(Mage::app()->getStore()->getCurrentCurrencyCode())
            ->setTotal($amount);
        $refund->setAmount($ppAmount);

        return $sale->refund($refund, $this->_apiContext);
    }

    /**
     * Get a list of all registrated webhooks for $this->_apiContext
     *
     * @return bool|\PayPal\Api\WebhookList
     */
    public function getWebhooks()
    {
        $webhooks = new \PayPal\Api\Webhook();
        try {
            return $webhooks->getAll($this->_apiContext);
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            Mage::helper('iways_paypalplus')->handleException($ex);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        return false;
    }

    /**
     * Retrive an webhook event
     *
     * @param $webhookEventId
     * @return bool|\PayPal\Api\WebhookEvent
     */
    public function getWebhookEvent($webhookEventId)
    {
        try {
            $webhookEvent = new \PayPal\Api\WebhookEvent();
            return $webhookEvent->get($webhookEventId, $this->_apiContext);
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            Mage::helper('iways_paypalplus')->handleException($ex);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        return false;
    }

    /**
     * Get a list of all available event types
     *
     * @return bool|\PayPal\Api\WebhookEventTypeList
     */
    public function getWebhooksEventTypes()
    {
        $webhookEventType = new \PayPal\Api\WebhookEventType();
        try {
            return $webhookEventType->availableEventTypes($this->_apiContext);
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            Mage::helper('iways_paypalplus')->handleException($ex);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        return false;
    }

    /**
     * Creates a webhook
     *
     * @return bool|\PayPal\Api\Webhook
     */
    public function createWebhook()
    {
        $webhook = new \PayPal\Api\Webhook();
        $webhook->setUrl(Mage::helper('iways_paypalplus')->getWebhooksUrl());
        $webhookEventTypes = array();
        foreach (Mage::getModel('iways_paypalplus/webhook_event')->getSupportedWebhookEvents() as $webhookEvent) {
            $webhookEventType = new \PayPal\Api\WebhookEventType();
            $webhookEventType->setName($webhookEvent);
            $webhookEventTypes[] = $webhookEventType;
        }
        $webhook->setEventTypes($webhookEventTypes);
        $webhookData = $webhook->create($this->_apiContext);
        $this->saveWebhookId($webhookData->getId());
        return $webhookData;
    }

    /**
     * Delete webhook with webhookId for PayPal APP $this->_apiContext
     *
     * @param $webhookId
     * @return bool
     */
    public function deleteWebhook($webhookId)
    {
        $webhook = new \PayPal\Api\Webhook();
        $webhook->setId($webhookId);
        try {
            return $webhook->delete($this->_apiContext);
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            Mage::helper('iways_paypalplus')->handleException($ex);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        return false;
    }

    /**
     * Validate WebhookEvent
     *
     * @param $rawBody Raw request string
     * @return bool|\PayPal\Api\WebhookEvent
     */
    public function validateWebhook($rawBody)
    {
        try {
            $webhookEvent = new \PayPal\Api\WebhookEvent();
            return $webhookEvent->validateAndGetReceivedEvent($rawBody, $this->_apiContext);
        } catch (Exception $ex) {
            Mage::logException($ex);
            return false;
        }
    }


    /**
     * Build ShippingAddress from quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return ShippingAddress
     */
    protected function buildShippingAddress($quote)
    {
        $address = $quote->getShippingAddress();
        $addressCheckerArray = array(
            'setRecipientName' => $this->buildFullName($address),
            'setLine1' => implode(' ', $address->getStreet()),
            'setCity' => $address->getCity(),
            'setCountryCode' => $address->getCountryId(),
            'setPostalCode' => $address->getPostcode(),
            'setState' => $address->getRegion(),
        );
        $allowedEmpty = array('setPhone', 'setState');
        $shippingAddress = new ShippingAddress();
        foreach ($addressCheckerArray as $setter => $value) {
            if (empty($value) && !in_array($setter, $allowedEmpty)) {
                return false;
            }
            $shippingAddress->{$setter}($value);
        }
        return $shippingAddress;
    }

    /**
     * Build BillingAddress from quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return ShippingAddress
     */
    protected function buildBillingAddress($quote)
    {
        $address = $quote->getBillingAddress();
        $addressCheckerArray = array(
            'setLine1' => implode(' ', $address->getStreet()),
            'setCity' => $address->getCity(),
            'setCountryCode' => $address->getCountryId(),
            'setPostalCode' => $address->getPostcode(),
            'setState' => $address->getRegion(),
        );
        $allowedEmpty = array('setPhone', 'setState');
        $billingAddress = new Address();
        foreach ($addressCheckerArray as $setter => $value) {
            if (empty($value) && !in_array($setter, $allowedEmpty)) {
                return false;
            }
            $billingAddress->{$setter}($value);
        }

        return $billingAddress;
    }

    /**
     * Build Payer for payment
     *
     * @param $quote
     * @return Payer
     */
    protected function buildPayer($quote)
    {
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");
        return $payer;
    }

    /**
     * Build PayerInfo for Payer
     *
     * @param $quote
     * @return PayerInfo
     */
    protected function buildPayerInfo($quote)
    {
        $payerInfo = new PayerInfo();
        $address = $quote->getBillingAddress();
        if ($address->getFirstname()) {
            $payerInfo->setFirstName($address->getFirstname());
        }
        if ($address->getMiddlename()) {
            $payerInfo->setMiddleName($address->getMiddlename());
        }
        if ($address->getLastname()) {
            $payerInfo->setLastName($address->getLastname());
        }

        $billingAddress = $this->buildBillingAddress($quote);
        if ($billingAddress) {
            $payerInfo->setBillingAddress($billingAddress);
        }
        return $payerInfo;
    }

    /**
     * Get fullname from address
     *
     * @param  Mage_Sales_Model_Quote_Address $address
     * @return type
     */
    protected function buildFullName($address)
    {
        $name = array();
        if ($address->getFirstname()) {
            $name[] = $address->getFirstname();
        }
        if ($address->getMiddlename()) {
            $name[] = $address->getMiddlename();
        }
        if ($address->getLastname()) {
            $name[] = $address->getLastname();
        }
        return implode(' ', $name);
    }

    /**
     * Build ItemList
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return ItemList
     */
    protected function buildItemList($quote, $taxFailure)
    {
        $itemArray = array();
        $itemList = new ItemList();
        $currencyCode = $quote->getBaseCurrencyCode();

        if (!$taxFailure) {
            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                $item = new Item();
                if ($quoteItem->getQty() > 1) {
                    $item->setName($quoteItem->getName() . ' x' . $quoteItem->getQty());
                } else {
                    $item->setName($quoteItem->getName());
                }
                $item
                    ->setSku($quoteItem->getSku())
                    ->setCurrency($currencyCode)
                    ->setQuantity(1)
                    ->setPrice($quoteItem->getBaseRowTotal());

                $itemArray[] = $item;
            }

            $itemList->setItems($itemArray);
        }
        return $itemList;
    }

    /**
     * Build Amount
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Amount
     */
    protected function buildAmount($quote)
    {
        $details = new Details();
        $details->setShipping($quote->getShippingAddress()->getBaseShippingAmount())
            ->setTax(
                $quote->getBillingAddress()->getBaseTaxAmount()
                + $quote->getBillingAddress()->getBaseHiddenTaxAmount()
                + $quote->getShippingAddress()->getBaseTaxAmount()
                + $quote->getShippingAddress()->getBaseHiddenTaxAmount()
            )
            ->setSubtotal(
                $quote->getBaseSubtotal()
            );

        $totals = $quote->getTotals();
        if (isset($totals['discount']) && $totals['discount']->getValue()) {
            $details->setShippingDiscount(-$totals['discount']->getValue());
        }
        $amount = new Amount();
        $amount->setCurrency($quote->getBaseCurrencyCode())
            ->setDetails($details)
            ->setTotal($quote->getBaseGrandTotal());

        return $amount;
    }


    /**
     * Build WebProfile
     *
     * @return boolean|WebProfile
     */
    protected function buildWebProfile()
    {
        $webProfile = new WebProfile();
        if (Mage::getStoreConfig('iways_paypalplus/dev/web_profile_id')) {
            $webProfile->setId(Mage::getStoreConfig('iways_paypalplus/dev/web_profile_id'));
            return $webProfile;
        }
        try {
            $webProfile->setName('magento_' . microtime());
            $webProfile->setPresentation($this->buildWebProfilePresentation());
            $inputFields = new InputFields();
            $inputFields->setAddressOverride(1);
            $webProfile->setInputFields($inputFields);
            $response = $webProfile->create($this->_apiContext);
            $this->saveWebProfileId($response->getId());
            return $response;
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
            Mage::helper('iways_paypalplus')->handleException($ex);
        }
        return false;
    }

    /**
     * Build presentation
     *
     * @return Presentation
     */
    protected function buildWebProfilePresentation()
    {
        $presentation = new Presentation();
        $presentation->setBrandName(Mage::app()->getWebsite()->getName());
        $presentation->setLogoImage($this->getHeaderImage());
        $presentation->setLocaleCode(
            substr(
                Mage::getStoreConfig('general/locale/code'),
                3,
                2
            )
        );
        return $presentation;
    }

    /**
     * Get Header Logo for Web experience
     *
     * @return string
     */
    protected function getHeaderImage()
    {
        if (Mage::getStoreConfig('iways_paypalplus/api/hdrimg')) {
            return Mage::getStoreConfig('iways_paypalplus/api/hdrimg');
        }
        return Mage::getDesign()->getSkinUrl(
            Mage::getStoreConfig('design/header/logo_src'),
            array('_secure' => true)
        );
    }

    /**
     * Reset web profile id
     *
     * @return type
     */
    public function resetWebProfileId()
    {
        foreach (Mage::app()->getStores() as $store) {
            Mage::getModel('core/config')->saveConfig(
                'iways_paypalplus/dev/web_profile_id',
                false,
                'stores',
                $store->getId()
            );
        }
        Mage::app()->getCacheInstance()->cleanType('config');
        return true;
    }

    /**
     * Save WebProfileId
     *
     * @param string $id
     * @return boolean
     */
    protected function saveWebProfileId($id)
    {
        return Mage::helper('iways_paypalplus')->saveStoreConfig('iways_paypalplus/dev/web_profile_id', $id);
    }

    /**
     * Save WebhookId
     *
     * @param string $id
     * @return boolean
     */
    protected function saveWebhookId($id)
    {
        return Mage::helper('iways_paypalplus')->saveStoreConfig('iways_paypalplus/dev/webhook_id', $id);
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function getQuote()
    {
        return Mage::getSingleton('checkout/type_onepage')->getQuote();
    }

    /**
     * Get current customer
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer()
    {
        return Mage::helper('customer')->getCustomer();
    }

    /**
     * Check if PayPal credentails are valid for given configuration.
     *
     * Uses WebProfile::get_list()
     *
     * @param $website
     * @return bool
     */
    public function testCredentials($website)
    {
        try {
            $this->setApiContext($website);
            WebProfile::get_list($this->_apiContext);
            return true;
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('iways_paypalplus')->__('Provided credentials not valid.')
            );
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }
}