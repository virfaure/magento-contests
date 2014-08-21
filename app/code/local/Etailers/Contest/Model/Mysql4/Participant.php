<?php

class Etailers_Contest_Model_Mysql4_Participant extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_read;
    protected $_write;

    protected function _construct()
    {
        $this->_init('contest/contest_participant', 'contest_participant_id');
        $this->_read = $this->_getReadAdapter();
        $this->_write = $this->_getWriteAdapter();
    }

    /*
     * Load participant from DB by email
     *
     * @param string $subscriberEmail
     * @return array
     */
    public function loadByEmail($participantEmail)
    {
        $select = $this->_read->select()
            ->from($this->getMainTable())
            ->where('contest_participant_email=:contest_participant_email');

        $result = $this->_read->fetchRow($select, array('contest_participant_email'=>$participantEmail));

        if (!$result) {
            return array();
        }

        return $result;
    }
    
    /*
     * Load participant from DB by email
     *
     * @param string $subscriberEmail
     * @return array
     */
    public function hasParticipated($email, $contestID)
    {
        $select = $this->_read->select()
            ->from($this->getMainTable())
            ->where('contest_participant_email=:contest_participant_email')
            ->where('contest_id=:contest_id');

        $result = $this->_read->fetchRow($select, array('contest_participant_email'=>$email, 'contest_id'=>$contestID));

        if (!$result) {
            return array();
        }

        return $result;
    }

    public function updateInformedParticipant($participantID)
    {
        $data['contest_participant_informed'] = 1;
        $this->_write->update($this->getMainTable(), $data, array('contest_participant_id = ?' => $participantID));       
    }
    
     public function saveLastContestId($subscriberID, $contestID)
    {
        $data['subscriber_id'] = $subscriberID;
        $data['last_contest_id'] = $contestID;
        
        try{
            $select = $this->_read->select()
                ->from("newsletter_contest_subcriber")
                ->where('subscriber_id=:subscriber_id');
            $result = $this->_read->fetchRow($select, array('subscriber_id'=>$subscriberID));

            if (!$result) {
                $this->_write->insert("newsletter_contest_subcriber", $data);       
            }else{
                $this->_write->update("newsletter_contest_subcriber", $data, array('subscriber_id = ?' => $subscriberID));       
            } 
            return true;
        }catch (Exception $e) {
            Mage::log($e->getMessage());
            return null;
        }
    }
}
