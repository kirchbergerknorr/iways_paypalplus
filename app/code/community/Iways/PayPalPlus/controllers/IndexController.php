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
 * Created on 05.03.2015
 * Author Robert Hillebrand - hillebrand@i-ways.de - i-ways sales solutions GmbH
 * Copyright i-ways sales solutions GmbH © 2015. All Rights Reserved.
 * License http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */

/**
 * PayPalPlus checkout controller
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Iways_PayPalPlus_IndexController extends Mage_Checkout_Controller_Action
{
    /**
     * Index
     */
    public function indexAction()
    {
        $this->_redirect('checkout/cart');
    }

    /**
     * success
     */
    public function successAction()
    {
        try {
            $this->getOnepage()->getQuote()->collectTotals()->save();
            $this->getOnepage()->saveOrder();
            $this->getOnepage()->getQuote()->save();
            $this->_redirect('checkout/onepage/success');
            return true;
        } catch (Exception $ex) {
            Mage::logException($ex);
        }
        Mage::getSingleton('checkout/session')->addError($this->__('There was an error with your payment.'));
        $this->_redirect('checkout/cart');
    }

    /**
     * Validate agreements bevor redirect to PayPal
     */
    public function validateAction()
    {
        if (version_compare(Mage::getVersion(), '1.8.0', '>=') && !$this->_validateFormKey()) {
            $response = array('status' => 'error', 'message' => 'Invalid form key.');
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            return;
        }
        $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
        if ($requiredAgreements) {
            $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
            $diff = array_diff($requiredAgreements, $postedAgreements);
            if ($diff) {
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            }
        }
        $result['success'] = true;
        $result['error'] = false;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Get Onepage checkout
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function getQuote()
    {
        return $this->getOnepage()->getQuote();
    }

    /**
     * Patch PayPalPayment
     */
    public function patchAction()
    {
        try {
            if (version_compare(Mage::getVersion(), '1.8.0', '>=') && !$this->_validateFormKey()) {
                $response = array('status' => 'error', 'message' => 'Invalid form key.');
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
                return;
            }
            if (Mage::helper('iways_paypalplus')->isIdevOsc()) {
                /* Save Idev_Onestepcheckout POST Data to Quote */
                $this->getLayout()->createBlock('Iways_PayPalPlus_Block_Idev_Checkout',
                    'iways_paypalplus_handle_post_block');
            } else {
                if (Mage::helper('iways_paypalplus')->isFirecheckout()) {
                    $this->getQuote()->setFirecheckoutCustomerComment($this->getRequest()->getPost('order-comment'));
                    $quote = $this->getQuote();
                    foreach (Mage::helper('checkoutfields')->getEnabledFields() as $fieldName => $fieldConfig) {
                        $value = (string)$this->getRequest()->getPost($fieldName);
                        $quote->setData($fieldName, $value);
                    }
                }

                $billing = $this->getRequest()->getPost('billing', array());
                $customerBillingAddressId = $this->getRequest()->getPost('billing_address_id', false);

                if (isset($billing['email'])) {
                    $billing['email'] = trim($billing['email']);
                }
                $this->getOnepage()->saveBilling($billing, $customerBillingAddressId);

                $shipping = $this->getRequest()->getPost('shipping', array());
                if ($billing['use_for_shipping']) {
                    $shipping = $billing;
                }
                $customerShippingAddressId = $this->getRequest()->getPost('shipping_address_id', false);
                $this->getOnepage()->saveShipping($shipping, $customerShippingAddressId);

                $this->getOnepage()->saveShippingMethod($this->getRequest()->getPost('shipping_method', ''));

                $this->getOnepage()->savePayment($this->getRequest()->getPost('payment', array()));
            }
            $responsePayPal = Mage::getModel('iways_paypalplus/api')->patchPayment($this->getOnepage()->getQuote());
            if ($responsePayPal) {
                $response = array('status' => 'success');
            } else {
                $response = array(
                    'status' => 'error',
                    'message' => $this->__('Please select an other payment method.')
                );
            }
        } catch (Exception $ex) {
            $response = array('status' => 'error', 'message' => $ex->getMessage());
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    /**
     * Listener for PayPal REST Webhooks
     */
    public function webhooksAction()
    {
        if (!$this->getRequest()->isPost()) {
            return;
        }
        try {
            sleep(20);
            /** @var \PayPal\Api\WebhookEvent $webhookEvent */
            $webhookEvent = Mage::getSingleton('iways_paypalplus/api')->validateWebhook($this->getRequest()->getRawBody());
            Mage::getModel('iways_paypalplus/webhook_event')->processWebhookRequest($webhookEvent);
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            Mage::logException($ex);
            $this->getResponse()->setHeader('HTTP/1.1', '503 Service Unavailable')->sendResponse();
            exit;
        } catch (Exception $e) {
            Mage::logException($e);
            $this->getResponse()->setHttpResponseCode(500);
        }
    }
}