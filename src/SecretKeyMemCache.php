<?php

namespace DBStorage\Codec;

class SecretKeyMemCache implements SecretKeyCacheInterface
{
    public $apcuEnabled = false;
    public $yacEnabled = false;

    private $_yac;

    private static $_instance;
    
    /**
     * static instance
     *
     * @return static
     */
    public static function instance()
    {
        if (!self::$_instance) {
            self::$_instance = new SecretKeyMemCache();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        $this->apcuEnabled = extension_loaded('apcu');
        if (!$this->apcuEnabled) {
            $this->yacEnabled = extension_loaded('yac') && ini_get('yac.enable');
            if ($this->yacEnabled) {
                $this->_yac = new \Yac();
            }
        }

        syslog(LOG_WARNING, 'can not use cache, check extensions of apcu or yac');
    }    

    public function set($name, $value)
    {
        $name .= "kms:";

        if ($this->apcuEnabled) {
            return apcu_store($name, $value);
        } elseif ($this->_yac) {
            return $this->_yac->set($name, $value);
        }
        return false;
    }

    public function get($name)
    {
        $name .= "kms:";

        if ($this->apcuEnabled) {
            return apcu_fetch($name);
        } elseif ($this->_yac) {
            return $this->_yac->get($name);
        }
        return false;
    }
}
