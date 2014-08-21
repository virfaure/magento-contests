<?php

class Etailers_Contest_Model_Statusparticipant extends Varien_Object
{
    
    const STATUS_WINNER		= "winner";
    const STATUS_LOOSER		= "looser";
	const STATUS_NOTHING	= "--";

    static public function getOptionArray()
    {
        return array(
            self::STATUS_WINNER    	=> Mage::helper('contest')->__('Winner'),
            self::STATUS_LOOSER   	=> Mage::helper('contest')->__('Looser'),
            self::STATUS_NOTHING    => Mage::helper('contest')->__('--'),
        );
    }
}
