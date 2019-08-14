<?php
	class Hqpeak_TorBlocker_Model_Ipaddress extends Mage_Core_Model_Abstract
	{
		protected $_table = null;
		protected $_read = null;
		protected $_write = null;
		protected $_default_version = null;
		
		public function _construct() {
			parent::_construct();
			
			$this->_init('hqpeaktorblocker/ipaddress');
			
			$this->_table = Mage::getSingleton('core/resource')->getTableName('hqpeaktorblocker/ipaddress');
			$this->_read = Mage::getSingleton('core/resource')->getConnection('core/read');
			$this->_write = Mage::getSingleton('core/resource')->getConnection('core/write');
		}
		
		public function setTable($table) {
			$this->_table = $table;
		}
		
		public function getTable() {
			return $this->_table;
		}
		
		public function setRead($read) {
			$this->_read = $read;
		}
		
		public function getRead() {
			return $this->_read;
		}
		
		public function setWrite($write) {
			$this->_write = $write;
		}
		
		public function getWrite() {
			return $this->_write;
		}
		
		public function setVersion() {
			if ( !Mage::getStoreConfig('settings/main/url') ) {
				$this->_version = 'http://hqpeak.com/torexitlist/free/?format=json';
			} else {
				$this->_version = Mage::getStoreConfig('settings/main/url');
			}
		}
		
		public function getVersion() {
			return $this->_version;
		}
		
		public function emptyData() {
			if ( $this->_table ) {
				$sql = $this->_read->query("TRUNCATE TABLE $this->_table");
				return true;
			} 
			
			return false;
		}
		
		public function torGetIp($url) {
			$timeout = 5;
			$redirects = 0;
			$client = new Zend_Http_Client($url, array('maxredirects' => $redirects, 'timeout' => $timeout));
			$response = $client->request('POST');
			$data = $response->getBody();
			
			
			if ( $response->isError() )
				return array();
					
			//decode output as array
			$service_data = json_decode($data, true);
		
			//never trust the input - sanitate every ip
			if ( is_array($service_data) && $size = sizeof($service_data) > 0 ) {
				for ( $i=0; $i<$size; $i++ ) {
					if ( !preg_match("/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}/", $service_data[$i]) )
						$service_data[$i] = "0.0.0.0";
				}
			} else {
				return array();
			}
		
			return $service_data;
		}
		
		public function torToLong($arr) {
			if ( is_array($arr) ) {
				$ip2long = array();
					
				foreach ( $arr as $ip ) {
					if ( !in_array(ip2long($ip), $ip2long) )
						$ip2long[] = ip2long($ip);
				}
			} else {
				die("Bad data");
			}
		
			return $ip2long;
		}
		
		public function torFillTable($ip, $write, $table) {
			if ( is_array($ip) ) {
				$sql = "INSERT INTO $table (ip) VALUES ";
					
				foreach ( $ip as $dataItem ) {
					$dataItem = Mage::getSingleton('core/resource')->getConnection('core/read')->quote($dataItem, Zend_Db::INT_TYPE);
					$sql .= "(".$dataItem."), ";
				}
					
				$sql = rtrim($sql, ', ');
				$write->query($sql);
			} else {
				die("Bad data");
			}
		}
	}
?>