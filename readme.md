# Lang Excel Converter

Export localization to Excel. Each sheet is a group contains its' all locales translation.
Import Excel to localization files.

## Installation

Via Composer

``` bash
$ composer require asper/langexcelconverter
```

## Usage

### Import
Command:
```php
php artisan lang-excel:import {filename=translations.xlsx} {--disk}
```

Controller:
```php
use Maatwebsite\Excel\Facades\Excel;
use Asper\LangExcelConverter\Imports\TranslationsImport;

Excel::import(new TranslationsImport, 'YOUR FILE');
```

### Export
Command:
```php
php artisan lang-excel:export {filename=translations.xlsx} {--disk}
```

Controller:
```php
use Maatwebsite\Excel\Facades\Excel;
use Asper\LangExcelConverter\Exports\TranslationsExport;

// store
Excel::store(new TranslationsExport, 'YOUR FILE NAME');

// send download response
return Excel::download(new TranslationsExport, 'YOUR FILE NAME');
```

more Excel Facade usage: [Maatwebsite/Laravel-Excel](https://laravel-excel.com/)

### Demo Project
[asperhsu/langexcelconverter-demo](https://github.com/asperhsu/langexcelconverter-demo)
