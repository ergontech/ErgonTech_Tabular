<?php

class ErgonTech_Tabular_Adminhtml_Tabular_ProfileController extends Mage_Adminhtml_Controller_Action
{
    /**
     * ACL checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/ergontech_tabular');
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $profile = Mage::getModel('ergontech_tabular/import_profile');

        if ($id) {
            $profile->load($id);
            if (!$profile->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('ergontech_tabular')
                        ->__("The import profile with id %d does not exist!", $id));
            }
        }
        Mage::register('ergontech_tabular_import_profile', $profile);

        $this->loadLayout();
        $this->_title($this->__('Tabular'))
            ->_title($this->__('Import Profiles'));

        $this
            ->_setActiveMenu('system/convert');

        $this->renderLayout();
    }

}
