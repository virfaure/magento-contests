<?php

class Etailers_Contest_Block_Adminhtml_Newsletter_Subscriber_Grid extends Mage_Adminhtml_Block_Newsletter_Subscriber_Grid
{
    /**
     * Constructor
     *
     * Set main configuration of grid
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function setCollection($collection)
    {
        $collection->getSelect()
                ->joinleft(array('ncs'=>'newsletter_contest_subcriber'),'ncs.subscriber_id=main_table.subscriber_id',array('last_contest_id'))
                ->joinleft(array('contest'=>'contest'),'contest.contest_id=ncs.last_contest_id',array('contest_title'));
       
        // As Magento allowed only one rewrite for a block, this is a hack to load    
        // popup_id if module enabled    
        // You find the same one in Etailers_Contest
        if(Mage::helper('core')->isModuleEnabled('Etailers_Popup')){
			$collection->getSelect()
                ->joinleft(array('nps'=>'newsletter_popup_subcriber'),'nps.subscriber_id=main_table.subscriber_id',array('last_popup_id'))
                ->joinleft(array('popup'=>'popup'),'popup.popup_id=nps.last_popup_id',array('popup_title'));
		}
		
        parent::setCollection($collection);
    }

    protected function _prepareColumns()
    {
        $this->addColumnAfter('contest_id', array(
            'header'    => Mage::helper('newsletter')->__('Contest ID'),
            'index'     => 'last_contest_id',
            'default'   => '----',
            'renderer'  => 'Etailers_Contest_Block_Adminhtml_Newsletter_Subscriber_Renderer_Contest',
        ), 'store');
        
        if(Mage::helper('core')->isModuleEnabled('Etailers_Popup')){		
			$this->addColumnAfter('popup_id', array(
				'header'    => Mage::helper('newsletter')->__('Popup ID'),
				'index'     => 'last_popup_id',
				'default'   => '----',
				'renderer'  => 'Etailers_Popup_Block_Adminhtml_Newsletter_Subscriber_Renderer_Popup',
			), 'store');
		}

        return parent::_prepareColumns();
    }

}
