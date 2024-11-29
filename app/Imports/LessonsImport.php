<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class LessonsImport implements ToCollection
{
    protected $attendanceImport;

    public function __construct(AttendanceImport $attendanceImport)
    {
        $this->attendanceImport = $attendanceImport;
    }

    public function collection(Collection $rows)
    {
        // Пропускаем первую строку (заголовки)
        $rows = $rows->slice(1);

        // Сохраняем данные уроков
        $this->attendanceImport->setLessonsData($rows->toArray());
    }
}
