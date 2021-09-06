<?php

namespace DBStorage\Codec;

class ProjectConfig
{
    const COLLECTION_MONGO = 'mongo';

    /** @var CollectionConfig[] 索引数组，以 collection name 作键名 */
    public $collections = [];

    protected $secretName;

    private $_secretKey;

    public function __construct($secretName, SecretKeyGetterInterface $secretKeyGetter)
    {
        $this->secretName = $secretName;
        $this->_secretKey = $secretKeyGetter->getSecretKey($secretName);
    }

    public function setCollection($name, CollectionConfig $config)
    {
        $this->collections[$name] = $config;
    }

    public function makeCollectionConfig(array $fieldConfigs = [], $collectionType = self::COLLECTION_MONGO)
    {
        if ($collectionType === self::COLLECTION_MONGO) {
            return new MongoCollectionConfig($this->_secretKey, $fieldConfigs);
        }
        return new CollectionConfig($this->_secretKey, $fieldConfigs);
    }

    /**
     * 根据字段配置构建字段
     *
     * @param array $fieldsConfigs 字段配置
     * ```php
     * [
     *  'user' => [
     *      // 配置 email 字段
     *      'email' => ['security|hash', 'trim', 'codecWithField', 'codecFuncs', 'returnOriginal']
     *      'mobile' => 2, // security
     *      // 多层级的字段
     *      'third.email' => ['security|hash'],
     *  ],
     * 'login_record' => [
     *      // fields ...
     *      // 数组方式配置
     *      [['field_1', 'field_2'], 'security', 'trim', 'codecWithField', 'codecFuncs', 'returnOriginal'],
     *      [['field_3', 'field_3'], 'hash'],
     * ],
     * ]
     * ```
     * @return FieldConfig[] 字段名作为索引
     */
    public function makeFields(array $fieldsConfigs)
    {
        if (isset($fieldsConfigs[0])) { // 索引数组格式
            $configs = [];
            foreach ($fieldsConfigs as $line) {
                $opts = array_slice($line, 1);
                foreach ((array)$line[0] as $field) {
                    $configs[$field] = $opts;
                }
            }
            $fieldsConfigs = $configs;
        }

        $fields = [];
        foreach ($fieldsConfigs as $key => $params) {
            $field = new FieldConfig($this->_secretKey);
            foreach ((array)$params as $i => $v) {
                switch ($i) {
                    case 0:
                        $field->codecType = ($v === 'security' || $v === FieldConfig::SECURITY) ? FieldConfig::SECURITY : FieldConfig::HASH;
                        break;
                    case 1:
                        $field->afterDecoded = $v;
                        break;
                    case 2:
                        $field->codecWithField = $v;
                        break;
                    case 3:
                        $field->codecFuncs = $v;
                        break;
                    case 4:
                        $field->returnOriginalValueWhenDecryptedFailed = boolval($v);
                        break;
                }
            }

            $field->init();
            $fields[$key] = $field;
        }

        return $fields;
    }

    public function collectionExists($collectionName)
    {
        return isset($this->collections[$collectionName]);
    }

    /**
     * get collection codec
     *
     * @param string $collectionName
     * @return CodecInterface
     */
    public function getCodec($collectionName)
    {
        return isset($this->collections[$collectionName]) ? $this->collections[$collectionName] : DummyCodec::instance();
    }
}
