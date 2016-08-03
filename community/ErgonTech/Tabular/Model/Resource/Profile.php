<?php

class ErgonTech_Tabular_Model_Resource_Profile extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('ergontech_tabular/profile', 'entity_id');
        $this->_serializableFields = [
            'extra' => [null, []]
        ];
    }

    public function lookupStoreIds($id)
    {
        /** @var Varien_Db_Adapter_Interface $read */
        $read = $this->_getReadAdapter();

        $select = $read->select()
            ->from($this->getTable('ergontech_tabular/profile_store'), 'store_id')
            ->where('profile_id = ?', $id);

        return $read->fetchCol($select);
    }

    /**
     * Check whether we need to update store associations with object
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $write = $this->_getWriteAdapter();
        $table = $this->getTable('ergontech_tabular/profile_store');

        $current = $this->lookupStoreIds($object->getId());
        $newStore = (array)$object->getData('stores');

        // Find new things
        $insertStores = array_diff($newStore, $current);

        // Find things not present
        $deleteStores = array_diff($current, $newStore);

        if ($deleteStores) {
            $write->delete($table, [
                'profile_id = ?' => $object->getId(),
                'store_id IN (?)' => $deleteStores
            ]);
        }

        if ($insertStores) {
            $write->insertMultiple($table, array_map(function ($store) use($object) {
                return [
                    'profile_id' => $object->getId(),
                    'store_id' => $store
                ];
            }, $insertStores));
        }

        return parent::_afterSave($object);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $object
     * @return string|Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $stores = [
                $object->getStoreId(),
                Mage_Core_Model_App::ADMIN_STORE_ID
            ];

            $select
                ->join(
                    ['tps' => $this->getTable('ergontech_tabular/profile_store')],
                    $this->getMainTable() . '.entity_id = tps.profile_id',
                    ['store_id'])
                ->where('tps.store_id IN (?)', $stores)
                ->order('store_id DESC')
                ->limit(1);
        }

        return $select;
    }


    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
            $object->setData('stores', $stores);
        }

        return parent::_afterLoad($object);
    }
}
