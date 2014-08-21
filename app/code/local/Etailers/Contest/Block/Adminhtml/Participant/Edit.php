<?php

class Etailers_Contest_Block_Adminhtml_Participant_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'contest';
        $this->_controller = 'adminhtml_participant';
        
        $this->_updateButton('save', 'label', Mage::helper('contest')->__('Save Participant'));
        //$this->_updateButton('delete', 'label', Mage::helper('contest')->__('Delete Participant'));
	$this->removeButton('delete');
        
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

	// IF Edit 
        $objId = $this->getRequest()->getParam($this->_objectId);
        if (! empty($objId)) {
            $participant = Mage::getModel("contest/participant")->load($objId);
            if(!$participant->getContestParticipantInformed()){
			$this->_addButton('sendemailtoparticipant', array(
				'label'     => Mage::helper('adminhtml')->__('Send Mail to Particpant'),
				'class'     => 'send',
				'onclick'   => 'sendEmailToParticipant()',
			), -110);
                        }
        }
		
        $this->_formScripts[] = "
			function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            }
            
            function sendEmailToParticipant(){
               $('contest_participant_send_mail').value=1; 
               editForm.submit($('edit_form').action+'back/edit/');
            }
            
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

	
    public function getHeaderText()
    {
        if( Mage::registry('contest_data') && Mage::registry('contest_data')->getId() ) {
            return Mage::helper('contest')->__("Edit Participant '%s'", $this->htmlEscape(Mage::registry('contest_data')->getContestParticipantName()));
        } else {
            return Mage::helper('contest')->__('Add Participant');
        }
    }
}
