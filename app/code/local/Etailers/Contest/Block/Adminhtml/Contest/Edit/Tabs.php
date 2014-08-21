<?php

class Etailers_Contest_Block_Adminhtml_Contest_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('contest_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('contest')->__('Contest Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('contest')->__('Contest Information'),
          'title'     => Mage::helper('contest')->__('Contest Information'),
          'content'   => $this->getLayout()->createBlock('contest/adminhtml_contest_edit_tab_form')->toHtml(),
      ));
      
      $this->addTab('content_section', array(
          'label'     => Mage::helper('contest')->__('Contest Content'),
          'title'     => Mage::helper('contest')->__('Contest Content'),
          'content'   => $this->getLayout()->createBlock('contest/adminhtml_contest_edit_tab_content')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}
