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
 * Iways PayPalPlus Helper
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Iways_PayPalPlus_Helper_Data extends Mage_Payment_Helper_Data
{

    /**
     * Show Exception if debug mode.
     *
     * @param Exception $e
     */
    public function handleException(Exception $e)
    {
        if (Mage::getStoreConfig('iways_paypalplus/dev/debug')) {
            Mage::getSingleton('core/session')->addWarning($e->getData());
        }
    }

    /**
     * Build webhook listener url
     *
     * @return string
     */
    public function getWebhooksUrl()
    {
        return str_replace(
            'http://',
            'https://',
            Mage::getUrl(
                'paypalplus/index/webhooks',
                array(
                    '_forced_secure' => true,
                    '_nosid' => true,
                    '_store' => Mage::app()->getDefaultStoreView()->getCode()
                )
            )
        );
    }


    /**
     * Get url wrapper for security urls and form key
     *
     * @param $url
     * @param array $params
     * @param bool|true $formKey
     * @return string
     */
    public function getUrl($url, $params = array(), $formKey = true)
    {
        $isSecure = Mage::app()->getRequest()->isSecure();
        if ($isSecure) {
            $params['_forced_secure'] = true;
        } else {
            $params['_secure'] = true;
        }
        if ($formKey) {
            $params['form_key'] = Mage::getSingleton('core/session')->getFormKey();
        }
        return Mage::getUrl($url, $params);
    }

    /**
     * Get deafult country id for different supported checkouts
     *
     * @return mixed
     */
    public function getDefaultCountryId()
    {
        if ($this->isFirecheckout()) {
            return Mage::getStoreConfig('firecheckout/general/country');
        }
        if ($this->isMagestoreOsc()) {
            return Mage::getStoreConfig('onestepcheckout/general/country_id');
        }
        if ($this->isIdevOsc()) {
            return Mage::getStoreConfig('onestepcheckout/general/default_country');
        }
        return Mage::getStoreConfig('payment/account/merchant_country');
    }

    /**
     * Helper for saving store configuration programmatically
     *
     * @param $key
     * @param $value
     * @return boolean
     */
    public function saveStoreConfig($key, $value)
    {
        Mage::getModel('core/config')->saveConfig(
            $key,
            $value,
            'stores',
            Mage::app()->getStore()->getId()
        );
        Mage::app()->getCacheInstance()->cleanType('config');
        return true;
    }

    /**
     * Request payment experience from PayPal for current quote.
     *
     * @return string
     */
    public function getPaymentExperience()
    {
        if (Mage::getStoreConfig('payment/iways_paypalplus_payment/active')) {
            return Mage::getModel('iways_paypalplus/api')->getPaymentExperience();
        }
        return false;
    }

    /**
     * Check if Idev_OneStepCheckout is enabled and active
     *
     * @return bool
     */
    public function isIdevOsc()
    {
        return (
            Mage::helper('core')->isModuleEnabled('Idev_OneStepCheckout')
            && Mage::getStoreConfig('onestepcheckout/general/rewrite_checkout_links')
        );
    }

    /**
     * Checks if TM_Firecheckout is enabled and active
     *
     * @return bool
     */
    public function isFirecheckout()
    {
        return (
            Mage::helper('core')->isModuleEnabled('TM_FireCheckout')
            && Mage::getStoreConfig('firecheckout/general/enabled')
        );
    }

    /**
     * Checks if Magestore_Onestepcheckout is enabled and active
     *
     * @return bool
     */
    public function isMagestoreOsc()
    {
        return (
            Mage::helper('core')->isModuleEnabled('Magestore_Onestepcheckout')
            && Mage::getStoreConfig('onestepcheckout/general/active')
        );
    }

    /**
     * Checks if Awesome is enabled and active
     *
     * @return bool
     */
    public function isAwesomeCheckout()
    {
        return (
            Mage::helper('core')->isModuleEnabled('AnattaDesign_AwesomeCheckout')
        );
    }

    /**
     * Checks if Amasty_Scheckout is enabled and active
     *
     * @return bool
     */
    public function isAmastyScheckout()
    {
        return (
            Mage::helper('core')->isModuleEnabled('Amasty_Scheckout')
        );
    }

    /**
     * Checks if Iwd_Onestepcheckout is enabled and active
     *
     * @return bool
     */
    public function isIwdOsc()
    {
        return (
            Mage::helper('core')->isModuleEnabled('IWD_Opc')
            && Mage::getStoreConfig('opc/global/status')
        );
    }

    /**
     * Convert due date
     *
     * @param $date
     * @return string
     */
    public function convertDueDate($date)
    {
        $dateArray = explode('-', $date);
        $dateArray = array_reverse($dateArray);
        return implode('.', $dateArray);
    }
}