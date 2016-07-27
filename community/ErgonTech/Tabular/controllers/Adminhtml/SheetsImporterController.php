<?php

class ErgonTech_Tabular_Adminhtml_SheetsImporterController extends Mage_Adminhtml_Controller_Action
{
    /**
     * ACL checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/ergontech_tabular/sheets_import');
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

}
