<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Sales_Model_Entity_Setup */
$installer = $this;
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_payment'), 'ppp_pui_reference_number', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment' => 'PayPal Plus PuI Reference Number',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_payment'), 'ppp_pui_instruction_type', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment' => 'PayPal Plus PuI Insctruction Type',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_payment'), 'ppp_pui_payment_due_date', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment' => 'PayPal Plus PuI Payment Due Date',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_payment'), 'ppp_pui_note', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment' => 'PayPal Plus PuI Note',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_payment'), 'ppp_pui_bank_name', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment' => 'PayPal Plus PuI Bank Name',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_payment'), 'ppp_pui_account_holder_name', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment' => 'PayPal Plus PuI Holder Name',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_payment'), 'ppp_pui_international_bank_account_number', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment' => 'PayPal Plus PuI International Bank Account Number',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_payment'), 'ppp_pui_bank_identifier_code', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment' => 'PayPal Plus PuI Bank Identifier Code',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_payment'), 'ppp_pui_routing_number', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment' => 'PayPal Plus PuI Routing Number',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_payment'), 'ppp_pui_amount', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment' => 'PayPal Plus PuI Amount',
    ));
$installer->getConnection()
    ->addColumn($installer->getTable('sales/order_payment'), 'ppp_pui_currency', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment' => 'PayPal Plus PuI Currency',
    ));
$installer->endSetup();
