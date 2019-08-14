<?php
	class Hqpeak_TorBlocker_Block_Adminhtml_System_Config_Checkbox extends Mage_Adminhtml_Block_System_Config_Form_Field
	{
		const CONFIG_PATH = 'settings/configuration/options';
		protected $_values = null;
		
		protected function _construct() {
			parent::_construct();
			$this->setTemplate('hqpeaktorblocker/system/config/checkbox.phtml');
		}
		
		protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
			$this->setNamePrefix($element->getName())->setHtmlId($element->getHtmlId());
			return $this->_toHtml();
		}
		
		public function getValues()	{
			$values = array();
			foreach (Mage::getSingleton('hqpeaktorblocker/source')->toOptionArray() as $value) {
				$values[$value['value']] = $value['label'];
			}
			return $values;
		}
		
		public function getIsChecked($name) {
			return in_array($name, $this->getCheckedValues());
		}
		
		public function getCheckedValues() {
			$data = explode(",", Mage::getStoreConfig(self::CONFIG_PATH));
			$this->_values = $data;
			return $this->_values;
		}
	}
?>
