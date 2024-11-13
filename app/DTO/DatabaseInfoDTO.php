<?php

namespace App\DTO;

class DatabaseInfoDTO
{
    public string $databaseName;
    public string $connection;

    public function __construct(string $databaseName, string $connection)
    {
        $this->databaseName = $databaseName;
        $this->connection = $connection;
    }
}
