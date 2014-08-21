<?php

class Etailers_Contest_Block_Adminhtml_Participant_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('participantGrid');
      $this->setDefaultSort('contest_participant_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

	public function setCollection($collection)
    {
        $collection->getSelect();
               /* ->joinleft(array('ncs'=>'newsletter_contest_subcriber'),'ncs.last_contest_id=main_table.contest_id',array('last_contest_id'))
                ->joinleft(array('ns'=>'newsletter_subscriber'),'ns.subscriber_id=ncs.subscriber_id',array('subscriber_status'))
                ->group('main_table.contest_participant_id');*/
        
       
        parent::setCollection($collection);
    }
  
  protected function _prepareCollection()
  {
      $collection = Mage::getModel('contest/participant')->getCollection();

      $collection->setFirstStoreFlag(true);
      $this->setCollection($collection);

      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('contest_participant_id', array(
          'header'    => Mage::helper('contest')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'contest_participant_id',
      ));

      $this->addColumn('contest_participant_name', array(
          'header'    => Mage::helper('contest')->__('Name'),
          'align'     =>'left',
          'index'     => 'contest_participant_name',
      ));

	  $this->addColumn('contest_participant_email', array(
          'header'    => Mage::helper('contest')->__('Email'),
          'align'     =>'left',
          'index'     => 'contest_participant_email',
      ));
      
      $contestOptions = Mage::getResourceModel("contest/contest_collection")->toOptionArray();
      $options = array();
      foreach($contestOptions as $key => $data){
          $options[(int)$data['value']] = $data['value'];
      }
      $this->addColumn('contest_id', array(
          'header'    => Mage::helper('contest')->__('Contest ID'),
          'align'     =>'left',
          'index'     => 'contest_id',
          'type'      => 'options',
          'options'   => $options,
      ));
      
      $this->addColumn('contest_title', array(
          'header'    => Mage::helper('contest')->__('Contest Title'),
          'align'     =>'left',
          'index'     => 'contest_title',
      ));
      
      /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('contest')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback' => array($this, '_filterStoreCondition'),
            ));
        }
      
      
      /**
         * Check is single store mode

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('contest')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),
            ));
        }
*/

      $this->addColumn('contest_participant_status', array(
          'header'    => Mage::helper('contest')->__('Contest Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'contest_participant_status',
          'type'      => 'options',
          'options'   => Mage::getModel("contest/statusparticipant")->getOptionArray()
      ));
      
      $this->addColumn('contest_participant_informed', array(
          'header'    => Mage::helper('contest')->__('Participant Informed'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'contest_participant_informed',
          'renderer'  => 'Etailers_Contest_Block_Adminhtml_Participant_Renderer_Informed',
      ));
	  
	  /*
	   $this->addColumn('subscriber_status', array(
          'header'    => Mage::helper('contest')->__('Newsletter Subscription Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'subscriber_status',
          'type'      => 'options',
          'options'   => array(
                Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE   => Mage::helper('newsletter')->__('Not Activated'),
                Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED   => Mage::helper('newsletter')->__('Subscribed'),
                Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED => Mage::helper('newsletter')->__('Unsubscribed'),
                Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED => Mage::helper('newsletter')->__('Unconfirmed'),
            )
      ));
	  */
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('contest')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('contest')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

		
		$this->addExportType('*/*/exportCsv', Mage::helper('contest')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('contest')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('contest_participant_id');
        $this->getMassactionBlock()->setFormFieldName('contest');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('contest')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('contest')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('contest/statusparticipant')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('contest')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('contest')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        
        $this->getMassactionBlock()->addItem('sendmail', array(
             'label'=> Mage::helper('contest')->__('Send Email'),
             'url'  => $this->getUrl('*/*/massSendmail', array('_current'=>true)),
            'confirm'  => Mage::helper('contest')->__('Are you sure?')
        ));
        
        return $this;
    }

	protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }
    
  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
