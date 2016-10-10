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
 * Iways PayPalPlus Model Api2 Order
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Iways_PayPalPlus_Model_Api2_Order extends Mage_Sales_Model_Api2_Order
{
    /**
     * Add order payment method field to select
     *
     * @param Mage_Sales_Model_Resource_Order_Collection $collection
     * @return Iways_PayPalPlus_Model_Api2_Order
     */
    protected function _addPaymentMethodInfo(Mage_Sales_Model_Resource_Order_Collection $collection)
    {
        $collection->getSelect()->joinLeft(
            array('payment_method' => $collection->getTable('sales/order_payment')),
            'main_table.entity_id = payment_method.parent_id',
            array(
                'payment_method' => 'payment_method.method',
                'ppp_pui_reference_number',
                'ppp_pui_instruction_type',
                'ppp_pui_payment_due_date',
                'ppp_pui_note',
                'ppp_pui_bank_name',
                'ppp_pui_account_holder_name',
                'ppp_pui_international_bank_account_number',
                'ppp_pui_bank_identifier_code',
                'ppp_pui_routing_number',
                'ppp_pui_amount',
                'ppp_pui_currency'
            )
        );

        return $this;
    }
}