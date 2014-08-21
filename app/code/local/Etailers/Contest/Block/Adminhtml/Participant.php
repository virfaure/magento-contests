<?php
class Etailers_Contest_Block_Adminhtml_Participant extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_participant';
    $this->_blockGroup = 'contest';
    $this->_headerText = Mage::helper('contest')->__('Contest Participant Manager');
   // $this->_addButtonLabel = Mage::helper('contest')->__('Add Item');
   
    parent::__construct();
    
    $this->removeButton('add');
     
  }
}
