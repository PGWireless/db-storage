<?php

namespace DBStorage\Codec;

use Exception;
use Throwable;

class CodecException extends Exception
{
    public function __construct($msg = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($msg, $code, $previous);
    }
}
