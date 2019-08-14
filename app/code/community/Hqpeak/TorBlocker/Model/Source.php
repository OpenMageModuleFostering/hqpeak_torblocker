<?php 
	class Hqpeak_TorBlocker_Model_Source
	{
		public function toOptionArray() {
			return array(
					array('value' => 'visit', 'label' => Mage::helper('hqpeaktorblocker')->__('Visits <small>(Read only public content on the site)</small>')),
					array('value' => 'front', 'label' => Mage::helper('hqpeaktorblocker')->__('Front-end <small>(Access only the front-end of the site)</small>')),
					array('value' => 'admin', 'label' => Mage::helper('hqpeaktorblocker')->__('Admin <small>(Access the administration dashboard)</small>')),
					array('value' => 'register', 'label' => Mage::helper('hqpeaktorblocker')->__('Registration <small>(Register for the site)</small>')),
					array('value' => 'post', 'label' => Mage::helper('hqpeaktorblocker')->__('Request <small>(Send POST requests)</small>'))
			);
		}
	}
?>