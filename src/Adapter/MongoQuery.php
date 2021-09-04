<?php

namespace DBStorage\Codec\Adapter;

use yii\mongodb\Query;

class MongoQuery extends Query
{
    public $storageName;

    /** @var MongoCodec */
    protected $_codec;

    private $_alreadyDecoded = false;
    
    /** @inheritDoc */
    public function init()
    {
        parent::init();
        $this->_codec = MongoCodec::instance($this->storageName);
    }

    /** @inheritDoc */
    public function prepare()
    {
        $this->_alreadyDecoded = false;
        return parent::prepare();
    }
    
    /** @inheritDoc */
    protected function fetchRowsInternal($cursor, $all)
    {
        $result = parent::fetchRowsInternal($cursor, $all);
        if ($this->_alreadyDecoded) {
            return $result;
        }

        $this->_alreadyDecoded = true;

        $collName = $this->from;
        if (is_array($collName)) {
            $collName = $collName[1];
        }
        
        return $this->_codec->decode($collName, $result);
    }

    /** @inheritDoc */
    public function populate($rows)
    {
        $result = parent::populate($rows);
        if ($this->_alreadyDecoded) {
            return $result;
        }

        $this->_alreadyDecoded = true;
        
        $collName = $this->from;
        if (is_array($collName)) {
            $collName = $collName[1];
        }

        return $this->_codec->decode($collName, $result);
    }
}
