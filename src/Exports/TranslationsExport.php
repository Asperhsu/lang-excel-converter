<?php

namespace Asper\LangExcelConverter\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;
use Asper\LangExcelConverter\Services\TranslationManager;

class TranslationsExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        $groups = resolve(TranslationManager::class)->groups();

        $sheets = [];

        foreach ($groups as $group => $trans) {
            $sheets[] = new TranslationGroupExport($group, $trans);
        }

        return $sheets;
    }
}
