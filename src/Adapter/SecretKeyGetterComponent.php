<?php

namespace DBStorage\Codec\Adapter;

use yii\base\Component;
use InvalidArgumentException;
use DBStorage\Codec\SecretKeyGetterInterface;

class SecretKeyGetterComponent extends Component
{
    public $secretKeyName;

    /**
     * 返回 `SecretKeyGetterInterface` 的函数或实现 `SecretKeyGetterInterface` 的实例
     *
     * @var SecretKeyGetterInterface|callable
     */
    public $getter;

    private $_secretKey;

    public function init()
    {
        parent::init();

        $value = $this->getter;
        if ($value instanceof SecretKeyGetterInterface) {
            $this->getter = $value;
            return;
        } elseif (is_callable($value)) {
            $ins = call_user_func($value);
            if ($ins instanceof SecretKeyGetterInterface) {
                $this->getter = $ins;
                return;
            }
        }

        throw new InvalidArgumentException('invalid argument type');
    }

    public function getSecretKey()
    {
        if ($this->_secretKey === null) {
            $this->_secretKey = $this->getter->getSecretKey($this->secretKeyName);
        }

        return $this->_secretKey;
    }
}
