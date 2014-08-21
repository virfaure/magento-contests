<?php

class Etailers_Contest_Model_Mysql4_Participant_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    
     /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;


    /**
     * Define resource model
     *
     */
    protected function _construct()
    {
        $this->_init('contest/participant');
        $this->_map['fields']['contest_participant_id'] = 'main_table.contest_participant_id';
        $this->_map['fields']['store']   = 'store_table.store_id';
    }

   
    /**
     * Set first store flag
     *
     * @param bool $flag
     * @return Etailers_Contest_Model_Resource_Contest_Collection
     */
    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @return Etailers_Contest_Model_Resource_Contest_Collection
     */

    protected function _afterLoad()
    {
        /*if ($this->_previewFlag) {
            $items = $this->getColumnValues('contest_id');
            $connection = $this->getConnection();
            if (count($items)) {
                $select = $connection->select()
                        ->from(array('cps'=>$this->getTable('contest/contest_store')))
                        ->where('cps.contest_id IN (?)', $items);

                if ($result = $connection->fetchPairs($select)) {
                    foreach ($this as $item) {
                        if (!isset($result[$item->getData('contest_id')])) {
                            continue;
                        }
                        if ($result[$item->getData('contest_id')] == 0) {
                            $stores = Mage::app()->getStores(false, true);
                            $storeId = current($stores)->getId();
                            $storeCode = key($stores);
                        } else {
                            $storeId = $result[$item->getData('contest_id')];
                            $storeCode = Mage::app()->getStore($storeId)->getCode();
                        }
                        $item->setData('_first_store_id', $storeId);
                        $item->setData('store_code', $storeCode);   
                    }
                }
            }
        }*/
        
        // Join Contest Title 
        $items = $this->getColumnValues('contest_id');
        $connection = $this->getConnection();
        if (count($items)) {
            $select_title = $connection->select()
                        ->from(array('cc'=>$this->getTable('contest/contest')), array('contest_id', 'contest_title'))
                        ->where('cc.contest_id IN (?)', $items);

            if ($result = $connection->fetchPairs($select_title)) {
               foreach ($this as $item) {
                   $item->setData('contest_title', $result[$item->getData('contest_id')]);    
               }
             }
        }
        
        // Join Newsletter Subscribe Status 
        $items = $this->getColumnValues('subscriber_id');
        $connection = $this->getConnection();
        if (count($items)) {
            $select_subscriber = $connection->select()
                        ->from(array('ns'=>$this->getTable('newsletter/subscriber')), array('subscriber_id', 'subscriber_status'))
                        ->where('ns.subscriber_id IN (?)', $items);

            if ($result = $connection->fetchPairs($select_subscriber)) {
               foreach ($this as $item) {
                   $item->setData('subscriber_status', $result[$item->getData('subscriber_id')]);    
               }
             }
        }
        
        return parent::_afterLoad();
    }

    /**
     * Add filter by store
     *
     * @param int|Mage_Core_Model_Store $store
     * @param bool $withAdmin
     * @return Etailers_Contest_Model_Resource_Contest_Collection
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            if ($store instanceof Mage_Core_Model_Store) {
                $store = array($store->getId());
            }

            if (!is_array($store)) {
                $store = array($store);
            }

            if ($withAdmin) {
                $store[] = Mage_Core_Model_App::ADMIN_STORE_ID;
            }

            $this->addFilter('store', array('in' => $store), 'public');
        }
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     */

    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                array('store_table' => $this->getTable('contest/contest_store')),
                'main_table.contest_id = store_table.contest_id',
                array()
            )->group('main_table.contest_id');

            // Allow analytic functions usage because of one field grouping
          
            $this->_useAnalyticFunction = true;
        }
        return parent::_renderFiltersBefore();
    }


    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();

        $countSelect->reset(Zend_Db_Select::GROUP);

        return $countSelect;
    }
}
