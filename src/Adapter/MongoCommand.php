<?php

namespace DBStorage\Codec\Adapter;

use yii\mongodb\Command;

class MongoCommand extends Command
{
    public $storageName;

    /** @var MongoCodec */
    protected $_codec;

    /** @inheritDoc */
    public function init()
    {
        parent::init();
        $this->_codec = MongoCodec::instance($this->storageName);
    }
    
    /** @inheritDoc */
    public function query($collectionName, $options = [])
    {
        $this->encodeDocument($collectionName);
        return parent::query($collectionName, $options);
    }

    /** @inheritDoc */
    public function executeBatch($collectionName, $options = [])
    {
        foreach ($this->document as &$operation) {
            if (!empty($operation['document'])) {
                $operation['document'] = $this->_codec->encode($collectionName, $operation['document']);
            }
            if (!empty($operation['condition'])) {
                $operation['condition'] = $this->_codec->encode($collectionName, $operation['condition']);
            }
        }

        return parent::executeBatch($collectionName, $options);
    }

    /** @inheritDoc */
    public function count($collectionName, $condition = [], $options = [])
    {
        $this->encodeDocument($collectionName);

        return parent::count($collectionName, $condition, $options);
    }

    /** @inheritDoc */
    public function distinct($collectionName, $fieldName, $condition = [], $options = [])
    {
        $filter = [$fieldName => $condition];
        $filter = $this->_codec->encode($collectionName, $filter);
        $condition = $filter[$fieldName];
        
        $values = parent::distinct($collectionName,$fieldName, $condition, $options);
        return $this->_codec->decodeFieldValue($collectionName, $fieldName, $values);
    }

    private function encodeDocument($collectionName)
    {
        $this->document = $this->_codec->encode($collectionName, $this->document);
    }
}
