<?php

namespace DBStorage\Codec\Adapter;

use yii\mongodb\Collection;

class MongoCollection extends Collection
{
    public $storageName;

    /** @var MongoCodec */
    private $_codec;

    public function init()
    {
        parent::init();
        $this->_codec = MongoCodec::instance($this->storageName);
    }
    
    /** @inheritDoc */
    public function findOne($condition = [], $fields = [], $options = [])
    {
        $res = parent::findOne($condition, $fields, $options);
        return $this->_codec->decode($this->name, $res);
    }

    /** @inheritDoc */
    public function findAndModify($condition, $update, $options = [])
    {
        $res = parent::findAndModify($condition, $update, $options);
        return $this->_codec->decode($this->name, $res);
    }
}
