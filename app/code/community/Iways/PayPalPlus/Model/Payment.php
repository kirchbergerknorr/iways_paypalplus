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
    const PPP_STATUS_APPROVED = 'approved';
    const METHOD_CODE = 'iways_paypalplus_payment';
    const PENDING = 'pending';

    const PPP_PUI_INSTRUCTION_TYPE = 'PAY_UPON_INVOICE';

    protected $_code = self::METHOD_CODE;
    protected $_formBlockType = 'iways_paypalplus/payment_form';
    protected $_infoBlockType = 'iways_paypalplus/payment_info';

    /**
     * Payment Method features
     * @var bool
     */
    protected $_isGateway = true;
    protected $_canOrder = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canUseInternal = false;
    protected $_canCapturePartial = false;
    protected $_canCaptureOnce = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canUseCheckout = true;


    /**
     * Authorize payment method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @throws Exception Payment could not be executed
     *
     * @return Iways_PayPalPlus_Model_Payment
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $paymentId = Mage::app()->getRequest()->getParam('paymentId');
        $payerId = Mage::app()->getRequest()->getParam('PayerID');
        try {
            if (Mage::getStoreConfig('payment/iways_paypalplus_payment/transfer_reserved_order_id')) {
                Mage::getModel('iways_paypalplus/api')->patchInvoiceNumber(
                    $paymentId,
                    $payment->getOrder()->getIncrementId()
                );
            }
        }catch (\Exception $e) {
            Mage::logException($e);
        }
        /**
         * @var \PayPal\Api\Payment $ppPayment
         */
        $ppPayment = Mage::getModel('iways_paypalplus/api')->executePayment(
            $paymentId,
            $payerId
        );

        Mage::getSingleton('customer/session')->setPayPalPaymentId(null);
        Mage::getSingleton('customer/session')->setPayPalPaymentPatched(null);

        if (!$ppPayment) {
            Mage::throwException('Payment could not be executed.');
        }

        if($paymentInstructions = $ppPayment->getPaymentInstruction()) {
            $payment->setData('ppp_pui_reference_number', $paymentInstructions->getReferenceNumber());
            $payment->setData('ppp_pui_instruction_type', $paymentInstructions->getInstructionType());
            $payment->setData(
                'ppp_pui_payment_due_date',
                Mage::helper('iways_paypalplus')->convertDueDate($paymentInstructions->getPaymentDueDate())
            );
            $payment->setData('ppp_pui_note', $paymentInstructions->getNote());

            $bankInsctructions = $paymentInstructions->getRecipientBankingInstruction();
            $payment->setData('ppp_pui_bank_name', $bankInsctructions->getBankName());
            $payment->setData('ppp_pui_account_holder_name', $bankInsctructions->getAccountHolderName());
            $payment->setData(
                'ppp_pui_international_bank_account_number',
                $bankInsctructions->getInternationalBankAccountNumber()
            );
            $payment->setData('ppp_pui_bank_identifier_code', $bankInsctructions->getBankIdentifierCode());
            $payment->setData('ppp_pui_routing_number', $bankInsctructions->getRoutingNumber());

            $ppAmount = $paymentInstructions->getAmount();
            $payment->setData('ppp_pui_amount', $ppAmount->getValue());
            $payment->setData('ppp_pui_currency', $ppAmount->getCurrency());
        }

        $transactionId = null;
        try {
            $transactions = $ppPayment->getTransactions();

            if($transactions && isset($transactions[0])) {
                $resource = $transactions[0]->getRelatedResources();
                if($resource && isset($resource[0])) {
                    $sale = $resource[0]->getSale();
                    $transactionId = $sale->getId();
                    if($sale->getState() == self::PENDING) {
                        $payment->setIsTransactionPending(true);
                    }
                }
            }

        } catch (Exception $e) {
            $transactionId = $ppPayment->getId();
        }
        $payment->setTransactionId($transactionId);
        $payment->setParentTransactionId($ppPayment->getId());

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
        $ppRefund = Mage::getModel('iways_paypalplus/api')->refundPayment(
            $this->_getParentTransactionId($payment),
            $amount
        );
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
        return $this->_getLastParentTransactionId($payment->getLastTransId());
    }


    /**
     * Retrieves the  last parent transaction id without a transaction (PayPal Pay-Id)
     *
     * @param $transactionId
     * @return mixed
     */
    protected function _getLastParentTransactionId($transactionId) {
        $transaction = Mage::getModel('sales/order_payment_transaction')->load($transactionId, 'txn_id');
        if($transaction && $transaction->getParentTxnId()) {
            return $this->_getLastParentTransactionId($transaction->getParentTxnId());
        }
        return $transactionId;
    }
}