<?php
	class Hqpeak_TorBlocker_Model_Validation_TimeValidate extends Mage_Core_Model_Config_Data
	{
		public function save() {
			$time = $this->getValue();
			$url = Mage::getStoreConfig('settings/main/url');
			
			if ( $time === '' ) {
				Mage::throwException('The time interval field should not be empty');
			}
						
			if ( ($url === 'http://hqpeak.com/torexitlist/free/?format=json' && $time < 18000) || 
				(preg_match('/^http(s)?:\/\/(w{3}\.)?hqpeak.com(\/.+)+\?id=[0-9a-zA-Z]{40}&format=json/', $url) && $time < 300) ) 
			{
				Mage::throwException('The time interval is too short');
			}
			
			return parent::save();
		}
	}
?>