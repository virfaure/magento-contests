<?php

class Etailers_Contest_Block_Adminhtml_Newsletter_Subscriber_Renderer_Contest extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if($row->getData("last_contest_id")) $value =  "<b>ID ".$row->getData("last_contest_id"). "</b> - ". $row->getData("contest_title");
        return '<span>'.$value.'</span>';
    }
}

?>