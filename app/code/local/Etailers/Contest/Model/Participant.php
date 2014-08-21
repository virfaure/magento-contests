<?php

class Etailers_Contest_Model_Participant extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'etailers_participant';
    
    
    public function _construct()
    {
        $this->_init('contest/participant');
    }
    
    /**
     * Return email subscription status
     *
     * @return bool
     */
    public function hasParticipated($email, $contestID)
    {
        $data = $this->getResource()->hasParticipated($email, $contestID);
        if (!empty($data)) {
            return true;
        }

        return false;
    }
    
    /**
     * Return customer subscription status
     *
     * @return bool
     */
    public function updateInformedParticipant($participantID)
    {
        return $this->getResource()->updateInformedParticipant($participantID);
    }
    
    
    /**
     * Return if user is winner of contest
     *
     * @return bool
     */
    public function isWinner(){
        if ($this->getContestParticipantStatus() == Etailers_Contest_Model_Statusparticipant::STATUS_WINNER) return true;
        else return false;
    }
    
    /**
     * Save Contest ID with Subscriber ID
     *
     */
    public function saveLastContestId($subscriberId, $contestID){
        return $this->getResource()->saveLastContestId($subscriberId, $contestID);
    }
    

}
