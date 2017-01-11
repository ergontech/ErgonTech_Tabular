<?php

use Psr\Log\LogLevel;

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

    protected function _initAction()
    {
        $this->loadLayout();
        $this->_title($this->__('Tabular'))
            ->_title($this->__('Profiles'))
            ->_setActiveMenu('system/convert');
    }

    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $profile = Mage::getModel('ergontech_tabular/profile');

        if ($id) {
            $profile->load($id);
            if (!$profile->getId()) {
                $this->addProfileLoadErrorMessage($id);
                $this->_redirect('*/*/');
                return;
            }
        }


        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $profile->setData($data);
        }
        Mage::register('ergontech_tabular_profile', $profile);

        $this->_initAction();
        $this->_title($profile->getId() ? $profile->getName() : $this->__('New Profile'));

        $this->renderLayout();
    }

    public function runAction()
    {
        /** @var Mage_Core_Controller_Request_Http $request */
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->_redirect('*/*/');
            return;
        }

        /** @var \ErgonTech\Tabular\Model_Profile $profile */
        $profile = Mage::getModel('ergontech_tabular/profile');
        $id = $request->getPost('entity_id');

        $profile->load($id);

        if (!$profile->getId()) {
            $this->addProfileLoadErrorMessage($id);
            $this->_redirect('*/*/');
            return;
        }

        /** @var ErgonTech\Tabular\Model_Profile_Type $profileType */
        $profileType = Mage::helper('ergontech_tabular/profile_type_factory')->createProfileTypeInstance($profile);

        /** @var \Monolog\Logger $logger */
        $logger = Mage::helper('ergontech_tabular/monolog')->getLogger($profile->getProfileType());
        $outputHandler = new \Monolog\Handler\StreamHandler(fopen('php://output', 'w'));
        $outputHandler->setLevel(Mage::getIsDeveloperMode() ?LogLevel::DEBUG :LogLevel::INFO);

        $logger->pushHandler($outputHandler);

        try {
            $profileType->execute();
            $logger->log(LogLevel::INFO,
                sprintf('%s finished.', $profile->getName()));
        } catch (\Exception $e) {
            $logger->log(LogLevel::ERROR, sprintf('%s failed.', $profile->getName()));
            $logger->log(LogLevel::NOTICE, $e->getMessage());
        }
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {

            $id = $this->getRequest()->getParam('block_id');
            $profile = Mage::getModel('ergontech_tabular/profile')->load($id);
            if (!$profile->getId() && $id) {
                $this->addProfileLoadErrorMessage($id);
                $this->_redirect('*/*/');
                return;
            }


            $profile->setData($data);

            // try to save it
            try {
                // save the data
                $profile->save();
                // display success message
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ergontech_tabular')->__('The profile has been saved.'));
                // clear previously saved data from session
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('entity_id' => $profile->getId()));
                    return;
                }
                // go to grid
                $this->_redirect('*/*/');
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                // save data in session
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                // redirect to edit form
                $this->_redirect('*/*/edit', array('entity_id' => $this->getRequest()->getParam('entity_id')));
                return;
            }
        }
    }

    /**
     * Delete the profile
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('entity_id')) {
            try {
                // init model and delete
                $model = Mage::getModel('ergontech_tabular/profile');
                $model->load($id);
                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('')->__('Unable to find a Profile to delete.'));
                }
                $model->delete();
                // display success message
                $this->_getSession()->addSuccess(
                    Mage::helper('ergontech_tabular')->__('The profile has been deleted.')
                );
                // go to grid
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('ergontech_tabular')->__('An error occurred while deleting profile data. Please review log and try again.')
                );
                Mage::logException($e);
            }
            // redirect to edit form
            $this->_redirect('*/*/', array('id' => $id));
            return;
        }
        // display error message
        $this->_getSession()->addError(
            Mage::helper('ergontech_tabular')->__('Unable to find a profile to delete.')
        );
        // go to grid
        $this->_redirect('*/*/');
    }

    /**
     * @param $id
     */
    protected function addProfileLoadErrorMessage($id)
    {
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('ergontech_tabular')
                ->__('The profile with id %d does not exist!', $id));
    }
}
