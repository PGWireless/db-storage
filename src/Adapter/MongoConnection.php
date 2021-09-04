<?php

namespace DBStorage\Codec\Adapter;

use Yii;
use yii\mongodb\Connection;

class MongoConnection extends Connection
{
    public $storageName = PGMongoStorageComponent::DEFAULT_NAME;
    
    /** @inheritDoc */
    public function createCommand($document = [], $databaseName = null)
    {
        return new MongoCommand([
            'db' => $this,
            'databaseName' => $databaseName,
            'document' => $document,
            'storageName' => $this->storageName,
        ]);
    }

    /** @inheritDoc */
    public function selectDatabase($name)
    {
        return Yii::createObject([
            'class' => MongoDatabase::class,
            'name' => $name,
            'connection' => $this,
        ]);
    }
}
