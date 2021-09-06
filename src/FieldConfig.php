<?php

namespace DBStorage\Codec;

use InvalidArgumentException;

class FieldConfig implements CodecWithParamsInterface
{
    const HASH     = 1;
    const SECURITY = 2;

    public $codecType = self::SECURITY; // HASH or SECURITY
    public $afterDecoded; // 成功 decode 后执行的函数
    public $codecWithField;
    public $codecFuncs = [];
    // 解密失败时是否返回原值
    // 当配置为 false 时，解密失败返回 false
    public $returnOriginalValueWhenDecryptedFailed = true;

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
            if (!is_array($this->codecFuncs) || count($this->codecFuncs) !== 2) {
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
            $this->_decoder = function ($key, $value) {
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
            if (empty($params[$this->codecWithField])) {
                throw new CodecException('"codecWithField" is empty');
            }
            $key .= $params[$this->codecWithField];
        }

        return call_user_func($this->_encoder, $key, $value);
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

        $data = call_user_func($this->_decoder, $key, $value);

        if ($data === false && $this->returnOriginalValueWhenDecryptedFailed) {
            return $value;
        }

        if ($this->afterDecoded) {
            return call_user_func($this->afterDecoded, $data);
        }

        return $data;
    }
}
