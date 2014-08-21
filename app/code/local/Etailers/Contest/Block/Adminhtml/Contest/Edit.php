<?php

class Etailers_Contest_Block_Adminhtml_Contest_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'contest';
        $this->_controller = 'adminhtml_contest';
        
        $this->_updateButton('save', 'label', Mage::helper('contest')->__('Save Contest'));
        $this->_updateButton('delete', 'label', Mage::helper('contest')->__('Delete Contest'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
			function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            }
            
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

	
    public function getHeaderText()
    {
        if( Mage::registry('contest_data') && Mage::registry('contest_data')->getId() ) {
            return Mage::helper('contest')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('contest_data')->getContestTitle()));
        } else {
            return Mage::helper('contest')->__('Add Item');
        }
    }
}
