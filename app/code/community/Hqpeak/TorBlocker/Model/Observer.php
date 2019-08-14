<?php
	class Hqpeak_TorBlocker_Model_Observer extends Varien_Event_Observer
	{
		protected $_uri = null;
		protected $_url = null;
		protected $_template = null;
		protected $_request = null;
		protected $_options = null;
		protected $_time = null;
		protected $_timediff = null;
		protected $_table = null;
		protected $_read = null;
		protected $_write = null;
		
		public function _construct() {
			parent::_construct();		
			
			if ( !Mage::getStoreConfig('settings/main/time') ) {
				$this->_setTime();
			}
						
			$this->_table = Mage::getSingleton('core/resource')->getTableName('hqpeaktorblocker/ipaddress');
			$this->_read = Mage::getSingleton('core/resource')->getConnection('core/read');
			$this->_write = Mage::getSingleton('core/resource')->getConnection('core/write');
			$this->_timediff = Mage::getStoreConfig('settings/main/timeupdate');
		}
		
		public function serviceCheck($observer) {
			$this->_readData();
			$this->_updateService();
		}
		
		public function ipCheckVisit($observer) {
			$this->_allowVisit('visit');
			$this->_allowPost();
		}
		
		public function ipCheckUrl($observer) {
			$this->_urlVisit();
		}
		
		public function ipCheckFront($observer) {
			$this->_allowVisit('front');
			
		}
		
		public function ipCheckAdmin($observer) {
			$this->_allowVisit('admin');
		}
		
		public function checkRegister($observer) {
			$this->_allowVisit('register');
		}
		
		public function saveConfig($observer) {
			$url = Mage::getStoreConfig('settings/main/url');
			$urlCheck = Mage::getStoreConfig('settings/main/version');
				
			if ( !Mage::getStoreConfig('settings/configuration/check') ) {
				Mage::getConfig()->saveConfig('settings/main/check', 1);
			}
				
			if ( $url !== $urlCheck ) {
				Mage::getConfig()->saveConfig('settings/main/version', $url);
				$model->updateTable();
			}
		}
		
		protected function _readData() {
			$this->_uri = Mage::app()->getRequest()->getRequestUri();
			$this->_url = Mage::getStoreConfig('settings/main/url');
			$this->_time = Mage::getStoreConfig('settings/main/time');
			$this->_template = Mage::getStoreConfig('settings/main/errortemplate');
			$this->_request = Mage::getStoreConfig('settings/configuration/request');
			$this->_options = Mage::getStoreConfig('settings/configuration/options');
		}
		
		protected function _updateService() {
			$t = time();
			$diff = $t - $this->_time;
		
			if ( ($this->_url === 'http://hqpeak.com/torexitlist/free/?format=json' || 
				preg_match('/^http(s)?:\/\/(w{3}\.)?hqpeak.com(\/.+)+\?id=[0-9a-zA-Z]{40}&format=json/', $this->_url)) && 
				$diff > $this->_timediff ) {
		
				$model = Mage::getModel('hqpeaktorblocker/ipaddress');
				if ( !$model )
					die('Model cannot be loaded or doesn\'t exist');
		
		
				$ip_arr = $model->torGetIp($this->_url);
				if ( is_array($ip_arr) && sizeof($ip_arr) > 0 ) {
					if ( $this->_checkTable() ) {
						$sql = $this->_read->query("TRUNCATE TABLE $this->_table");
					} else {
						$sql = $this->_write->query("CREATE TABLE $this->_table");
					}
						
					$ip_long = $model->torToLong($ip_arr);
					$model->torFillTable($ip_long, $this->_write, $this->_table);
				}
		
				$this->_setTime();
			}
		}
		
		protected function _allowVisit($allow) {
			$opt_arr = explode(",", $this->_options);
			$check = false;
				
			if ( !empty($opt_arr) ) {
				foreach ( $opt_arr as $option ) {
					if ( $option == $allow ) {
						$check = true;
						break;
					}
				}
					
				if ( !$check ) {
					if ( $this->_ipCheck() ) {
						$this->_errorMsg();					
					}
				}
			}
		}
		
		protected function _allowPost() {
			$opt_arr = explode(",", $this->_options);
			$check = false;
			
			if ( !empty($opt_arr) ) {
				foreach ( $opt_arr as $option ) {
					if ( $option == "post" ) {
						$check = true;
						break;
					}
				}
				
				if ( !$check && Mage::app()->getRequest()->isPost() ) {
					if ( $this->_ipCheck() ) {
						$this->_errorMsg();
					}
				}
			}
		}
		
		protected function _urlVisit() {
			$requests_arr = explode(",", $this->_request);
			$check = false;
			
			if ( !empty($requests_arr) ) {
				foreach ( $requests_arr as $request ) {
					$req = trim(strtolower($request));
										
					if ( in_array($req, array_keys(Mage::app()->getRequest()->getPost())) || 
						in_array($req, array_keys(Mage::app()->getRequest()->getParams())) ||
						$req === Mage::app()->getRequest()->getModuleName() ||
						$req === Mage::app()->getRequest()->getControllerName() ||
						$req === Mage::app()->getRequest()->getActionName() ) 
					{
							$check = true;
							break;
					}
				}
						
				if ( $check ) {
					if ( $this->_ipCheck() ) {
						$this->_errorMsg();
					}
				}
			}
		}
		
		protected function _getUserIp($address)	{
			$user_ip = $address;
			return $user_ip;
		}
		
		protected function _ipCheck() {
			if ( $this->_checkTable() ) {
				if ( $this->_getUserIp(Mage::app()->getRequest()->getClientIp()) ) {
					$user2long = ip2long(Mage::app()->getRequest()->getClientIp());
					$tor_address = $this->_torUser($user2long);

					if ( $tor_address ) {
						return true;
					}
				}
		
				/*if ( $this->_getUserIp($_SERVER['HTTP_X_FORWARDED_FOR']) ) {
				 	$user2long = ip2long($this->_getUserIp($_SERVER['HTTP_X_FORWARDED_FOR']));
					$tor_address = $this->_torUser($user2long);
		
					if ( $tor_address ) {
						return true;
					}
				}*/
			} else {
				die("Table with ip addresses from tor exit list does not exist.");
			}
				
			return $this->_check;
		}
		
		protected function _torUser($ip_long) {
			$ip_long = Mage::getSingleton('core/resource')->getConnection('core/read')->quote($ip_long, Zend_Db::INT_TYPE);
			$sql = "SELECT * FROM $this->_table WHERE `ip`=$ip_long";
			$query = $this->_read->fetchOne($sql);
			return $query;
		}
		
		protected function _setTime() {
			Mage::getConfig()->saveConfig('settings/main/time', time());
		}
		
		protected function _checkTable() {
			return $this->_table;
		}
				
		protected function _updateTable() {
			$model = Mage::getModel('hqpeaktorblocker/ipaddress');	
			if ( !$model )
				die("Model cannot be loaded or doesn\'t exist");
				
			$model->setTable($this->_table);
			$model->setRead($this->_read);
			$model->setWrite($this->_write);
			$model->setVersion();
				
			if ( !$model->emptyData() ) {
				die("Failed to insert data in table");
			}
				
			$table = $model->getTable();
			$read = $model->getRead();
			$write = $model->getWrite();
			$default_version = $model->getVersion();
				
			$ip_arr = $model->torGetIp($default_version);
			if ( is_array($ip_arr) && !empty($ip_arr) ) {
				$ip_long = $model->torToLong($ip_arr);
				$model->torFillTable($ip_long, $write, $table);
				$this->_setTime();
			}
		}
				
		protected function _errorMsg() {
			echo htmlspecialchars_decode($this->_template);
			die();	
		}
	}
?>