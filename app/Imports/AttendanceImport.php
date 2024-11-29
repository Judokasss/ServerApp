<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceImport implements WithMultipleSheets
{
  protected $studentsData = [];
  protected $lessonsData = [];

  public function sheets(): array
  {
    return [
      'Students' => new StudentsImport($this),
      'Lessons' => new LessonsImport($this),
    ];
  }

  public function setStudentsData(array $data)
  {
    $this->studentsData = $data;
  }

  public function setLessonsData(array $data)
  {
    $this->lessonsData = $data;
  }

  public function students()
  {
    return $this->studentsData;
  }

  public function lessons()
  {
    return $this->lessonsData;
  }
}
