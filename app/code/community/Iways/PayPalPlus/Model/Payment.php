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
 * Description of Payment
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Iways_PayPalPlus_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'iways_paypalplus_payment';
    protected $_formBlockType = 'iways_paypalplus/form_payment';

    /**
     * Payment Method features
     * @var bool
     */
    protected $_isGateway = true;
    protected $_canOrder = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canCaptureOnce = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = false;
    protected $_canUseCheckout = true;

    const PPP_STATUS_APPROVED = 'approved';

    /**
     * Capture payment method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Iways_PayPalPlus_Model_Payment
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $paymentId = Mage::app()->getRequest()->getParam('paymentId');
        $payerId = Mage::app()->getRequest()->getParam('PayerID');

        $ppPayment = Mage::getModel('iways_paypalplus/api')->executePayment($paymentId,
            $payerId);

        Mage::getSingleton('customer/session')->setPayPalPaymentId(null);
        Mage::getSingleton('customer/session')->setPayPalPaymentPatched(null);

        if (!$ppPayment) {
            throw new Exception('Payment could not be executed.');
        }

        $payment->setTransactionId($ppPayment->getId());
        if($ppPayment->getState() == self::PPP_STATUS_APPROVED) {
            $payment->setStatus(self::STATUS_APPROVED);
        }
        return $this;
    }

    /**
     * Refund specified amount for payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Iways_PayPalPlus_Model_Payment
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $ppRefund = Mage::getModel('iways_paypalplus/api')->refundPayment($this->_getParentTransactionId($payment),
            $amount);
        $payment->setTransactionId($ppRefund->getId())->setTransactionClosed(1);
        return $this;
    }

    /**
     * Parent transaction id getter
     *
     * @param Varien_Object $payment
     * @return string
     */
    protected function _getParentTransactionId(Varien_Object $payment)
    {
        return $payment->getParentTransactionId() ? $payment->getParentTransactionId()
            : $payment->getLastTransId();
    }
}