<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Events\AfterSheet;

class TracerStudyExport implements 
    FromArray, 
    WithHeadings, 
    WithStyles, 
    WithColumnWidths, 
    WithEvents
{
    protected $rows;

    public function __construct($data)
    {
        $this->rows = $data;
    }

    public function headings(): array
    {
        return [
            ['LAPORAN DATA TRACER STUDY'],
            [],
            ['Total Data'],
            [count($this->rows)],
            [],
            [
                'No',
                'Nama',
                'Email',
                'NIM',
                'Fakultas',
                'Program Studi',
                'Tahun Masuk',
                'Tahun Lulus',
                'Status Kerja',
                'Tempat Kerja',
                'Jabatan',
                'Jenis Pekerjaan',
                'Tipe Kerja',
                'Sektor',
                'Gaji',
                'Relevansi Studi',
                'Saran Untuk Kampus'
            ]
        ];
    }

    public function array(): array
    {
        $dataRows = [];

        foreach ($this->rows as $index => $row) {
            $dataRows[] = [
                $index + 1,
                $row->user->name ?? '-',
                $row->user->email ?? '-',
                $row->student_id_number ?? '-',
                $row->faculty->name ?? '-',
                $row->studyProgram->name ?? '-',
                $row->entry_year ?? '-',
                $row->graduation_year ?? '-',
                $row->employment_status ?? '-',
                $row->current_workplace ?? '-',
                $row->job_title ?? '-',
                $row->job_category ?? '-',
                $row->employment_type ?? '-',
                $row->employment_sector ?? '-',
                $row->monthly_income_range ?? '-',
                $row->job_study_relevance_level ?? '-',
                $row->suggestion_for_university ?? '-',
            ];
        }

        return $dataRows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]], // Judul
            3 => ['font' => ['bold' => true]], // Header ringkasan
            6 => ['font' => ['bold' => true]], // Header tabel
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 25,
            'C' => 30,
            'D' => 18,
            'E' => 35,
            'F' => 25,
            'G' => 15,
            'H' => 15,
            'I' => 18,
            'J' => 25,
            'K' => 20,
            'L' => 20,
            'M' => 18,
            'N' => 18,
            'O' => 15,
            'P' => 20,
            'Q' => 50,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Merge Title
                $sheet->mergeCells('A1:Q1');

                // Center Title
                $sheet->getStyle('A1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Wrap text kolom panjang (Saran)
                $sheet->getStyle('Q:Q')
                    ->getAlignment()
                    ->setWrapText(true);

                // Auto row height
                $sheet->getDefaultRowDimension()->setRowHeight(-1);

                // Alignment default kiri atas
                $sheet->getStyle($sheet->calculateWorksheetDimension())
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_TOP);

                // Freeze header tabel
                $sheet->freezePane('A7');
            }
        ];
    }
}
