<?php

namespace Asper\LangExcelConverter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Asper\LangExcelConverter\Imports\TranslationsImport;

class ImportExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang-excel:import
                            {input=translations.xlsx : input filename}
                            {--disk= : input disk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Excel to Langs';

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
        $filename = $this->argument('input');
        $disk = $this->option('disk');

        Excel::import(new TranslationsImport, $filename, $disk);

        $path = str_replace(base_path(), '', Storage::disk($disk)->path($filename));
        $this->info($path . ' imported');
    }
}
