<?php

namespace DBStorage\Codec;

class CollectionConfig implements CodecInterface
{
    /**
     * collection fields
     * index by field name
     * @var FieldConfig[]
     */
    public $fields = [];

    private $_secretKey;

    /** @var CodecInterface 注入的 Codec，未注入时使用内部的 encode, decode 方法 */
    private $_codec;

    public function __construct($secretKey, array $fieldConfigs = [], CodecInterface $codec = null)
    {
        foreach ($fieldConfigs as $key => $val) {
            $this->setFieldConfig($key, $val);
        }

        $this->_secretKey = $secretKey;
        $this->_codec = $codec;
    }

    /**
     * 构建 FieldConfig 对象
     *
     * @param int $type FieldConfig::SECURITY or FieldConfig::HASH
     * @param string $codecWithField
     * @param array $codecFuncs
     * @param bool $returnOriginalValueWhenDecryptedFailed
     * @return FieldConfig
     */
    public function makeFieldConfig(
        $type = FieldConfig::SECURITY,
        $codecWithField = '',
        array $codecFuncs = [],
        $returnOriginalValueWhenDecryptedFailed = false
    ) {
        $field = new FieldConfig($this->_secretKey);
        $field->codecType = $type;
        $field->codecWithField = $codecWithField;
        $field->codecFuncs = $codecFuncs;
        $field->returnOriginalValueWhenDecryptedFailed = $returnOriginalValueWhenDecryptedFailed;

        $field->init();

        return $field;
    }

    public function makeSimpleFieldConfig()
    {
        return new FieldConfig($this->_secretKey);
    }

    public function setCodec(CodecInterface $codec)
    {
        $this->_codec = $codec;
    }

    public function setFieldConfig($fieldName, FieldConfig $config)
    {
        $this->fields[$fieldName] = $config;
    }

    /**
     * get field codec
     *
     * @param string $field
     * @return CodecInterface|null
     */
    public function getFieldCodec($field)
    {
        return isset($this->fields[$field]) ? $this->fields[$field] : null;
    }

    /**
     * get codec
     *
     * @return CodecInterface
     */
    public function getCodec()
    {
        if ($this->_codec) {
            return $this->_codec;
        }
        return new DynamicCodec([$this, 'encode'], [$this, 'decode']);
    }

    public function encode($value)
    {
        return $this->_codec ? $this->_codec->encode($value) : $this->internalEncode($value);
    }

    public function decode($value)
    {
        return $this->_codec ? $this->_codec->decode($value) : $this->internalDecode($value);
    }


    protected function internalEncode($value)
    {
        $res = [];
        foreach ($value as $key => $v) {
            if (isset($this->fields[$key])) {
                $res[$key] = $this->fields[$key]->encode($v, $value);
            } else {
                $res[$key] = $v;
            }
        }
        return $res;
    }

    protected function internalDecode($value)
    {
        $res = [];
        foreach ($value as $key => $v) {
            if (isset($this->fields[$key])) {
                $res[$key] = $this->fields[$key]->decode($v, $res);
            } else {
                $res[$key] = $v;
            }
        }
        return $res;
    }
}
