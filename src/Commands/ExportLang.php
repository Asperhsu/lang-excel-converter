<?php

namespace Asper\LangExcelConverter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Asper\LangExcelConverter\Exports\TranslationsExport;
use Asper\LangExcelConverter\Services\TranslationManager;

class ExportLang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang-excel:export
                            {output=translations.xlsx : output filename}
                            {--disk= : output disk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export Langs to Excel';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $filename = $this->argument('output');
        $disk = $this->option('disk');

        $groups = resolve(TranslationManager::class)->groups();
        if (!count($groups)) {
            $this->error('You don\'t have any lang file');
        }

        Excel::store(new TranslationsExport, $filename, $disk);
        $path = str_replace(base_path(), '', Storage::disk($disk)->path($filename));
        $this->info($path . ' exported');
    }
}
