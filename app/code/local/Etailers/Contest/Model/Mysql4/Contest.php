<?php

class Etailers_Contest_Model_Mysql4_Contest extends Mage_Core_Model_Mysql4_Abstract
{
     /**
     * Store model
     *
     * @var null|Mage_Core_Model_Store
     */
    protected $_store  = null;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('contest/contest', 'contest_id');
    }

    /**
     * Process contest data before deleting
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Etailers_Contest_Model_Resource_Contest
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        
        // Contest STORE
        $condition = array(
            'contest_id = ?'     => (int) $object->getId(),
        );
        $this->_getWriteAdapter()->delete($this->getTable('contest/contest_store'), $condition);

		// URL REWRITE
		$condition = array(
            'id_path = ?'     => "contest/view/".(int) $object->getId(),
        );
		$this->_getWriteAdapter()->delete($this->getTable('core/url_rewrite'), $condition);
 
        return parent::_beforeDelete($object);
    }

    /**
     * Process contest data before saving
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Etailers_Contest_Model_Resource_Contest
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        /*
         * For two attributes which represent timestamp data in DB
         * we should make converting such as:
         * If they are empty we need to convert them into DB
         * type NULL so in DB they will be empty and not some default value
         */

        if (!$this->getIsUniqueContestToStores($object)) {
            Mage::throwException(Mage::helper('contest')->__('A contest URL key for specified store already exists.'));
        }

        if (!$this->isValidContestIdentifier($object)) {
            Mage::throwException(Mage::helper('contest')->__('The contest URL key contains capital letters or disallowed symbols.'));
        }

        if ($this->isNumericContestIdentifier($object)) {
            Mage::throwException(Mage::helper('contest')->__('The contest URL key cannot consist only of numbers.'));
        }

        // modify create / update dates
        if ($object->isObjectNew() && !$object->hasCreationTime()) {
            $object->setCreationTime(Mage::getSingleton('core/date')->gmtDate());
        }

        $object->setUpdateTime(Mage::getSingleton('core/date')->gmtDate());

        return parent::_beforeSave($object);
    }

    /**
     * Assign contest to store views
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Etailers_Contest_Model_Resource_Contest
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table  = $this->getTable('contest/contest_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = array(
                'contest_id = ?'     => (int) $object->getId(),
                'store_id IN (?)' => $delete
            );

            $this->_getWriteAdapter()->delete($table, $where);
        }

        if ($insert) {
            $data = array();

            foreach ($insert as $storeId) {
                $data[] = array(
                    'contest_id'  => (int) $object->getId(),
                    'store_id' => (int) $storeId
                );
            }

            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }

        return parent::_afterSave($object);
    }

    /**
     * Perform operations after object load
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Etailers_Contest_Model_Resource_Contest
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());

            $object->setData('store_id', $stores);

        }

        return parent::_afterLoad($object);
    }

    /**
     * Retrieve load select with filter by identifier, store and activity
     *
     * @param string $identifier
     * @param int|array $store
     * @param int $isActive
     * @return Varien_Db_Select
     */
    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('cp' => $this->getMainTable()))
            ->join(
                array('cps' => $this->getTable('contest/contest_store')),
                'cp.contest_id = cps.contest_id',
                array())
            ->where('cp.contest_url = ?', $identifier)
            ->where('cps.store_id IN (?)', $store);

        if (!is_null($isActive)) {
            $select->where('cp.is_active = ?', $isActive);
        }

        return $select;
    }

    
    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param Etailers_Contest_Model_Contest $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID, (int)$object->getStoreId());
            $select->join(
                array('contest_store' => $this->getTable('contest/contest_store')),
                $this->getMainTable() . '.contest_id = contest_store.contest_id',
                array())
                ->where('contest_store.store_id IN (?)', $storeIds)
                ->order('contest_store.store_id DESC')
                ->limit(1);
        }

        return $select;
    }
    
    /**
     * Check for unique of identifier of contest to selected store(s).
     *
     * @param Mage_Core_Model_Abstract $object
     * @return bool
     */
    public function getIsUniqueContestToStores(Mage_Core_Model_Abstract $object)
    {
        if (Mage::app()->isSingleStoreMode() || !$object->hasStores()) {
            $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID);
        } else {
            $stores = (array)$object->getData('stores');
        }

        $select = $this->_getLoadByIdentifierSelect($object->getData('contest_url'), $stores);

        if ($object->getId()) {
            $select->where('cps.contest_id <> ?', $object->getId());
        }

        if ($this->_getWriteAdapter()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     *  Check whether contest identifier is numeric
     *
     * @date Wed Mar 26 18:12:28 EET 2008
     *
     * @param Mage_Core_Model_Abstract $object
     * @return bool
     */
    protected function isNumericContestIdentifier(Mage_Core_Model_Abstract $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('contest_url'));
    }

    /**
     *  Check whether contest identifier is valid
     *
     *  @param    Mage_Core_Model_Abstract $object
     *  @return   bool
     */
    protected function isValidContestIdentifier(Mage_Core_Model_Abstract $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('contest_url'));
    }


    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($contestId)
    {
        $adapter = $this->_getReadAdapter();

        $select  = $adapter->select()
            ->from($this->getTable('contest/contest_store'), 'store_id')
            ->where('contest_id = ?',(int)$contestId);

        return $adapter->fetchCol($select);
    }

    /**
     * Set store model
     *
     * @param Mage_Core_Model_Store $store
     * @return Etailers_Contest_Model_Resource_Contest
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return Mage::app()->getStore($this->_store);
    }
}
