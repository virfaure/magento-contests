<?php

class Etailers_Contest_Adminhtml_ParticipantController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('contest/participant')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('contest/participant')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('contest_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('contest/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
				$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            }
            
			$this->_addContent($this->getLayout()->createBlock('contest/adminhtml_participant_edit'));
			//->_addLeft($this->getLayout()->createBlock('contest/adminhtml_contest_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('contest')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		$model = Mage::getModel('contest/participant');		
		
		if ($data = $this->getRequest()->getPost()) {
        
                        $model->setData($data)->setId($this->getRequest()->getParam('id'));
                        
			try {

				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
                            
                                if(!empty($data["contest_participant_send_mail"]) &&  $data["contest_participant_send_mail"] == 1){
					// SEND EMAIL TO PARTICPANT
					$this->_sendEmailToParticipant($this->getRequest()->getParam('id'));                              
				}
              
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('contest')->__('Participant was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('contest')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('contest/participant');
				$model->setId($this->getRequest()->getParam('id'))->delete();
				
				//$modelRewrite = Mage::getModel('core/url_rewrite');
				//$model->setId($this->getRequest()->getParam('id'))->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $contestIds = $this->getRequest()->getParam('contest');
        if(!is_array($contestIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($contestIds as $contestId) {
                    $contest = Mage::getModel('contest/participant')->load($contestId);
                    $contest->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($contestIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $contestParticipantIds = $this->getRequest()->getParam('contest');
        if(!is_array($contestParticipantIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($contestParticipantIds as $contestParticipantId) {
                    $contest = Mage::getSingleton('contest/participant')
                        ->load($contestParticipantId)
                        ->setContestParticipantStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($contestParticipantIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function massSendmailAction()
    {
        $contestParticipantIds = $this->getRequest()->getParam('contest');
        if(!is_array($contestParticipantIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                $success = 0;
                
                foreach ($contestParticipantIds as $contestParticipantId) {
                    $contestParticipant = Mage::getSingleton('contest/participant')->load($contestParticipantId);
                    
                    if($contestParticipant){

                        if($this->_sendEmailToParticipant($contestParticipantId)){
                            $success++;
                        }
                    }
                }
                if($success > 0){
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d email(s) were successfully send', count($success))
                    );
                }
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
  
    public function exportCsvAction()
    {
        $fileName   = 'contest.csv';
        $content    = $this->getLayout()->createBlock('contest/adminhtml_participant_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'contest.xml';
        $content    = $this->getLayout()->createBlock('contest/adminhtml_participant_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
    
    protected function _sendEmailToParticipant($id){	
        if(!empty($id)){
             $participant = Mage::getModel("contest/participant")->load($id);
             if($participant){
                 // Check Informed Status => if 1, Participant already informed,
                 // No need to send him an another email
                 if($participant->getContestParticipantInformed() != 1){
                     //if Result Contest is not Nothing ! 
                     if($participant->getContestParticipantStatus() != Etailers_Contest_Model_Statusparticipant::STATUS_NOTHING){
                         try{
                            $contest = Mage::getModel("contest/contest")->load($participant->getContestId());
                            $storeId = $participant->getStoreId();
                            $store = Mage::getModel('core/store')->load($storeId); 
                            
                            $from_email = Mage::getStoreConfig("trans_email/ident_general/email");
                            $from_name = Mage::getStoreConfig("trans_email/ident_general/name");

                            $data = array(
                                'participant'      => $participant,
                                'contest'          => $contest,
                                'store'            => $store
                            );
                             
                            $emailId = Mage::getStoreConfig('etailers_contest/etailers_contest_group/custom_template', $storeId);

                            $mailTemplate = Mage::getModel('core/email_template');
                            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store' => $storeId));

                            $resultSent = $mailTemplate->sendTransactional(
                                $emailId,
                                array('name' => $from_name, 'email' => $from_email),
                                $participant->getContestParticipantEmail(), 
                                $participant->getContestParticipantName(), 
                                $data
                            );
                            
                            if(!$resultSent){
                                $this->_getSession()->addError($this->__('Could not send mail to participant'));
				$this->_redirect('*/*/edit', array('id' => $id));
                            }else{
                                $this->_getSession()->addSuccess($this->__('Email Sent to participant ID : '. $id));

                                //Update Status
                                Mage::getModel('contest/participant')->updateInformedParticipant($id);

                                $this->_redirect('*/*/edit', array('id' => $id));
                            }
                             
                         }catch (Exception $e) {
                            Mage::log($e->getMessage());
                            return null;
			 }
                     }else{
                        Mage::getSingleton('adminhtml/session')->addError("Participant Status required to send email in ID : " . $id );
			$this->_redirect('*/*/edit', array('id' => $id));
                     }
                 }else{
                     Mage::getSingleton('adminhtml/session')->addError($this->__("Participant %d is already informed of this contest's results", $id));
                     $this->_redirect('*/*/edit', array('id' => $id));
                 }
             }
        }
    }
       
}
