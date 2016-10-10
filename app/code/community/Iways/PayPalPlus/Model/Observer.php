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
 * Created on 03.03.2015
 * Author Robert Hillebrand - hillebrand@i-ways.de - i-ways sales solutions GmbH
 * Copyright i-ways sales solutions GmbH © 2015. All Rights Reserved.
 * License http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */

/**
 * Iways PayPalPlus Observer
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Iways_PayPalPlus_Model_Observer
{

    /**
     * Webhook url already exists error code
     */
    const WEBHOOK_URL_ALREADY_EXISTS = 'WEBHOOK_URL_ALREADY_EXISTS';

    /**
     * Add shipping address if payment is iways_paypalplus_payment
     *
     * @param Varien_Event_Observer $observer
     * @return Iways_PayPalPlus_Model_Observer
     */
    public function patchPayment(Varien_Event_Observer $observer)
    {
        try {
            $quote = $observer->getEvent()->getQuote();
            if (
                !Mage::getSingleton('customer/session')->getPayPalPaymentPatched()
                && $quote->getPayment()
                && $quote->getPayment()->getMethodInstance()->getCode() == Iways_PayPalPlus_Model_Payment::METHOD_CODE
            ) {
                Mage::getModel('iways_paypalplus/api')->patchPayment($quote);
            }
        } catch (Exception $ex) {
            if ($ex->getMessage() != 'The requested Payment Method is not available.') {
                Mage::logException($ex);
            }
        }
        return $this;
    }

    /**
     * Resets WebProfile ID on config save
     * @param Varien_Event_Observer $observer
     * @return \Iways_PayPalPlus_Model_Observer
     */
    public function resetWebProfile(Varien_Event_Observer $observer)
    {
        $this->validateCredentials($observer);
        Mage::getModel('iways_paypalplus/api')->resetWebProfileId();
        return $this;
    }

    /**
     * Check Webhook
     *
     * @param Varien_Event_Observer $observer
     * @return boolean Success
     */
    public function checkWebhook(Varien_Event_Observer $observer)
    {
        $api = Mage::getModel('iways_paypalplus/api')->setApiContext($this->getDefaultStoreId($observer));

        try {
            $api->createWebhook();
            return true;
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
            if ($ex->getData()) {
                $data = Mage::helper('core')->jsonDecode($ex->getData());
                if (isset($data['name']) && $data['name'] == self::WEBHOOK_URL_ALREADY_EXISTS) {
                    return true;
                }
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('iways_paypalplus')->__('Webhook creation failed. Error: %s',
                        isset($data['details'][0]['issue']) ? $data['details'][0]['issue'] : $ex->getMessage())
                );
            }
            Mage::helper('iways_paypalplus')->handleException($ex);
            return false;
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('iways_paypalplus')->__('Webhook creation failed.')
            );
            return false;
        }
    }

    /**
     * Validate PayPal credentials
     *
     * @param Varien_Event_Observer $observer
     * @throws Mage_Core_Exception
     */
    protected function validateCredentials(Varien_Event_Observer $observer)
    {
        Mage::getModel('iways_paypalplus/api')->testCredentials($this->getDefaultStoreId($observer));
    }

    /**
     * Try to get default store id from observer
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     * @throws Mage_Core_Exception
     */
    protected function getDefaultStoreId(Varien_Event_Observer $observer)
    {
        $website = $observer->getWebsite();
        if ($website) {
            $website = Mage::app()
                ->getWebsite($website)
                ->getDefaultGroup()
                ->getDefaultStoreId();
        }
        return $website;
    }
}