<?php

namespace DBStorage\Codec\Adapter;

use DBStorage\Codec\CodecInterface;

class MongoCodec
{
    protected $storageName;

    private static $_instances = [];

    /**
     * get instance
     *
     * @param string $storageName
     * @return static
     */
    public static function instance($storageName)
    {
        if (!isset(self::$_instances[$storageName])) {
            self::$_instances[$storageName] = new static($storageName);
        }

        return self::$_instances[$storageName];
    }

    public function __construct($storageName)
    {
        $this->storageName = $storageName;
    }

    public function encode($collectionName, $value)
    {
        if ($value) {
            if ($codec = $this->getCodec($collectionName)) {
                return $codec->encode($value);
            }
        }

        return $value;
    }

    public function decode($collectionName, $value)
    {
        if ($value) {
            if ($codec = $this->getCodec($collectionName)) {
                return $codec->decode($value);
            }
        }

        return $value;
    }

    public function encodeFieldValue($collectionName, $fieldName, $value)
    {
        if ($value) {
            if ($codec = $this->getFieldCodec($collectionName, $fieldName)) {
                return $this->encodeValue($codec, $value);
            }
        }

        return $value;
    }

    public function decodeFieldValue($collectionName, $fieldName, $value)
    {
        if (!$value) {
            return $value;
        }

        if ($codec = $this->getFieldCodec($collectionName, $fieldName)) {
            return $this->decodeValue($codec, $value);
        }

        return $value;
    }

    private function getCodec($collectionName)
    {
        $ins = PGMongoStorageComponent::instance($this->storageName);
        if ($ins) {
            return $ins->getCollectionConfig($collectionName);
        }

        return null;
    }

    private function getFieldCodec($collectionName, $fieldName)
    {
        if ($coll = $this->getCodec($collectionName)) {
            return $coll->getFieldCodec($fieldName);
        }

        return null;
    }

    private function encodeValue(CodecInterface $codec, $value)
    {
        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = $codec->encode($val);
            }
        } elseif (is_scalar($value)) {
            $value = $codec->encode($value);
        }

        return $value;
    }

    private function decodeValue(CodecInterface $codec, $value)
    {
        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = $codec->decode($val);
            }
        } elseif (is_scalar($value)) {
            $value = $codec->decode($value);
        }

        return $value;
    }
}
