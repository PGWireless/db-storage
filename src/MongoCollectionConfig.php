<?php

namespace DBStorage\Codec;

use InvalidArgumentException;

class MongoCollectionConfig extends CollectionConfig
{
    public function encode($value)
    {
        $this->_encode($value);
        return $value;
    }

    public function decode($value)
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('array required when decode');
        }

        $this->_decode($value);

        return $value;
    }

    protected static function isOperator($name)
    {
        return is_string($name) && substr($name, 0, 1) === '$';
    }

    private function _encode(&$where, CodecInterface $codec = null)
    {
        if (is_array($where)) {
            foreach ($where as $key => &$val) {
                if (is_int($key)) {
                    $this->_encode($val, $codec);
                } else if (static::isOperator($key)) {
                    if (is_scalar($val) && $codec) {
                       $val = $codec->encode($val);
                    } elseif (is_array($val)) {
                       $this->_encode($val, $codec);
                    }
                } else { // $key 非操作符，视作字段名
                    $fCodec = $this->getFieldCodec($key);
                    if ($fCodec) { // 当前字段需要处理
                        if (is_scalar($val)) {
                            $val = $fCodec->encode($val);
                        } else {
                            $this->_encode($val, $fCodec);
                        }
                    }
                }
            }
        } elseif (is_scalar($where) && $codec) {
            $where = $codec->encode($where);
        }
    }

    private function _decode(array &$data, $keyPrefix = '', CodecInterface $codec = null)
    {
        if (static::isAssociative($data)) { // 关联数组
            foreach ($data as $key => &$val) {
                $confKey = $keyPrefix . $key;
                $fCodec = $this->getFieldCodec($confKey);

                if (is_scalar($val) && $fCodec) {
                    $val = $fCodec->decode($val);
                } elseif (is_array($val)) {
                    $this->_decode($val, $keyPrefix . $key . '.', $fCodec);
                }
            }
        } else { // 索引数组
            foreach ($data as &$val) {
                if (is_scalar($val) && $codec) {
                    $val = $codec->decode($val);
                } elseif (is_array($val)) {
                    $this->_decode($val, $keyPrefix, $codec);
                }
            }
        }
    }

    /**
     * Returns a value indicating whether the given array is an associative array.
     *
     * An array is associative if all its keys are strings. If `$allStrings` is false,
     * then an array will be treated as associative if at least one of its keys is a string.
     *
     * Note that an empty array will NOT be considered associative.
     *
     * @param array $array the array being checked
     * @param bool $allStrings whether the array keys must be all strings in order for
     * the array to be treated as associative.
     * @return bool whether the array is associative
     */
    public static function isAssociative($array, $allStrings = true)
    {
        if (!is_array($array) || empty($array)) {
            return false;
        }

        if ($allStrings) {
            foreach ($array as $key => $value) {
                if (!is_string($key)) {
                    return false;
                }
            }

            return true;
        }

        foreach ($array as $key => $value) {
            if (is_string($key)) {
                return true;
            }
        }

        return false;
    }
}
