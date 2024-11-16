<?php

namespace App\DTO\UserDTO;

class UserCollectionDTO
{
  private array $users;

  public function __construct(array $users)
  {
    $this->users = $users;
  }

  public function toArray(): array
  {
    return array_map(function ($user) {
      // Проверяем, является ли $user массивом, и если да, фильтруем и преобразуем его в объект UserDTO
      if (is_array($user)) {
        $filteredUser = array_intersect_key($user, array_flip(['id', 'username', 'email', 'birthday']));
        $user = new UserDTO(
          $filteredUser['id'] ?? null,
          $filteredUser['username'] ?? null,
          $filteredUser['email'] ?? null,
          $filteredUser['birthday'] ?? null
        );
      }
      return $user->toArray();
    }, $this->users);
  }
}
