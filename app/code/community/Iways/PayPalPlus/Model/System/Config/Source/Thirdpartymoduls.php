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
 * Magento payment methods for admin config
 *
 * @author robert
 */
class Iways_PayPalPlus_Model_System_Config_Source_Thirdpartymoduls
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();

        $methods = array(array('value' => '', 'label' => Mage::helper('adminhtml')->__('--Please Select--')));

        foreach ($payments as $paymentCode => $paymentModel) {
            if (strpos($paymentCode, 'paypal') !== false) {
                continue;
            }

            $paymentTitle = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
            if (empty($paymentTitle)) {
                $paymentTitle = $paymentCode;
            }
            $methods[$paymentCode] = array(
                'label' => $paymentTitle,
                'value' => $paymentCode,
            );
        }
        return $methods;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $payments = Mage::getSingleton('payment/config')->getAllMethods();

        $methods = array();

        foreach ($payments as $paymentCode => $paymentModel) {
            if ($paymentCode == Iways_PayPalPlus_Model_Payment::METHOD_CODE) {
                continue;
            }
            $paymentTitle = Mage::getStoreConfig('payment/' . $paymentCode . '/title');

            if (empty($paymentTitle)) {
                $paymentTitle = $paymentCode;
            }
            $methods[$paymentCode] = $paymentTitle;
        }
        return $methods;
    }
}