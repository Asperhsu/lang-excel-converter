<?php

namespace Asper\LangExcelConverter\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TranslationGroupExport implements FromCollection, WithTitle, WithHeadings
{
    protected $group;
    protected $trans;

    public function __construct(string $group, array $trans)
    {
        $this->group = $group;
        $this->trans = $trans;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $rows = collect();

        foreach ($this->trans as $key => $locales) {
            $rows->push(array_merge(compact('key'), $locales));
        }

        return $rows;
    }

    public function title(): string
    {
        // title can not has '/'
        return str_replace('/', '.', $this->group);
    }

    public function headings(): array
    {
        return array_merge([
            'Key',
        ], array_keys(head($this->trans)));
    }
}
