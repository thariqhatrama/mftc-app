<?php

namespace Database\Seeders;

use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use App\Models\SelfAssessmentQuestion;
use Illuminate\Database\Seeder;

class ChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['2.1.1', 'Manajemen', 'Kebijakan Wisata Ramah Muslim ditandatangani manajemen', 'file'],
            ['2.1.2', 'Manajemen', 'Tim pengelola dengan tugas & tanggung jawab', 'textarea'],
            ['2.2.1', 'Fasilitas', 'Mushola bersih, air wudhu, petunjuk kiblat', 'boolean'],
            ['2.2.2', 'Fasilitas', 'Toilet bersih & terawat', 'boolean'],
            ['2.3.1', 'Makanan', 'Penyediaan makanan halal (tersertifikasi)', 'file'],
            ['2.4.1', 'Pelayanan', 'Pelayanan umum: mekanisme penerimaan, informasi waktu shalat', 'textarea'],
        ];

        foreach ($items as $i => [$criteriaId, $category, $text, $inputType]) {
            SelfAssessmentQuestion::query()->updateOrCreate(
                [
                    'scope' => ScopeObject::HOTEL->value,
                    'level' => CertificationLevel::ONE_STAR->value,
                    'sort_order' => $i + 1,
                ],
                [
                    'category' => $category,
                    'question_text' => "[{$criteriaId}] {$text}",
                    'input_type' => $inputType,
                    'input_options' => $inputType === 'boolean'
                        ? ['Ya', 'Tidak']
                        : null,
                    'helper_text' => null,
                    'is_required' => true,
                    'is_active' => true,
                    'has_answers' => false,
                ]
            );
        }
    }
}
