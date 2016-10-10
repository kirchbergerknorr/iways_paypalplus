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
class Iways_PayPalPlus_Block_Payment_Info extends Mage_Payment_Block_Info
{

    /**
     * Set PayPal Plus template in construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paypalplus/payment/info.phtml');
    }

    /**
     * Render as PDF
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('paypalplus/payment/pdf/info.phtml');
        return $this->toHtml();
    }

    /**
     * Prepare information specific to current payment method
     *
     * @param Varien_Object|array $transport
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $info = array();

        if (!$this->getIsSecureMode()) {
            $info[Mage::helper('iways_paypalplus')->__('Transaction ID')] = $this->getInfo()->getLastTransId();
        }

        if($this->isPUI()) {
            $info[Mage::helper('iways_paypalplus')->__('Account holder')] = $payment->getData('ppp_pui_account_holder_name');
            $info[Mage::helper('iways_paypalplus')->__('Bank')] = $payment->getData('ppp_pui_bank_name');
            $info[Mage::helper('iways_paypalplus')->__('IBAN')] = $payment->getData('ppp_pui_international_bank_account_number');
            $info[Mage::helper('iways_paypalplus')->__('BIC')] = $payment->getData('ppp_pui_bank_identifier_code');
            $info[Mage::helper('iways_paypalplus')->__('Reference number')] = $payment->getData('ppp_pui_reference_number');
            $info[Mage::helper('iways_paypalplus')->__('Payment due date')] = $payment->getData('ppp_pui_payment_due_date');
        }
        return $transport->addData($info);
    }

    /**
     * Checks if PayPal Plus payment is PUI
     *
     * @return bool
     */
    public function isPUI()
    {
        return ($this->getInfo()->getData('ppp_pui_instruction_type') == Iways_PayPalPlus_Model_Payment::PPP_PUI_INSTRUCTION_TYPE) ? true : false;
    }
}