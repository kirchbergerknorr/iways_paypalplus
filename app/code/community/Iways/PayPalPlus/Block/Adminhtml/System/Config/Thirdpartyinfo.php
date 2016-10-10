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
 * Iways PayPalPlus Payment Block Adminhtml System Config Fieldset Payment THridparty Info
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Iways_PayPalPlus_Block_Adminhtml_System_Config_Thirdpartyinfo
    extends Mage_Paypal_Block_Adminhtml_System_Config_Fieldset_Expanded
{
    /**
     * @var
     */
    protected $_dummyElement;
    /**
     * @var
     */
    protected $_fieldRenderer;
    /**
     * @var
     */
    protected $_values;

    /**
     * Renders dynamic textfields.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);

        $thirdPartyMethods = Mage::getStoreConfig('payment/iways_paypalplus_payment/third_party_moduls');
        $thirdPartyMethods = explode(',', $thirdPartyMethods);

        $payments = Mage::getSingleton('payment/config')->getActiveMethods();

        foreach ($payments as $paymentCode => $paymentModel) {
            if (in_array($paymentCode, $thirdPartyMethods)) {
                $html .= $this->_getFieldHtml($element, $paymentModel);
            }
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }


    /**
     * Creates a dummy element
     * @return Varien_Object
     */
    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new Varien_Object(array(
                'show_in_default' => 1,
                'show_in_website' => 1,
                'show_in_store' => 1
            ));
        }
        return $this->_dummyElement;
    }


    /**
     * Returns field renderer
     *
     * @return Mage_Adminhtml_Block_System_Config_Form_Field
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_fieldRenderer;
    }


    /**
     * Renders a dynamic textfield
     *
     * @param $fieldset
     * @param $paymentModel
     * @return mixed
     */
    protected function _getFieldHtml($fieldset, $paymentModel)
    {
        $configData = $this->getConfigData();
        $path = 'payment/third_party_modul_info/text_' . $paymentModel->getCode();
        if (isset($configData[$path])) {
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = (int)(string)$this->getForm()->getConfigRoot()->descend($path);
            $inherit = true;
        }
        if (!$data) {
            $data = '';
        }

        $e = $this->_getDummyElement();

        $field = $fieldset->addField(
            $paymentModel->getCode(),
            'text',
            array(
                'name' => 'groups[third_party_modul_info][fields][text_' . $paymentModel->getCode() . '][value]',
                'label' => $paymentModel->getTitle(),
                'value' => $data,
                'inherit' => $inherit,
                'can_use_default_value' => $this->getForm()->canUseDefaultValue($e),
                'can_use_website_value' => $this->getForm()->canUseWebsiteValue($e),
            )
        )->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }
}