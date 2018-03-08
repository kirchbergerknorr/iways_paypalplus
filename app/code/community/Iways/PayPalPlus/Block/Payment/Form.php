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

/**
 * Iways PayPalPlus Payment Block
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Iways_PayPalPlus_Block_Payment_Form extends Mage_Payment_Block_Form
{
    /**
     * PayPalPlus Payment method code
     */
    const IWAYS_PAYPALPLUS_PAYMENT = 'iways_paypalplus_payment';

    /**
     * Templates for third party methods
     */
    const THIRDPARTY_TEMPLATE = 'thirdPartyPaymentMethods: [%s],';
    const THIRDPARTY_METHOD_TEMPLATE =
        '{"redirectUrl":"%s", "methodName": "%s", "imageUrl": "%s", "description": "%s"}';

    /**
     * Byte marks to check payment method availability.
     */
    const CHECK_USE_FOR_COUNTRY = 1;
    const CHECK_USE_FOR_CURRENCY = 2;
    const CHECK_USE_CHECKOUT = 4;
    const CHECK_USE_FOR_MULTISHIPPING = 8;
    const CHECK_USE_INTERNAL = 16;
    const CHECK_ORDER_TOTAL_MIN_MAX = 32;
    const CHECK_RECURRING_PROFILES = 64;
    const CHECK_ZERO_TOTAL = 128;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paypalplus/form/payment.phtml');
    }

    /**
     * Request payment experience from PayPal for current quote.
     *
     * @return string
     */
    public function getPaymentEperience()
    {
        return Mage::helper('iways_paypalplus')->getPaymentExperience();
    }

    /**
     * Construct third party method json string with all needed information for PayPal.
     *
     * @return string
     */
    public function getThirdPartyMethods()
    {
        $thirdPartyMethods = Mage::getStoreConfig('payment/iways_paypalplus_payment/third_party_moduls');
        if (!empty($thirdPartyMethods)) {
            $thirdPartyMethods = explode(',', $thirdPartyMethods);
            $activePamentMethods = $this->getMethods();
            $renderMethods = array();
            foreach ($activePamentMethods as $activePaymentMethod) {
                if (in_array($activePaymentMethod->getCode(), $thirdPartyMethods)) {
                    $renderMethods[] = sprintf(
                        self::THIRDPARTY_METHOD_TEMPLATE,
                        $this->getCheckoutUrl() . $activePaymentMethod->getCode(),
                        $activePaymentMethod->getTitle(),
                        '',
                        Mage::getStoreConfig('payment/third_party_modul_info/text_' . $activePaymentMethod->getCode())
                    );
                }
            }
            return sprintf(
                self::THIRDPARTY_TEMPLATE,
                implode(', ', $renderMethods)
            );
        }
        return '';
    }

    /**
     * Build Json Object for payment name and code.
     *
     * Used for third party method selection.
     *
     * @return string
     */
    public function getThirdPartyJsonObject()
    {
        $methods = $this->getMethods();
        $methodsArray = array();
        foreach ($methods as $method) {
            $methodsArray[$method->getTitle()] = $method->getCode();
        }
        return json_encode($methodsArray);
    }

    /**
     * Build Method Json Object for payment code and name.
     *
     * Used for third party method selection.
     *
     * @return string
     */
    public function getThirdPartyMethodJsonObject()
    {
        $thirdPartyMethods = Mage::getStoreConfig('payment/iways_paypalplus_payment/third_party_moduls');
        $renderMethods = array();
        if (!empty($thirdPartyMethods)) {
            $thirdPartyMethods = explode(',', $thirdPartyMethods);
            $activePaymentMethods = $this->getMethods();
            foreach ($activePaymentMethods as $activePaymentMethod) {
                if (in_array($activePaymentMethod->getCode(), $thirdPartyMethods)) {
                    $renderMethods[$activePaymentMethod->getCode()] = $activePaymentMethod->getTitle();
                }
            }
        }
        return json_encode($renderMethods);
    }

    /**
     * Check payment method model
     *
     * @param Mage_Payment_Model_Method_Abstract $method
     * @return bool
     */
    protected function _canUseNewMethod($method)
    {
        return $method->isApplicableToQuote(
            $this->getQuote(),
            self::CHECK_USE_FOR_COUNTRY | self::CHECK_USE_FOR_CURRENCY | self::CHECK_ORDER_TOTAL_MIN_MAX
        );
    }

    /**
     * Retrieve available payment methods
     *
     * with versionswitch
     *
     * @return array
     */
    public function getMethods()
    {
        $methods = $this->getData('methods');
        if ($methods === null) {
            if (
                (Mage::getEdition() == MAGE::EDITION_COMMUNITY && version_compare(Mage::getVersion(), '1.8.0', '>='))
                || (Mage::getEdition() == MAGE::EDITION_ENTERPRISE &&  version_compare(Mage::getVersion(), '1.13.0', '>='))
            ){
                $methods = $this->getNewMethods();
            } else {
                $methods = $this->getOldMethods();
            }
            $this->setData('methods', $methods);
        }
        return $methods;
    }

    /**
     * Returns all payment methods which are allowed for current quote
     *
     * Magento > 1.8.0
     *
     * @return array
     */
    public function getNewMethods()
    {
        $quote = $this->getQuote();
        $store = $quote ? $quote->getStoreId() : null;
        $methods = array();
        foreach (Mage::helper('payment')->getStoreMethods($store, $quote) as $method) {
            if ($method->getCode() == self::IWAYS_PAYPALPLUS_PAYMENT) {
                continue;
            }
            if ($this->_canUseNewMethod($method)
                && $method->isApplicableToQuote($quote, Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL)
            ) {
                $methods[] = $method;
            }
        }
        return $methods;
    }

    /**
     * Returns all payment methods which are allowed for current quote
     *
     * Magento < 1.8.0
     *
     * @return array
     */
    public function getOldMethods()
    {
        $quote = $this->getQuote();
        $store = $quote ? $quote->getStoreId() : null;
        $methods = Mage::helper('payment')->getStoreMethods($store, $quote);
        $total = $quote->getBaseSubtotal() + $quote->getShippingAddress()->getBaseShippingAmount();
        foreach ($methods as $key => $method) {
            if (!$this->_canUseOldMethod($method) && !($total != 0 || $method->getCode()
                    == 'free' || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles()))
            ) {
                unset($methods[$key]);
            }
        }
        return $methods;
    }

    /**
     * Check payment method model
     *
     * Magento < 1.8.0
     *
     * @param Mage_Payment_Model_Method_Abstract $method
     * @return bool
     */
    protected function _canUseOldMethod($method)
    {
        if (!$method->canUseForCountry($this->getQuote()->getBillingAddress()->getCountry())) {
            return false;
        }

        if (!$method->canUseForCurrency($this->getQuote()->getStore()->getBaseCurrencyCode())) {
            return false;
        }

        /**
         * Checking for min/max order total for assigned payment method
         */
        $total = $this->getQuote()->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if ((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total
                    > $maxTotal))
        ) {
            return false;
        }
        return true;
    }

    /**
     * Get frontend language
     *
     * @return string
     */
    public function getLanguage()
    {
        return Mage::getStoreConfig('general/locale/code');
    }

    /**
     * Get country for current quote
     *
     * @return string
     */
    public function getCountryId()
    {
        $billingAddress = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();
        if ($billingAddress) {
            $countryId = $billingAddress->getCountryId();
        } else {
            $countryId = Mage::helper('iways_paypalplus')->getDefaultCountryId();
        }
        return $countryId;
    }

    /**
     * Return quote for current customer.
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Return Magento checkout url
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return Mage::helper('checkout/url')->getCheckoutUrl();
    }

    /**
     * Checks if pui should be forced in sandbox mode
     *
     * @return mixed
     */
    public function isPuiSandboxMode()
    {
        return Mage::getStoreConfig('iways_paypalplus/dev/pui_sandbox');
    }

    /**
     * Should show loading indicator?
     * @return mixed
     */
    public function  showLoadingIndicator()
    {
        return Mage::getStoreConfig('payment/iways_paypalplus_payment/show_loading_indicator');
    }

    /**
     * Get current PayPal payment id
     *
     * @return mixed
     */
    public function getPayPalPaymentId() {
        return Mage::getSingleton('customer/session')->getPayPalPaymentId();
    }

}