<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class StudentsImport implements ToCollection
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

        // Сохраняем данные студентов
        $this->attendanceImport->setStudentsData($rows->toArray());
    }
}
