<?php

namespace DBStorage\Codec;

use InvalidArgumentException;

class FieldConfig implements CodecWithParamsInterface
{
    const HASH     = 1;
    const SECURITY = 2;

    public $codecType = self::SECURITY; // HASH or SECURITY
    public $codecWithField;
    public $codecFuncs = [];
    // 解密失败时返回原值，否则返回 false
    public $returnOriginalValueWhenDecryptedFailed = false;

    private $_secretKey = '';

    /** @var callable  */
    private $_encoder;
    /** @var callable  */
    private $_decoder;

    public function __construct($secretKey)
    {
        $this->_secretKey = $secretKey;
    }

    public function init()
    {
        if (!empty($this->codecFuncs)) {
            if (count($this->codecFuncs) !== 2) {
                throw new InvalidArgumentException('Invalid property "codecFuncs" configured');
            }
            if (!is_callable($this->codecFuncs[0]) || !is_callable($this->codecFuncs[1])) {
                throw new InvalidArgumentException('Invalid property "codecFuncs", must be callable');
            }

            $this->_encoder = $this->codecFuncs[0];
            $this->_decoder = $this->codecFuncs[0];
            return;
        }

        if ($this->codecType === static::HASH) {
            $this->_encoder = function ($key, $value) {
                return StaticHashCodec::encode($key, $value);
            };
            $this->_decoder = function ($key, $value) {
                return $value;
            };
        } elseif ($this->codecType === static::SECURITY) {
            $this->_encoder = function ($key, $value) {
                return StaticSecurityCodec::encode($key, $value);
            };
            $this->_decoder = function($key, $value) {
                return StaticSecurityCodec::decode($key, $value);
            };
        } else {
            throw new InvalidArgumentException('Invalid property "codecType" configured');
        }
    }

    public function encode($value, array $params = [])
    {
        if (!$value) {
            return '';
        }
        
        $key = $this->_secretKey;
        if ($this->codecWithField) {
            if ( empty($params[$this->codecWithField])) {
                throw new CodecException('"codecWithField" is empty');
            }
            $key .= $params[$this->codecWithField];
        }

        return ($this->_encoder)($key, $value);
    }

    public function decode($value, array $params = [])
    {
        if (!$value) {
            return '';
        }

        $key = $this->_secretKey;
        if ($this->codecWithField) {
            if (empty($params[$this->codecWithField])) {
                throw new CodecException('"codecWithField" is empty');
            }
            $key .= $params[$this->codecWithField];
        }

        $data = ($this->_decoder)($key, $value);

        if ($data === false && $this->returnOriginalValueWhenDecryptedFailed) {
            return $value;
        }

        return $data;
    }
}
