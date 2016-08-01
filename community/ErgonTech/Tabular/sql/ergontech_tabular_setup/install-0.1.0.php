<?php

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

$importProfileTableName = $installer->getTable('ergontech_tabular/import_profile');
$importProfileTable = $connection
    ->newTable($importProfileTableName)
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'primary' => true,
        'nullable' => false
    ], 'Import profile entity Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [], 'Profile name')
    ->addColumn('profile_type', Varien_Db_Ddl_Table::TYPE_TEXT, 64, [], 'Profile type Id')
    ->addColumn('extra', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', [], 'Extra serialized data')
    ->addIndex(
        $installer->getIdxName(
            $importProfileTableName, ['name'],
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        ['name'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE]);

$importProfileStoreTableName = $installer->getTable('ergontech_tabular/import_profile_store');
$importProfileStoreTable = $connection
    ->newTable($importProfileStoreTableName)
    ->addColumn('profile_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'unsigned' => true,
        'primary' => true,
        'nullable' => false
    ], 'Tabular profile Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, [
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ], 'Store Id')
    ->addIndex(
        $installer->getIdxName(
            $importProfileStoreTableName,
            ['store_id']
        ), ['store_id'])
    ->addForeignKey(
        $installer->getFkName(
            'ergontech_tabular/import_profile_store', 'profile_id', 'ergontech_tabular/import_profile', 'entity_id'),
        'profile_id',
        $importProfileTableName, 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'ergontech_tabular/import_profile_store', 'store_id', 'core/store', 'store_id'),
        'store_id',
        $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$connection->createTable($importProfileTable);
$connection->createTable($importProfileStoreTable);

$installer->endSetup();
