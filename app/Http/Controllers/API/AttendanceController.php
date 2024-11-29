<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AttendanceImport;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function processAttendanceFile(Request $request)
    {
        // Проверяем, был ли файл передан
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'Файл не передан в запросе'], 400);
        }

        $file = $request->file('file');

        // Проверяем валидность файла
        if (!$file->isValid() || !in_array($file->getClientOriginalExtension(), ['xls', 'xlsx', 'csv'])) {
            return response()->json(['error' => 'Неверный формат файла. Допустимые форматы: xls, xlsx, csv'], 400);
        }

        // Чтение данных из Excel
        try {
            $import = new AttendanceImport();
            $data = Excel::import($import, $file);

            // Получаем данные студентов и занятий
            $studentsData = $import->students(); // Получаем данные студентов
            $lessonsData = $import->lessons(); // Получаем данные занятий
            Log::info('Students Data:', $studentsData);
            Log::info('Lessons Data:', $lessonsData);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при чтении файла: ' . $e->getMessage()], 500);
        }

        // Обработка данных
        $students = $this->processStudents($studentsData, $lessonsData);
        Log::info('Processed Students:', $students);
        $result = $this->calculateResults($students);
        $autoPassStudents = $this->getAutoPassStudents($students);

        // Возвращаем результат
        return response()->json([
            'students' => $result['students'],
            'auto_pass_students' => $autoPassStudents,
            'result' => $result['result'],
        ]);
    }

    private function processStudents($studentsData, $lessonsData)
    {
        $students = [];

        foreach ($studentsData as $student) {
            // Структурируем данные студента в ассоциативный массив
            $studentName = $student[0]; // Имя студента
            $subgroup = $student[1] ?? 1; // Подгруппа (если не указана, ставим 1)
            $labsCompleted = $student[2] ?? 0; // Лабораторные работы (если не указаны, ставим 0)

            Log::info('Processing student:', ['student_name' => $studentName, 'subgroup' => $subgroup]);

            $lessons = [];

            // Перебор всех уроков
            foreach ($lessonsData as $lesson) {
                if (isset($lesson[0])) {
                    // Преобразуем значение даты в стандартный формат
                    $lessonDate = Date::excelToDateTimeObject($lesson[0])->format('d.m.Y');
                } else {
                    Log::warning('Дата отсутствует в уроке', $lesson);
                    continue; // пропускаем урок, если нет даты
                }

                // Проверяем, если студент соответствует уроку
                $visit = ($lesson[5] == $studentName) && // студент в уроке
                    ($lesson[4] == $subgroup || $lesson[4] == null); // подгруппа совпадает или не указана

                // Логирование посещения
                if ($visit) {
                    Log::info('Lesson match found', ['lesson' => $lesson, 'student' => $studentName]);
                }
                $lessonTime = $this->convertExcelTimeToTime($lesson[1]);
                // Добавляем информацию о посещении с преобразованной датой
                $lessons[] = [
                    'date' => $lessonDate,  // Преобразованная дата
                    'time' =>  $lessonTime, // lesson[1] — это значение времени в формате Excel,  // время
                    'type' => $lesson[2],  // тип (лекция, лабораторная)
                    'number' => $lesson[3],  // номер занятия
                    'subgroups' => $lesson[4],  // подгруппа
                    'visit' => $visit,  // true, если студент был на уроке
                ];
            }

            // Добавляем студента в итоговый массив
            $students[] = [
                'name' => $studentName,
                'subgroup' => $subgroup,
                'leasons' => $lessons,
                'visit_percent' => $this->calculateVisitPercent($lessons),
                'success_labs_percent' => $this->calculateLabsPercent($labsCompleted),
                'success_labs' => $labsCompleted,
                'result' => false,
            ];
        }

        Log::info('Final student data:', $students);

        return $students;
    }

    private function calculateVisitPercent($lessons)
    {
        $total = count($lessons);
        $visited = array_filter($lessons, fn($lesson) => $lesson['visit']);
        return round(count($visited) / $total * 100, 2);
    }

    private function calculateLabsPercent($labsCompleted)
    {
        // Лабораторные работы
        $requiredLabs = 6; // Пример: обязательных 6 лабораторных
        return round($labsCompleted / $requiredLabs * 100, 2);
    }

    private function calculateResults($students)
    {
        $success = 0;
        $unsuccessfully = 0;

        foreach ($students as &$student) {
            // Проверка автоматического зачета
            if ($student['visit_percent'] >= 80 && $student['success_labs_percent'] == 100) {
                $student['result'] = true;
                $success++;
            } else {
                $unsuccessfully++;
            }
        }

        return [
            'students' => $students,
            'result' => [
                'success' => $success,
                'unsuccessfully' => $unsuccessfully,
            ]
        ];
    }

    private function getAutoPassStudents($students)
    {
        return array_filter($students, fn($student) => $student['result'] === true);
    }

    function convertExcelTimeToTime($excelTime)
    {
        $hours = floor($excelTime * 24); // Получаем количество полных часов
        $minutes = round(($excelTime * 24 - $hours) * 60); // Получаем количество минут
        return sprintf('%02d:%02d', $hours, $minutes); // Возвращаем время в формате 'HH:MM'
    }
}
