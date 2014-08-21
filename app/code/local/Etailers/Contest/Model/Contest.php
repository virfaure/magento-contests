<?php

class Etailers_Contest_Model_Contest extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'etailers_contest';
    
    public function _construct()
    {
        $this->_init('contest/contest');
    }
    
}
