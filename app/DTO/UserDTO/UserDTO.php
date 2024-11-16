<?php

namespace App\DTO\UserDTO;

class UserDTO
{
  public $id;
  public $username;
  public $email;
  public $birthday;

  public function __construct($id, $username, $email, $birthday)
  {
    $this->id = $id;
    $this->username = $username;
    $this->email = $email;
    $this->birthday = $birthday;
  }

  // Метод для преобразования DTO в массив
  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'username' => $this->username,
      'email' => $this->email,
      'birthday' => $this->birthday,
    ];
  }
}
