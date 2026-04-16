<?php

namespace App\Services;


use App\Http\Requests\LanguageFileTextGetRequest;
use App\Libraries\AppLibrary;
use Exception;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\LanguageRequest;
use App\Http\Requests\PaginateRequest;
use Smartisan\Settings\Facades\Settings;


class LanguageService
{

    protected $languageFilter = [
        'name',
        'code',
        'status',
    ];
    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return Language::where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->languageFilter)) {
                        $query->where($key, 'like', '%' . $request . '%');
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(LanguageRequest $request)
    {
        try {
            if (!file_exists(base_path("resources/js/languages/{$request->code}.json"))) {
                copy(base_path("resources/js/languages/en.json"), base_path("resources/js/languages/{$request->code}.json"));
            }

            if (!file_exists(base_path("lang/{$request->code}"))) {
                mkdir(base_path("lang/{$request->code}"), 0755);
                $files = scandir(base_path("lang/en"));
                if (count($files) > 2) {
                    foreach ($files as $file) {
                        if ($file != '.' && $file != '..') {
                            copy(base_path("lang/en/{$file}"), base_path("lang/{$request->code}/{$file}"));
                        }
                    }
                }
            }

            $language = Language::create($request->validated());
            if ($request->image) {
                $language->addMediaFromRequest('image')->toMediaCollection('language');
            }

            return $language;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(LanguageRequest $request, Language $language): Language
    {
        try {
            $language->update($request->validated());
            if ($request->image) {
                $language->clearMediaCollection('language');
                $language->addMediaFromRequest('image')->toMediaCollection('language');
            }
            return $language;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Language $language): void
    {
        try {
            if (Settings::group('site')->get("site_default_language") != $language->id) {
                if (!env('DEMO')) {
                    AppLibrary::deleteDir(base_path("lang/{$language->code}"));
                    if (file_exists(base_path("resources/js/languages/{$language->code}.json"))) {
                        unlink(base_path("resources/js/languages/{$language->code}.json"));
                    }
                }
                $language->delete();
            } else {
                throw new Exception("Default language not deletable", 422);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Language $language): Language
    {
        try {
            return $language;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }


    /**
     * @throws Exception
     */
    /**
     * @throws Exception
     */
    public function fileList(Language $language)
    {
        try {
            $i = 0;
            $array = [];

            $jsonPath = base_path("resources/js/languages/{$language->code}.json");
            if (file_exists($jsonPath)) {
                $array[$i] = (object)[
                    'path' => $jsonPath,
                    'name' => "{$language->code}.json"
                ];
                $i++;
            }

            $langDir = base_path("lang/{$language->code}");
            if (file_exists($langDir)) {
                $files = scandir($langDir);
                if (count($files) > 2) {
                    foreach ($files as $file) {
                        if ($file != '.' && $file != '..') {
                            $array[$i] = (object)[
                                'path' => $langDir . DIRECTORY_SEPARATOR . $file,
                                'name' => $file
                            ];
                            $i++;
                        }
                    }
                }
            }
            return collect($array);
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }


    public function fileText(LanguageFileTextGetRequest $request)
    {
        $path = $this->validatePath($request->path);

        if (file_exists($path)) {
            $explodeName = explode('.', $request->name);
            if (count($explodeName) > 1) {
                if ($explodeName[1] == 'json') {
                    // JSON files are loaded via JS, but if backend needs content:
                    return json_decode(file_get_contents($path), true);
                } else {
                    return include($path);
                }
            }
        }
        return [];
    }

    /**
     * @throws Exception
     */
    public function fileTextStore(Request $request): void
    {
        try {
            $path = $this->validatePath($request->x_language_file_path);
            
            if (!file_exists($path)) {
                throw new Exception("Translation file not found at validated path.", 404);
            }

            $fileContent = file_get_contents($path);
            foreach ($request->all() as $key => $value) {
                if ($key != 'x_language_file_path' && $key != 'x_language_file_name' && $key != '_token') {
                    $key = str_replace('_', ' ', $key);
                    // Match both 'key' and "key" formats in PHP lang files or JSON
                    if (strpos($fileContent, "'" . $key . "'") !== false) {
                        $fileContent = str_replace("'" . $key . "'", "\"{$value}\"", $fileContent);
                    } elseif (strpos($fileContent, "\"{$key}\"") !== false) {
                        $fileContent = str_replace("\"{$key}\"", "\"{$value}\"", $fileContent);
                    }
                }
            }

            // Use LOCK_EX to prevent corruption during concurrent edits
            file_put_contents($path, $fileContent, LOCK_EX);
            
            \App\Models\AdminNotification::record('info', 'Translations Updated', "Language file '{$request->x_language_file_name}' was modified by " . (auth()->user()->name ?? 'Admin'));
        } catch (Exception $exception) {
            Log::info("Language save error: " . $exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * Validates that the requested path is within the allowed translation directories
     * to prevent Path Traversal and Local File Inclusion attacks.
     */
    private function validatePath(string $path): string
    {
        $realPath = realpath($path);
        
        // Allowed roots
        $langRoot = realpath(base_path('lang'));
        $jsRoot   = realpath(base_path('resources/js/languages'));

        if ($realPath && (str_starts_with($realPath, $langRoot) || str_starts_with($realPath, $jsRoot))) {
            return $realPath;
        }

        throw new Exception("Security Alert: Unauthorized path access attempted in Language Service.", 403);
    }
}