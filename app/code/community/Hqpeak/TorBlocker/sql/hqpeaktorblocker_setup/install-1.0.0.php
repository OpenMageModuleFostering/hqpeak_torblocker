<?php
	$installer = $this;
	$installer->startSetup();
	$installer->run("DROP TABLE IF EXISTS {$this->getTable('hqpeaktorblocker/ipaddress')}");
	$table = $installer->getConnection()
		->newTable($installer->getTable('hqpeaktorblocker/ipaddress'))
		->addColumn('ip', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
			'unsigned' => false,
			'nullable' => false,
			'primary' => true,
			'identity' => false,
		), 'IP Address');
	$installer->getConnection()->createTable($table);
	$installer->endSetup();
?>