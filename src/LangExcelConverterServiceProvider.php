<?php

namespace Asper\LangExcelConverter;

use Illuminate\Support\ServiceProvider;

class LangExcelConverterServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // $this->mergeConfigFrom(__DIR__ . '/../config/excel.php', 'excel');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['langexcelconverter'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/excel.php' => config_path('excel.php'),
        ], 'langexcelconverter.config');

        // Registering package commands.
        $this->commands([
            \Asper\LangExcelConverter\Commands\ImportExcel::class,
            \Asper\LangExcelConverter\Commands\ExportLang::class,
        ]);
    }
}
