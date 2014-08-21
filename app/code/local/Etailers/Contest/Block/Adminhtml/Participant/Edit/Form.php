<?php

class Etailers_Contest_Block_Adminhtml_Participant_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                                      'method' => 'post',
        							  'enctype' => 'multipart/form-data'
                                   )
      );
      
      $fieldset = $form->addFieldset('edit_form', array('legend'=>Mage::helper('contest')->__('Participant Information')));
     
      $fieldset->addField('contest_participant_name', 'text', array(
          'label'     => Mage::helper('contest')->__('Name'),
          'readonly'  => true,
          'name'      => 'contest_participant_name',
      ));
      
      $fieldset->addField('contest_participant_email', 'text', array(
          'label'     => Mage::helper('contest')->__('Email'),
          'readonly'  => true,
          'name'      => 'contest_participant_email',
      ));
      
      $fieldset->addField('contest_participant_informed', 'checkbox', array(
          'label'     => Mage::helper('contest')->__('Participant Informed'),
          'value'     => 1,
          'disabled'  => true,
          'name'      => 'contest_participant_informed'
      ));
      
       /**
		* Check is single store mode
		*/
		if (!Mage::app()->isSingleStoreMode()) {
			$fieldset->addField('store_id', 'select', array(
				'name'      => 'stores[]',
				'label'     => Mage::helper('cms')->__('Store View'),
				'title'     => Mage::helper('cms')->__('Store View'),
				'disabled'  => true,
				'readonly'  => true,
				'after_element_html' => '<small>'.Mage::helper('contest')->__('Only for information, this cannot be changed.').'</small>',
				'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
				'disabled'  => $isElementDisabled
			));
		}
		else {
			$fieldset->addField('store_id', 'hidden', array(
				'name'      => 'stores[]',
				'disabled'  => true,
				'readonly'  => true,
				'value'     => Mage::app()->getStore(true)->getId()
			));
		}
       
      $fieldset->addField('contest_participant_status', 'select', array(
          'label'     => Mage::helper('contest')->__('Contest Result'),
          'name'      => 'contest_participant_status',
          'values'    => Mage::getModel("contest/statusparticipant")->getOptionArray(),
      ));
      
       $fieldset->addField('contest_participant_send_mail', 'hidden', array(
          'label'     => Mage::helper('contest')->__('Contest Email'),
          'required'  => false,
          'name'      => 'contest_participant_send_mail',
	  ));
     
      
      $form->setUseContainer(true);
      $this->setForm($form);
      
      if ( Mage::getSingleton('adminhtml/session')->getContestData() )
      {   $contestData = Mage::getSingleton('adminhtml/session')->getContestData();
          $form->setValues($contestData);
          $form->getElement('contest_participant_informed')->setIsChecked($contestData["contest_participant_informed"]);
          Mage::getSingleton('adminhtml/session')->setContestData(null);
      } elseif ( Mage::registry('contest_data') ) {
          $contestData = Mage::registry('contest_data')->getData();
          $form->setValues($contestData);
          $form->getElement('contest_participant_informed')->setIsChecked($contestData["contest_participant_informed"]);
      }
      
      return parent::_prepareForm();
  }
}
