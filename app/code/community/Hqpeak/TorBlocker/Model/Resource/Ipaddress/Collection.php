<?php
	class Hqpeak_TorBlocker_Model_Resource_Ipaddress_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
	{
		public function _construct() {
			parent::_construct();
			$this->_init('hqpeaktorblocker/ipaddress');
		}
	}
?>	