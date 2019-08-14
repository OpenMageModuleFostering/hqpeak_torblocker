<?php
	class Hqpeak_TorBlocker_Block_Adminhtml_Information extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
	{
		public function render(Varien_Data_Form_Element_Abstract $element) {
			$helper = Mage::helper('hqpeaktorblocker');
			$moduleId = 'Hqpeak_TorBlocker';
			$name = $this->_getConfigValue($moduleId, 'name');
			$version = $this->_getConfigValue($moduleId, 'version');
			$description = $this->_getConfigValue($moduleId, 'description');
			$extensionPage = $this->_getConfigValue($moduleId, 'extlink');
			
			$html =
				'<style>
					.information {width: 600px;}
                	.information-label {color: #000000; font-weight: bold; width: 150px;}
                	.information-text { padding-bottom: 15px;}
            	</style>';
			
			$html .= '
            	<table cellspacing="0" cellpading="0" class="information">
                	<tr>
                    	<td class="information-label">'.$helper->__('Extension:').'</td>
                    	<td class="information-text"><strong>'.$name.'</strong></td>
                	</tr>
                    <tr>
                    	<td class="information-label">'.$helper->__('Version:').'</td>
                    	<td class="information-text"><strong>'.$version.'</strong></td>
                	</tr>
                	<tr>
                    	<td class="information-label">'.$helper->__('Short Description:').'</td>
                    	<td class="information-text">'.$description.'</td>
                	</tr>
                	<tr>
                    	<td class="information-label">'.$helper->__('Download Page:').'</td>
                    	<td class="information-text">'.$extensionPage.'</td>
               	 	</tr>
            	</table>';
			
			return $html;
		}
		
		protected function _getConfigValue($module, $config) {
			$locale = Mage::app()->getLocale()->getLocaleCode();
			$defaultLocale = 'en_US';
			$mainConfig = Mage::getConfig();
			$moduleConfig = $mainConfig->getNode('modules/'.$module.'/'.$config);
		
			if ( (string) $moduleConfig ) {
				return $moduleConfig;
			}
		
			if ( $moduleConfig->$locale ) {
				return $moduleConfig->$locale;
			} else {
				return $moduleConfig->$defaultLocale;
			}
		}
	}
?>