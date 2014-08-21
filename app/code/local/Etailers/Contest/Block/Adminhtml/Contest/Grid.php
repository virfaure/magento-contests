<?php

class Etailers_Contest_Block_Adminhtml_Contest_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('contestGrid');
      $this->setDefaultSort('contest_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('contest/contest')->getCollection();
      $collection->setFirstStoreFlag(true);
      $this->setCollection($collection);

      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('contest_id', array(
          'header'    => Mage::helper('contest')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'contest_id',
      ));

      $this->addColumn('contest_title', array(
          'header'    => Mage::helper('contest')->__('Title'),
          'align'     =>'left',
          'index'     => 'contest_title',
      ));

	  $this->addColumn('contest_date_start', array(
          'header'    => Mage::helper('contest')->__('Start Date'),
          'align'     =>'left',
          'type'      => 'date',
          'index'     => 'contest_date_start',
      ));
      
      
      $this->addColumn('contest_date_end', array(
          'header'    => Mage::helper('contest')->__('End Date'),
          'align'     =>'left',
          'type'      => 'date',
          'index'     => 'contest_date_end',
      ));
      
      $this->addColumn('contest_url', array(
          'header'    => Mage::helper('contest')->__('Contest URL'),
          'align'     =>'left',
          'index'     => 'contest_url',
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
                'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),
            ));
        }
      
	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('contest')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      $this->addColumn('contest_status', array(
          'header'    => Mage::helper('contest')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'contest_status',
          'type'      => 'options',
          'options'   => Mage::getModel("contest/status")->getOptionArray(),
      ));
	  
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
        $this->setMassactionIdField('contest_id');
        $this->getMassactionBlock()->setFormFieldName('contest');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('contest')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('contest')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('contest/status')->getOptionArray();

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
