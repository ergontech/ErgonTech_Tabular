<?php

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$connection = $installer->getConnection();

$profileTableName = $installer->getTable('ergontech_tabular/profile');
$profileTable = $connection
    ->newTable($profileTableName)
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, [
        'identity' => true,
        'unsigned' => true,
        'primary' => true,
        'nullable' => false
    ], 'Profile entity Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [], 'Profile name')
    ->addColumn('profile_type', Varien_Db_Ddl_Table::TYPE_TEXT, 64, [], 'Profile type Id')
    ->addColumn('extra', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', [], 'Extra serialized data')
    ->addIndex(
        $installer->getIdxName(
            $profileTableName, ['name'],
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        ['name'],
        ['type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE]);

$profileStoreTableName = $installer->getTable('ergontech_tabular/profile_store');
$profileStoreTable = $connection
    ->newTable($profileStoreTableName)
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
            $profileStoreTableName,
            ['store_id']
        ), ['store_id'])
    ->addForeignKey(
        $installer->getFkName(
            'ergontech_tabular/profile_store', 'profile_id', 'ergontech_tabular/profile', 'entity_id'),
        'profile_id',
        $profileTableName, 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            'ergontech_tabular/profile_store', 'store_id', 'core/store', 'store_id'),
        'store_id',
        $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    );

$connection->createTable($profileTable);
$connection->createTable($profileStoreTable);

$installer->endSetup();
