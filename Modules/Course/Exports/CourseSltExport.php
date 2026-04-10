<?php

namespace Modules\Course\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CourseSltExport implements FromArray, WithHeadings
{
    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function __construct(private readonly array $rows)
    {
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Activity',
            'F2F Hours',
            'Non F2F Hours',
            'Independent Hours',
            'Total Hours',
        ];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $lines = [];

        foreach ($this->rows as $row) {
            $lines[] = [
                $row['activity'] ?? '',
                $row['f2f_hours'] ?? 0,
                $row['non_f2f_hours'] ?? 0,
                $row['independent_hours'] ?? 0,
                $row['total_hours'] ?? 0,
            ];
        }

        return $lines;
    }
}
