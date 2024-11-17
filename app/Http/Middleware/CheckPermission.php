<?php

namespace App\Http\Middleware;

use Closure;

class CheckPermission
{
  public function handle($request, Closure $next, $permissionCode)
  {

    if (!$request->user()->hasPermission($permissionCode))
      return response()->json([
        'error' => "У вас нет доступа к данной операции. Необходимое разрешение: {$permissionCode}"
      ], 403);

    return $next($request);
  }
}
