<?php
	class Hqpeak_TorBlocker_Model_Validation_UrlValidate extends Mage_Core_Model_Config_Data
	{
		public function save() {
			$url = $this->getValue();
			$urlCheck = Mage::getStoreConfig('settings/main/version');
			
			if ( $url === '' ) {
				Mage::throwException('The URL address field should not be empty');
			}
							
			if ( $url !== 'http://hqpeak.com/torexitlist/free/?format=json' && !preg_match('/^http(s)?:\/\/(w{3}\.)?hqpeak.com(\/.+)+\?id=[0-9a-zA-Z]{40}&format=json/', $url) ) {
				Mage::throwException('The URL address supplied is not valid');
			}
			
			return parent::save();
		}
	}
?>