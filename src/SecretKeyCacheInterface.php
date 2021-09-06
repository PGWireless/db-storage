<?php

namespace DBStorage\Codec;

interface SecretKeyCacheInterface
{
    /**
     * 写缓存
     *
     * @param string $name
     * @param string $value
     * @return string|false  失败时返回 false
     */
    public function set($name, $value);

    /**
     * 读缓存
     *
     * @param string $name
     * @return string|false 失败时返回 false
     */
    public function get($name);
}
