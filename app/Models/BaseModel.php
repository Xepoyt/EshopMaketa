<?php

namespace App\Models;

use Nette\SmartObject;
use Nette\Database\Explorer;
use Nette\Database\Connection;

abstract class BaseModel{
    /** @var Explorer */
    protected $explorer;

    /** @var Connection */
    protected $connection;

    public function __construct(Explorer $explorer)
    {
        $this->explorer = $explorer;
        $this->connection = $explorer->getConnection();
    }

    abstract public function getTableName(): string;

    public function getZaznamy()
    {
        return $this->explorer->table($this->getTableName());
    }

    public function vlozit(array $data)
    {
        return $this->explorer->table($this->getTableName())->insert($data);
    }

    public function upravit(string $idName, int $idVal, array $data)
    {
        return $this->explorer->table($this->getTableName())->where($idName, $idVal)->update($data);
    }
}