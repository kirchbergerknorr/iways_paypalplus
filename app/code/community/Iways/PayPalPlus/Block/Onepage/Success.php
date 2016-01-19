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
 * Iways PayPalPlus Onepage Success
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Iways_PayPalPlus_Block_Onepage_Success extends Mage_Checkout_Block_Onepage_Review
{
    /**
     * Current order to work with.
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Payment Code
     */
    const IWAYS_PAYPALPLUS_PAYMENT = 'iways_paypalplus_payment';
    /**
     * Caches given order.
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $orderId = Mage::getSingleton('checkout/type_onepage')->getCheckout()->getLastOrderId();
        $this->_order = Mage::getModel('sales/order')->load($orderId);
    }

    /**
     * Check if last order is PayPalPlus
     * @return bool
     */
    public function isPPP() {
        if($this->_order->getPayment()->getMethodInstance()->getCode() == self::IWAYS_PAYPALPLUS_PAYMENT) {
            return true;
        }
        return false;
    }
}