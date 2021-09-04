<?php

namespace DBStorage\Codec\Adapter;

use Yii;
use yii\base\Component;
use InvalidArgumentException;
use DBStorage\Codec\FieldConfig;
use DBStorage\Codec\ProjectConfig;
use DBStorage\Codec\CollectionConfig;
use DBStorage\Codec\SecretKeyGetterInterface;

class PGMongoStorageComponent extends Component
{
    const DEFAULT_NAME = 'pg-mongo-storage';

    public $secretKeyName;

    /**
     * 返回 `SecretKeyGetterInterface` 的函数或实现 `SecretKeyGetterInterface` 的实例
     *
     * @var SecretKeyGetterInterface|callable
     */
    protected $secretGetter;

    /**
     * ```php
     * [
     *  'user' => [
     *      // 配置 email 字段
     *      'email' => ['security|hash', 'codecWithField', 'codecFuncs', 'returnOriginal']
     *      // 多层级的字段
     *      'third.email' => ['security|hash'],
     *  ],
     * 'login_record' => [
     *      // fields ...
     * ],
     * ]
     * ```
     *
     * @var array
     */
    public $collections = [];

    /** @var \DBStorage\Codec\ProjectConfig */
    private $projectConfig;

    /**
     * get static instance
     *
     * @param string $name
     * @return static
     */
    public static function instance($name = self::DEFAULT_NAME)
    {
        return Yii::$app->get($name, false);
    }

    public function setSecretGetter($value)
    {
        if ($value instanceof SecretKeyGetterInterface) {
            $this->secretGetter = $value;
            return;
        } elseif (is_callable($value)) {
            $ins = call_user_func($value);
            if ($ins instanceof SecretKeyGetterInterface) {
                $this->secretGetter = $ins;
                return;
            }
        }

        throw new InvalidArgumentException('invalid argument type');
    }

    /**
     * get ProjectConfig
     *
     * @return ProjectConfig
     */
    public function getProjectConfig()
    {
        if ($this->projectConfig === null) {
            $this->projectConfig = new ProjectConfig($this->secretKeyName, $this->secretGetter);
        }

        return $this->projectConfig;
    }

    private $_collectionConfigs = [];

    /**
     * get CollectionConfig
     *
     * @param string $name
     * @return CollectionConfig|null
     */
    public function getCollectionConfig($name)
    {
        if (isset($this->_collectionConfigs[$name])) {
            return $this->_collectionConfigs[$name];
        }

        if (!isset($this->collections[$name])) {
            return null;
        }

        $collection = $this->getProjectConfig()->makeCollectionConfig();

        foreach ($this->collections[$name] as $key => $fieldParams) {
            $field = $collection->makeSimpleFieldConfig();
            foreach ($fieldParams as $i => $v) {
                switch ($i) {
                    case 0:
                        $field->codecType = $v === 'security' ? FieldConfig::SECURITY : FieldConfig::HASH;
                        break;
                    case 1:
                        $field->codecWithField = $v;
                        break;
                    case 2:
                        $field->codecFuncs = $v;
                        break;
                    case 3:
                        $field->returnOriginalValueWhenDecryptedFailed = boolval($v);
                        break;
                }
            }

            $collection->setFieldConfig($key, $field);
        }

        $this->_collectionConfigs[$name] = $collection;

        return $collection;
    }
}
