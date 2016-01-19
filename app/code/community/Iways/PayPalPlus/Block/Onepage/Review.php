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
 * Iways PayPalPlus Onepage Payment Methods Block
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Iways_PayPalPlus_Block_Onepage_Review extends Mage_Checkout_Block_Onepage_Review
{
    /**
     * Default PayPal Plus OnePage Review template for firecheckout
     */
    const FIRECHECKOUT_TEMPLATE = 'paypalplus/firecheckout/checkout/review.phtml';

    /**
     * Override template file
     * @return string
     */
    public function getTemplate()
    {
        if (Mage::getStoreConfig('payment/iways_paypalplus_payment/active') && Mage::helper('core')->isModuleEnabled('TM_FireCheckout') && Mage::getStoreConfig('firecheckout/general/enabled')) {
            return self::FIRECHECKOUT_TEMPLATE;
        }
        return parent::getTemplate();
    }
}