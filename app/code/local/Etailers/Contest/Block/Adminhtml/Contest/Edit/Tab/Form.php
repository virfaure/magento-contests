<?php

class Etailers_Contest_Block_Adminhtml_Contest_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('contest_form', array('legend'=>Mage::helper('contest')->__('Contest Information')));
     
      $model = Mage::getModel('contest/contest');
        
		/**
		* Check is single store mode
		*/
		if (!Mage::app()->isSingleStoreMode()) {
			$fieldset->addField('store_id', 'multiselect', array(
				'name'      => 'stores[]',
				'label'     => Mage::helper('cms')->__('Store View'),
				'title'     => Mage::helper('cms')->__('Store View'),
				'required'  => true,
				'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
				'disabled'  => $isElementDisabled
			));
		}
		else {
			$fieldset->addField('store_id', 'hidden', array(
				'name'      => 'stores[]',
				'value'     => Mage::app()->getStore(true)->getId()
			));
		}
        
      
		  $fieldset->addField('contest_url', 'text', array(
			  'label'     => Mage::helper('contest')->__('Contest Url'),
			  'class'     => 'required-entry float-left',
			  'required'  => true,
			  'name'      => 'contest_url',
			  'after_element_html' => '<small style="float: right;">.html</small>',
		  ));
		  
		  $fieldset->addField('contest_url_cms', 'text', array(
			  'label'     => Mage::helper('contest')->__('Thank You Page Url'),
			  'class'     => 'required-entry float-left',
			  'required'  => true,
			  'name'      => 'contest_url_cms',
			  'after_element_html' => '<small style="float: right;">.html</small>',
		  ));
		  
			$fieldset->addField('contest_create_cmspage', 'checkbox', array(
			  'label'     => Mage::helper('contest')->__('Create CMS Page'),
			  'class'     => 'float-left',
			  'name'      => 'contest_create_cmspage',
			  'value'  	=> '1',
			  'after_element_html' => '<small>'.Mage::helper('contest')->__('It will create the Thank You CMS Page with above URL').'</small>',
		  ));
      
		$dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
		$fieldset->addField('contest_date_start', 'date', array(
			'name'   => 'contest_date_start',
			'label'  => Mage::helper('contest')->__('Start Date'),
			'title'  => Mage::helper('contest')->__('Start Date'),
			'image'  => $this->getSkinUrl('images/grid-cal.gif'),
			'required'  => true,
			'format'       => $dateFormatIso
		));

	   $fieldset->addField('contest_date_end', 'date', array(
			'name'   => 'contest_date_end',
			'label'  => Mage::helper('contest')->__('End Date'),
			'title'  => Mage::helper('contest')->__('End Date'),
			'image'  => $this->getSkinUrl('images/grid-cal.gif'),
			'required'  => true,
			'format'       => $dateFormatIso
		));

      
      $fieldset->addField('contest_status', 'select', array(
          'label'     => Mage::helper('contest')->__('Status'),
          'name'      => 'contest_status',
          'values'    => Mage::getModel("contest/status")->getOptionArray(),
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getContestData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getContestData());
          Mage::getSingleton('adminhtml/session')->setContestData(null);
      } elseif ( Mage::registry('contest_data') ) {
          $form->setValues(Mage::registry('contest_data')->getData());
      }
      return parent::_prepareForm();
  }
}
