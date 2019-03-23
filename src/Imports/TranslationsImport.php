<?php

namespace Asper\LangExcelConverter\Imports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Asper\LangExcelConverter\Services\TranslationManager;

class TranslationsImport implements ToCollection, WithHeadingRow, WithEvents
{
    public $manager;
    public $sheetNames = [];
    public $sheetData = [];

    public function __construct()
    {
        $this->manager = resolve(TranslationManager::class);
    }

    /**
     * @param array $array
     */
    public function collection(Collection $rows)
    {
        // because sheet name can not has '/', revert transform from export.
        $group = str_replace('.', '/', $this->currentSheetName());

        // transform to TranslationManager data structure
        $locales = [];
        foreach ($rows as $row) {
            foreach ($row->except('key') as $lang => $value) {
                $locales[$lang][$row['key']] = $value;
            }
        }

        // revert array dot data
        foreach ($locales as $lang => $trans) {
            $locales[$lang] = $this->manager::array_undot($trans);
        }

        // store to files
        array_set($this->manager, $group, $locales);
        $this->manager->save($group);
    }

    public function currentSheetName()
    {
        return last($this->sheetNames);
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $this->sheetNames[] = $event->getSheet()->getTitle();
            }
        ];
    }
}
