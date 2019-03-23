<?php

namespace Asper\LangExcelConverter\Services;

use Symfony\Component\Finder\Finder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use ArrayAccess;

class TranslationManager implements ArrayAccess
{
    protected $fileSystem;
    protected $basePath;
    protected $translations;

    public function __construct(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
        $this->basePath = app('path.lang');

        $this->load();
    }

    /**
     * load all lang files into array. format: group.locale.key => text
     *
     * @return array
     */
    public function load()
    {
        $translations = [];

        $pattern = '/([a-z]+)\/([\w\/\-_]+)\.php$/';
        $finder = Finder::create()->files()->name('*.php')->in($this->basePath);

        foreach ($finder as $file) {
            $matches = [];
            $relativePathname = str_replace(str_finish($this->basePath, DIRECTORY_SEPARATOR), '', $file->getPathname());
            preg_match($pattern, $relativePathname, $matches);

            if (count($matches) !== 3 || $matches[1] === 'vendor') {
                continue;
            }

            list($full, $lang, $group) = $matches;

            $translations[$group][$lang] = require $file->getPathname();
        }

        ksort($translations);

        return $this->translations = $translations;
    }

    /**
     * save translations to lang files
     *
     * @param $group  string|array group(s) to save
     * @return void
     */
    public function save($group = null)
    {
        $translations = $group ? array_only($this->translations, array_wrap($group)) : $this->translations;

        foreach ($translations as $group => $translation) {
            foreach ($translation as $lang => $content) {
                $path = str_finish($this->basePath, DIRECTORY_SEPARATOR) . $lang . DIRECTORY_SEPARATOR . $group . '.php';
                $this->checkFolder($path);
                $output = "<?php\n\nreturn " . static::var_export_short($content) . ';' . \PHP_EOL;
                $this->fileSystem->put($path, $output);
            }
        }

        return $this;
    }

    /**
     * get key with locales combination by group, key will be transfor to array_dot
     * format: group.key.[locales] => text
     *
     * @param boolean $unDot    revert key to multidimention array
     * @return array
     */
    public function groups(bool $unDot = false)
    {
        return collect(array_keys($this->translations))->mapWithKeys(function ($group) use ($unDot) {
            return [$group => $this->group($group, $unDot)];
        })->toArray();
    }

    /**
     * get key with locales combination, key will be transfor to array_dot
     * format: key.[locales] => text
     *
     * @param string $group     translation group
     * @param boolean $unDot    revert key to multidimention array
     * @return array
     */
    public function group(string $group, bool $unDot = false)
    {
        $locales = collect($this->get($group));

        // merge all keys and unique
        $keys = $locales->map(function ($trans, $lang) {
            return array_keys(array_dot($trans));
        })->reduce(function ($carry, $item) {
            return collect($carry)->merge($item);
        }, [])->unique();

        // store translations to each key in each locale
        $groupLocales = $keys->mapWithKeys(function ($key) use ($locales) {
            return [$key => $locales->map(function ($trans, $lang) use ($key) {
                return array_get($trans, $key);
            })->toArray()];
        })->toArray();

        return $unDot ? static::array_undot($groupLocales) : $groupLocales;
    }

    /**
     * var_export to short array syntax
     * source: https://stackoverflow.com/a/35207172
     *
     * @param array $data
     * @param boolean $return
     * @return void
     */
    public static function var_export_short(array $data, $return = true)
    {
        $dump = var_export($data, true);

        $dump = preg_replace('#(?:\A|\n)([ ]*)array \(#i', '[', $dump); // Starts
        $dump = preg_replace('#\n([ ]*)\),#', "\n$1],", $dump); // Ends
        $dump = preg_replace('#=> \[\n\s+\],\n#', "=> [],\n", $dump); // Empties

        if (gettype($data) == 'object') { // Deal with object states
            $dump = str_replace('__set_state(array(', '__set_state([', $dump);
            $dump = preg_replace('#\)\)$#', '])', $dump);
        } else {
            $dump = preg_replace('#\)$#', ']', $dump);
        }

        $dump = str_replace('  ', '    ', $dump);

        if ($return === true) {
            return $dump;
        } else {
            echo $dump;
        }
    }

    /**
     * Opposite of array_dot
     * source: https://github.com/laravel/framework/issues/1851#issuecomment-20796924
     *
     * @param array $array
     * @return array
     */
    public static function array_undot(array $array)
    {
        $ret = [];
        foreach ($array as $key => $value) {
            array_set($ret, $key, $value);
        }
        return $ret;
    }

    public function has($key)
    {
        return Arr::has($this->translations, $key);
    }

    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }
        return Arr::get($this->translations, $key, $default);
    }

    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];
        foreach ($keys as $key => $value) {
            Arr::set($this->translations, $key, $value);
        }
        return $this;
    }

    public function all()
    {
        return $this->translations;
    }

    public function offsetExists($key)
    {
        return $this->has($key);
    }

    public function offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    public function offsetUnset($key)
    {
        $this->set($key, null);
    }

    public function __toString()
    {
        return json_encode($this->translations);
    }

    protected function checkFolder(string $path)
    {
        $segments = explode(DIRECTORY_SEPARATOR, $path);
        array_pop($segments);
        $folder = implode(DIRECTORY_SEPARATOR, $segments);

        if (!$this->fileSystem->exists($folder)) {
            $this->fileSystem->makeDirectory($folder, 0755, true);
        }
    }
}
