<?php
class Etailers_Contest_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {

    }

    public function viewAction()
    {
        $session = Mage::getSingleton('core/session');

        // GET Id
        $contest_id = $this->getRequest()->getParam("id");

        if($contest_id != null && $contest_id != '')    {
            $contestModel = Mage::getModel('contest/contest');
            $contestModel->setStoreId(Mage::app()->getStore()->getId());

            if (!$contestModel->load($contest_id)) {
                return false;
            }else{
                //Check if contest still valid with status and dates
                $arrContest = $contestModel->getData();
                $now = date("Y-m-d");
                if($arrContest['contest_status'] == Etailers_Contest_Model_Status::STATUS_ENABLED && ($arrContest['contest_date_start'] <= $now && $arrContest['contest_date_end'] >= $now)){
                    $arrContest['contest_active'] = true;
                }else{
                    $arrContest['contest_active'] = false;
                }
            }
        } else {
            $arrContest = null;
        }

        $this->loadLayout();

        $block = $this->getLayout()->getBlock('contest');
        $block->setData('contest',$arrContest);

        if($arrContest){
            $head = $this->getLayout()->getBlock('head');
            $head->setTitle($arrContest['contest_title']);
            $head->setDescription(strip_tags($arrContest['contest_description']));
        }

        $this->renderLayout();
    }


    public function participatecontestAction()
    {
        $dataPost = $this->getRequest()->getPost(); // contest_name, contest_email, contest_accept

        if ($dataPost) {

            try {
                $postObject = new Varien_Object();
                $postObject->setData($dataPost);

                $error = false;

                if (!Zend_Validate::is(trim($dataPost['contest_participant_name']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($dataPost['contest_participant_email']), 'EmailAddress')) {
                    $error = true;
                }

                if ($dataPost['contest_accept'] != 1) {
                    $error = true;
                }

                if ($error) {
                    throw new Exception();
                }

                //Check if User has already participated for this contest !
                $contestParticipantModel = Mage::getModel('contest/participant');
                if($contestParticipantModel->hasParticipated($dataPost['contest_participant_email'], $dataPost['contest_id'])){
                    Mage::getSingleton('core/session')->addError(Mage::helper('contest')->__('You have already participated in this contest.'));
                    $contest_url = Mage::getUrl('', array('_direct' => $dataPost['contest_url']));
                    $this->_redirectUrl($contest_url);
                    return;
                }else{

                    $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($dataPost['contest_participant_email']);

                    if(!$subscriber->getId()){
                        //Newsletter subscribe
                        $status = Mage::getModel('newsletter/subscriber')->subscribe($dataPost['contest_participant_email']);
                        if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                            Mage::getSingleton('core/session')->addSuccess(Mage::helper('contest')->__('Confirmation request has been sent.'));
                        }
                        else {
                            Mage::getSingleton('core/session')->addSuccess(Mage::helper('contest')->__('Thank you for your subscription.'));
                        }
                        $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($dataPost['contest_participant_email']);
                    }

                    // Save Last Contest Id in newsletter_contest_subcriber
                    Mage::getModel('contest/participant')->saveLastContestId($subscriber->getId(), $dataPost['contest_id']);

                    //Save him in contest_participant
                    $dataPost['store_id'] = Mage::app()->getStore()->getId();
                    $contestParticipantModel->setData($dataPost)->save();
                }

                Mage::getSingleton('core/session')->addSuccess(Mage::helper('contest')->__('Your participation in this contest has been submitted. Thank you !'));

                // Redirect to CMS Page of Contest
                $contest_url_cms = Mage::getUrl('', array('_direct' => $dataPost['contest_url_cms']));
                $this->_redirectUrl($contest_url_cms);

                return;
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError(Mage::helper('contest')->__('Unable to submit your request. Please, try again later'));

                // Redirect to URL of Contest
                if($dataPost['contest_url']){
                    $contest_url = Mage::getUrl('', array('_direct' => $dataPost['contest_url']));
                    $this->_redirectUrl($contest_url);
                }
                else $this->_redirect('*/*/view/');

                return;
            }

        } else {
            //Redirect to View without a contest ID
            $this->_redirect('*/*/view/');
        }
    }
}
