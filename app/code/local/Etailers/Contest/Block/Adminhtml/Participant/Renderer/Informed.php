<?php

class Etailers_Contest_Block_Adminhtml_Participant_Renderer_Informed extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if($row->getData("contest_participant_informed") == 1) $value =  Mage::helper('contest')->__('Yes');
        else $value =  Mage::helper('contest')->__('No');
        
        return $value;
    }
}

?>
