<?php

namespace App\DTO;

class ServerInfoDTO
{
    public string $phpVersion;
    public string $serverInfo;

    public function __construct(string $phpVersion, string $serverInfo)
    {
        $this->phpVersion = $phpVersion;
        $this->serverInfo = $serverInfo;
    }
}
