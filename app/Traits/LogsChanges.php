<?php

namespace App\Traits;

use App\Models\ChangeLog;
use Illuminate\Support\Facades\Auth;

trait LogsChanges
{
  public static function bootLogsChanges()
  {
    //static:: указывает, что метод будет вызван в контексте класса, который использует трейт
    // Логирование после создания модели
    static::created(function ($model) {
      static::logChange($model, 'create', [], $model->attributesToArray());
    });

    // Логирование перед обновлением модели
    static::updating(function ($model) {
      $before = $model->getOriginal();
      $after = $model->getDirty();
      static::logChange($model, 'update', $before, $after);
    });

    // Логирование перед удалением модели
    static::deleting(function ($model) {
      static::logChange($model, 'delete', $model->attributesToArray(), []);
    });
  }

  private static function logChange($model, $action, $before, $after)
  {
    // Попытка получить ID текущего пользователя, если он авторизован
    $userId = Auth::check() ? Auth::id() : null;

    // Если пользователь не авторизован, используем системный ID или другой идентификатор
    if ($userId === null) {
      $userId = 1;
    }

    ChangeLog::create([
      'entity_type' => $model->getTable(), // Имя сущности
      'entity_id' => $model->id, // ID сущности
      'before' => json_encode($before),
      'after' => json_encode($after),
      'created_by' => $userId, // Пользователь, выполнивший изменение
    ]);
  }
}
