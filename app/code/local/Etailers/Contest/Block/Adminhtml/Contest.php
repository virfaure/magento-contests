<?php
class Etailers_Contest_Block_Adminhtml_Contest extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_contest';
    $this->_blockGroup = 'contest';
    $this->_headerText = Mage::helper('contest')->__('Contest Manager');
    $this->_addButtonLabel = Mage::helper('contest')->__('Add Contest');
    parent::__construct();
  }
}
