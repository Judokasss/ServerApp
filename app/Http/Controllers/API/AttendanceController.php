<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function upload(Request $request)
    {
        // Проверка наличия файла
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'File not found'], 400);
        }

        $file = $request->file('file');
        $data = Excel::toArray([], $file);

        // Обработка данных
        $students = $this->processData($data);

        return response()->json($students);
    }

    private function processData($data)
    {
        $groups = [];
        $rows = $data[0]; // Первый лист Excel

        // Извлечение данных для занятий
        $lessonHeaders = array_slice($rows[0], 4); // Типы занятий
        $lessonDates = array_slice($rows[1], 4);   // Даты
        $lessonTimes = array_slice($rows[2], 4);   // Время
        $lessonNumbers = array_slice($rows[3], 4); // Номера занятий

        // Минимальное количество лабораторных для зачета
        $minLabsForCredit = 4; // Например, 4 лабораторные работы из 6 обязательны

        $totalLabs = 6;

        // Процент посещаемости для зачета
        $minVisitPercentForCredit = 80; // 80% посещаемости для зачета

        // Обработка студентов
        foreach (array_slice($rows, 4) as $row) {
            // Пропускаем пустые строки
            if (empty($row[0]) || empty($row[2])) {
                continue;
            }

            $name = $row[0];
            $subgroup = $row[1] ?? 1; // Если подгруппа не указана, использовать 1
            $group = $row[2];
            $submitted_labs = $row[3];
            $attendance = [];

            foreach (array_slice($row, 4) as $index => $visit) {
                $attendance[] = [
                    'date' => $this->convertExcelDate($lessonDates[$index]),
                    'time' => $this->convertExcelTimeToTime($lessonTimes[$index]),
                    'type' => $lessonHeaders[$index] === 'Лекция' ? 'lecture' : 'lab',
                    'number' => $lessonNumbers[$index],
                    'subgroup' => $subgroup, // Используем subgroup из текущей строки
                    'visit' => $visit === '+' // Принимаем "+" как посещение
                ];
            }

            // Расчет процентов посещаемости
            $total_classes = count($attendance);
            $visited_classes = count(array_filter($attendance, fn($att) => $att['visit']));
            $visit_percent = $total_classes ? round(($visited_classes / $total_classes) * 100, 2) : 0;

            // Расчет процентов лабораторных
            $success_labs_percent = $submitted_labs ? round(($submitted_labs / $totalLabs) * 100, 2) : 0; // 6 - пример общего числа лаб

            // Условие для зачета: минимум 80% посещаемости и минимум 4 сданных лабораторных работы
            $result = $visit_percent >= $minVisitPercentForCredit && $submitted_labs >= $minLabsForCredit;

            // Добавляем студента в массив по группе
            if (!isset($groups[$group])) {
                $groups[$group] = [
                    'group_name' => $group,
                    'students' => [],
                    'result' => [
                        'success' => 0,
                        'unsuccessfully' => 0
                    ]
                ];
            }

            // Добавляем студента в нужную группу
            $groups[$group]['students'][] = [
                'name' => $name,
                'subgroup' => $subgroup,
                'leasons' => $attendance,
                'visit_percent' => $visit_percent,
                'success_labs_percent' => $success_labs_percent,
                'success_labs' => $submitted_labs,
                'result' => $result
            ];

            // Подсчитываем успешных и неуспешных студентов в группе
            if ($result) {
                $groups[$group]['result']['success']++;
            } else {
                $groups[$group]['result']['unsuccessfully']++;
            }
        }

        // Преобразуем группы в массив
        return array_values($groups);
    }



    private function convertExcelDate($excelDate)
    {
        return Carbon::createFromFormat('Y-m-d', gmdate('Y-m-d', ($excelDate - 25569) * 86400))->format('Y-m-d');
    }


    private function convertExcelTimeToTime($excelTime)
    {
        $hours = floor($excelTime * 24); // Получаем количество полных часов
        $minutes = round(($excelTime * 24 - $hours) * 60); // Получаем количество минут
        return sprintf('%02d:%02d', $hours, $minutes); // Возвращаем время в формате 'HH:MM'
    }
}
