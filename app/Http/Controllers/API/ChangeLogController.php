<?php

namespace App\Http\Controllers\API;

use App\Models\ChangeLog;
use App\Http\Controllers\Controller;

class ChangeLogController extends Controller
{
  public function restoreEntity($id)
  {
    // Найти запись в ChangeLog по ID
    $changeLog = ChangeLog::find($id);
    // Проверка есть ли запись
    if (!$changeLog) {
      return response()->json(['message' => 'Entry not found'], 404);
    }

    // Автоматически преобразуем имя таблицы в имя модели и получаем полное имя класса
    $modelClass = 'App\\Models\\' . ucfirst(\Illuminate\Support\Str::singular($changeLog->entity_type));
    // Проверка объявлен ли класс
    if (!class_exists($modelClass)) {
      return response()->json(['message' => 'Model not found'], 404);
    }

    // Находим запись по ID сущности
    $entity = $modelClass::find($changeLog->entity_id);

    if (!$entity) {
      return response()->json(['message' => 'Entity not found'], 404);
    }

    // Восстанавливаем состояние из before
    // Декодируем before в ассоциативный массив
    $beforeState = json_decode($changeLog->before, true);
    // Заполняем свойства сущности данными из массива
    $entity->fill($beforeState);
    // Сохраняем изменения в бд
    $entity->save();

    return response()->json([
      'message' => 'Entity restored successfully',
      'restored_entity' => $entity,
    ]);
  }
}
