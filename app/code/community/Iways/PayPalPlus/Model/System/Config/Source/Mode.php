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
 * PayPal Api Mode resource class
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Iways_PayPalPlus_Model_System_Config_Source_Mode
{

    /**
     * Live string
     */
    const LIVE = 'live';

    /**
     * Sandbox string
     */
    const SANDBOX = 'sandbox';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::LIVE, 'label' => Mage::helper('iways_paypalplus')->__('Live')),
            array('value' => self::SANDBOX, 'label' => Mage::helper('iways_paypalplus')->__('Sandbox')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::SANDBOX => Mage::helper('iways_paypalplus')->__('Sandbox'),
            self::LIVE => Mage::helper('iways_paypalplus')->__('Live'),
        );
    }
}