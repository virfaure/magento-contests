<?php

class Etailers_Contest_Block_Adminhtml_Contest_Edit_Tab_Content extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	* Load Wysiwyg on demand and Prepare layout
	*/
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		}
	}
    
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('contest_form', array('legend'=>Mage::helper('contest')->__('Contest Content')));
     
      $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
            array('tab_id' => $this->getTabId())
      );
        

      $fieldset->addField('contest_title', 'text', array(
          'label'     => Mage::helper('contest')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'contest_title',
          'style'     => 'width:700px;',
      ));
      /*
      $fieldset->addField('contest_share_meta', 'textarea', array(
          'label'     => Mage::helper('contest')->__('Share Meta'),
          'class'     => 'required-entry',
          'style'     => 'width:700px; height:100px;',
          'required'  => true,
          'name'      => 'contest_share_meta',
      ));
      */
      $fieldset->addField('contest_description', 'editor', array(
          'label'     => Mage::helper('contest')->__('Description'),
          'class'     => 'required-entry',
          'style'     => 'width:700px; height:100px;',
          'config'    => $wysiwygConfig,
          'required'  => true,
          'name'      => 'contest_description',
      ));

      $fieldset->addField('contest_text_legal', 'editor', array(
          'label'     => Mage::helper('contest')->__('Legal Text'),
          'class'     => 'required-entry',
          'style'     => 'width:700px; height:200px;',
          'config'    => $wysiwygConfig,
          'required'  => true,
          'name'      => 'contest_text_legal',
      ));

     
      
      $fieldset->addField('contest_image', 'image', array(
          'label'     => Mage::helper('contest')->__('Image'),
          'required'  => false,
          'name'      => 'contest_image',
          'path'      => 'contest/',
          'after_element_html' => '<small style="position: absolute;">'.Mage::helper('contest')->__('Allowed extensions : jpg, jpeg, gif, png').'</small>',
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
