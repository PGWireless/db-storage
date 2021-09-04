<?php

namespace DBStorage\Codec\Adapter;

use Yii;
use yii\mongodb\Database;

class MongoDatabase extends Database
{
    /** @inheritDoc */
    protected function selectCollection($name)
    {
        return Yii::createObject([
            'class' => MongoCollection::class,
            'database' => $this,
            'name' => $name,
        ]);
    }
}
