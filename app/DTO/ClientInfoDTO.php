<?php

namespace App\DTO;

class ClientInfoDTO
{
    public string $ip;
    public string $userAgent;

    public function __construct(string $ip, string $userAgent)
    {
        $this->ip = $ip;
        $this->userAgent = $userAgent;
    }
}
