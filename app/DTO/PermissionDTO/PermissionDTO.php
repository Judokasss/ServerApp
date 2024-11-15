<?php

namespace App\DTO\PermissionDTO;

class PermissionDTO
{
    public $name;
    public $code;
    public $description;
    public $created_by;

    public function __construct($name, $description = null, $code, $created_by)
    {
        $this->name = $name;
        $this->description = $description;
        $this->code = $code;
        $this->created_by = $created_by;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'created_by' => $this->created_by,
        ];
    }
}
