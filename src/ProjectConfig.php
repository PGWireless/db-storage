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
