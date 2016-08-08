<?php

class ErgonTech_Tabular_Block_Adminhtml_Profile_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('tabularProfileGrid');
        $this->setDefaultSort('Name');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        /* @var $collection ErgonTech_Tabular_Model_Resource_Profile_Collection */
        $collection = Mage::getResourceModel('ergontech_tabular/profile_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('ergontech_tabular');
        $this->addColumn('name', array(
            'header'    => $helper->__('Name'),
            'align'     => 'left',
            'index'     => 'name',
        ));

        $this->addColumn('profile_type', array(
            'header'    => $helper->__('Profile Type'),
            'align'     => 'left',
            'index'     => 'profile_type'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => $helper->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback'
                => array($this, '_filterStoreCondition'),
            ));
        }

        return parent::_prepareColumns();
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('entity_id' => $row->getId()));
    }

}
