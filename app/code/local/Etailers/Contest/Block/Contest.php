<?php
class Etailers_Contest_Block_Contest extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getContest()     
     { 
        if (!$this->hasData('contest')) {
            $this->setData('contest', Mage::registry('contest'));
        }
        return $this->getData('contest');
        
    }
}