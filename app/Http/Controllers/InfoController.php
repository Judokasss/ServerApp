<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

use App\DTO\ClientInfoDTO;
use App\DTO\DatabaseInfoDTO;
use App\DTO\ServerInfoDTO;


class InfoController extends Controller
{
    // 1. Данные об установленной версии PHP
    public function serverInfo()
    {
        $phpVersion = phpversion();
        $serverUname = php_uname();

        // Создаем объект DTO для информации о сервере
        $serverInfoDTO = new ServerInfoDTO($phpVersion, $serverUname);

        return response()->json($serverInfoDTO);
    }

    // 2. Данные о клиенте (IP, User-Agent)
    public function clientInfo()
    {
        $clientIP = Request::ip();
        $userAgent = Request::header('User-Agent');

        // Создание объекта DTO для клиента
        $clientInfoDTO = new ClientInfoDTO($clientIP, $userAgent);

        // Возвращаем данные в формате JSON
        return response()->json($clientInfoDTO);
    }

    // 3. Данные об используемой базе данных
    public function databaseInfo()
    {
        $databaseName = DB::connection()->getDatabaseName();
        $connectionType = config('database.default');

        // Создание объекта DTO для базы данных
        $databaseInfoDTO = new DatabaseInfoDTO($databaseName, $connectionType);

        // Возвращаем данные в формате JSON
        return response()->json($databaseInfoDTO);
    }
}
