<?php

class Etailers_Contest_Adminhtml_ContestController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('contest/contest')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Contest Manager'), Mage::helper('adminhtml')->__('Contest Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('contest/contest')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('contest_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('contest/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Contest Manager'), Mage::helper('adminhtml')->__('Contest Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Contest News'), Mage::helper('adminhtml')->__('Contest News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
				$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            }
            
			$this->_addContent($this->getLayout()->createBlock('contest/adminhtml_contest_edit'))
				->_addLeft($this->getLayout()->createBlock('contest/adminhtml_contest_edit_tabs'));

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
		$model = Mage::getModel('contest/contest');		
		
		if ($data = $this->getRequest()->getPost()) {
			
			if(isset($_FILES['contest_image']['name']) && $_FILES['contest_image']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('contest_image');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					$path = Mage::getBaseDir('media') . DS . "/contest/" . DS;
					$uploader->save($path, $_FILES['contest_image']['name'] );
					
				} catch (Exception $e) {
					 $this->_getSession()->addError($e->getMessage());
		        }
	        
		        //this way the name is saved in DB
	  			$data['contest_image'] = $_FILES['contest_image']['name'];
			}else
			{
				if(isset($data['contest_image']['delete']) && $data['contest_image']['delete'] == 1) {
					 $data['contest_image'] = '';
				} else {
					unset($data['contest_image']);
				}
			}

                        // Thank you CMS Page
                        $data['contest_url_cms'] = str_replace(".html", "", $data['contest_url_cms']);
                        $data['contest_url_cms_html'].= $data['contest_url_cms'].".html";
                        
                        if(isset($data['contest_create_cmspage'])){
                            //Create CMS Page With URL
                            foreach($data['stores'] as $key => $store){
                                $pageModel = Mage::getModel('cms/page')->checkIdentifier($data['contest_url_cms_html'], $store);
                                if(!$pageModel){      
                                    $cmsPage = array(
                                        'title' => 'Thank you page for contest '.$data['contest_title'],
                                        'identifier' => $data['contest_url_cms_html'],
                                        'content' => 'Thank you page for contest '.$data['contest_title'],				
                                        'is_active' => 1,
                                        'sort_order' => 0,
                                        'stores' => $store,
                                        'root_template' => 'one_column'
                                    );
                                    Mage::getModel('cms/page')->setData($cmsPage)->save();
                                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('contest')->__('CMS Page was successfully created'));
                                }
                            }
                        }
                        
             // Remove .html of URL Contest Page
			$data['contest_url'] = str_replace(".html", "", $data['contest_url']);
			$data['contest_url_html'].= $data['contest_url'].".html";
                        
			// Format Date 
	  		$data = $this->_filterDates($data, array('contest_date_start', 'contest_date_end'));

            $model->setData($data)->setId($this->getRequest()->getParam('id'));
                        
			try {

				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
                                
				//////////////////////////////////////////////////////
				// URL REWRITE
				//$rewrite = Mage::getModel('core/url_rewrite')->loadByIdPath("contest/view/".$model->getId());
				$urlRewriteCollection = Mage::getModel('core/url_rewrite')->getCollection()
                                    ->addFilter('id_path', "contest/view/".$model->getId())
                                    ->load();
                                
                                // Delete if Exists
                                foreach ($urlRewriteCollection as $urlRewrite)
                                {
                                  $urlRewrite->delete();
                                }
                                
                                //Create URL for each store    
                                foreach($data['stores'] as $key => $store){
                                        Mage::getModel('core/url_rewrite')
                                        -> setStoreId($store) 
                                        -> setIdPath("contest/view/".$model->getId())
                                        -> setRequestPath($data['contest_url_html'])
                                        -> setTargetPath('contest/index/view/id/'.$model->getId())
                                        -> save();
                                }
			
                        
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('contest')->__('Item was successfully saved'));
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
				$model = Mage::getModel('contest/contest');
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
                    $contest = Mage::getModel('contest/contest')->load($contestId);
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
        $contestIds = $this->getRequest()->getParam('contest');
        if(!is_array($contestIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($contestIds as $contestId) {
                    $contest = Mage::getSingleton('contest/contest')
                        ->load($contestId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($contestIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'contest.csv';
        $content    = $this->getLayout()->createBlock('contest/adminhtml_contest_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'contest.xml';
        $content    = $this->getLayout()->createBlock('contest/adminhtml_contest_grid')
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
}
