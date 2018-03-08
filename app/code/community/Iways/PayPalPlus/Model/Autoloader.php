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
 * Autoloader for namespaces
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Iways_PayPalPlus_Model_Autoloader
{
    protected static $_registered = false;

    /**
     * Add autoloader
     *
     * @param Varien_Event_Observer $observer
     * @return \Iways_PayPalPlus_Model_Autoloader
     */
    public function addAutoloader(Varien_Event_Observer $observer)
    {
        if (self::$_registered) {
            return $this;
        }
        spl_autoload_register(array($this, 'autoload'), false, true);

        self::$_registered = true;
        return $this;
    }

    /**
     * Autoload
     *
     * @param string $class
     */
    public function autoload($class)
    {
        $classFile = str_replace('\\', DS, $class) . '.php';
        // Actually check if file exists in include path, do nothing otherwise
        if (stream_resolve_include_path($classFile) !== false) {
            include $classFile;
        }
    }
}