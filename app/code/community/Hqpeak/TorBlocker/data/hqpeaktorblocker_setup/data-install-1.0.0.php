<?php
	$checkUrl = Mage::getStoreConfig('settings/configuration/url');
	$model = Mage::getModel('hqpeaktorblocker/ipaddress');
	
	if ( $checkUrl && $checkUrl !== 'http://hqpeak.com/torexitlist/free/?format=json' && !preg_match('/^http(s)?:\/\/(w{3}\.)?hqpeak.com(\/.+)+\?id=[0-9a-zA-Z]{40}&format=json/', $checkUrl) )
		die('The URL address supplied is not valid');
	
	if ( !$model )
		die('Model cannot be loaded or doesn\'t exist');
	
	$model->setTable(Mage::getSingleton('core/resource')->getTableName('hqpeaktorblocker/ipaddress'));
	$model->setRead(Mage::getSingleton('core/resource')->getConnection('core/read'));
	$model->setWrite(Mage::getSingleton('core/resource')->getConnection('core/write'));
	$model->setVersion();
	
	$table = $model->getTable();
	$read = $model->getRead();
	$write = $model->getWrite();
	$default_version = $model->getVersion();
	
	$ip_arr = $model->torGetIp($default_version);
	
	if ( is_array($ip_arr) && !empty($ip_arr) ) {
		$ip_long = $model->torToLong($ip_arr);
		$model->torFillTable($ip_long, $write, $table);
	}
?>	