<?php

namespace App\Libraries;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Product;
use InvalidArgumentException;
use App\Enums\CurrencyPosition;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\File;

class AppLibrary
{
    public static function date($date, $pattern = null): string
    {
        if (!$pattern) {
            $pattern = config('app.date_format');
        }
        return Carbon::parse($date)->format($pattern);
    }

    public static function time($time, $pattern = null): string
    {
        if (!$pattern) {
            $pattern = config('app.time_format');
        }
        return Carbon::parse($time)->format($pattern);
    }

    public static function datetime($dateTime, $pattern = null): string
    {
        if (!$pattern) {
            $pattern = config('app.time_format') . ', ' . config('app.date_format');
        }
        return Carbon::parse($dateTime)->format($pattern);
    }

    public static function increaseDate($dateTime, $days, $pattern = null): string
    {
        if (!$pattern) {
            $pattern = config('app.date_format');
        }
        return Carbon::parse($dateTime)->addDays($days)->format($pattern);
    }

    public static function deliveryTime($dateTime, $pattern = null): string
    {
        if (!$pattern) {
            $pattern = config('app.time_format');
        }
        $explode = explode('-', $dateTime);
        if (count($explode) == 2) {
            return Carbon::parse(trim($explode[0]))->format($pattern) . ' - ' . Carbon::parse(
                trim($explode[1])
            )->format($pattern);
        }
        return '';
    }

    public static function associativeToNumericArrayBuilder($array): array
    {
        $i = 1;
        $buildArray = [];
        if (count($array)) {
            foreach ($array as $arr) {
                if (isset($arr['children'])) {
                    $children = $arr['children'];
                    unset($arr['children']);

                    $arr['parent'] = 0;
                    $buildArray[$i] = $arr;
                    $parentId = $i;
                    $i++;
                    foreach ($children as $child) {
                        $child['parent'] = $parentId;
                        $buildArray[$i] = $child;
                        $i++;
                    }
                } else {
                    $arr['parent'] = 0;
                    $buildArray[$i] = $arr;
                    $i++;
                }
            }
        }
        return $buildArray;
    }

    public static function numericToAssociativeArrayBuilder($array): array
    {
        $i = 0;
        $parentId = null;
        $parentIncrementId = null;
        $buildArray = [];
        if (count($array)) {
            foreach ($array as $arr) {
                if (!$arr['parent']) {
                    $parentId = $arr['id'];
                    $parentIncrementId = $i;
                    $buildArray[$i] = $arr;
                    $i++;
                }

                if ($arr['parent'] == $parentId) {
                    $buildArray[$parentIncrementId]['children'][] = $arr;
                }
            }
        }
        if ($buildArray) {
            foreach ($buildArray as $key => $build) {
                if ($build['url'] == "#" && !isset($build['children'])) {
                    unset($buildArray[$key]);
                }
            }
        }

        return $buildArray;
    }

    public static function permissionWithAccess(&$permissions, $rolePermissions): object
    {
        if ($permissions) {
            foreach ($permissions as $permission) {
                if (isset($rolePermissions[$permission->id])) {
                    $permission->access = true;
                } else {
                    $permission->access = false;
                }
            }
        }
        return $permissions;
    }

    public static function menu(&$menus, $permissions): array
    {
        if ($menus && $permissions) {
            foreach ($menus as $key => $menu) {
                if (isset($permissions[$menu['url']]) && !$permissions[$menu['url']]['access']) {
                    if ($menu['url'] != '#') {
                        unset($menus[$key]);
                    }
                }
            }
        }
        return $menus;
    }

    public static function pluck($array, $value, $key = null, $type = 'object'): array
    {
        $returnArray = [];
        if ($array) {
            foreach ($array as $item) {
                if ($key != null) {
                    if ($type == 'array') {
                        $returnArray[$item[$key]] = strtolower($value) == 'obj' ? $item : $item[$value];
                    } else {
                        $returnArray[$item[$key]] = strtolower($value) == 'obj' ? $item : $item->$value;
                    }
                } elseif ($value == 'obj') {
                    $returnArray[] = $item;
                } elseif ($type == 'array') {
                    $returnArray[] = $item[$value];
                } else {
                    $returnArray[] = $item->$value;
                }
            }
        }
        return $returnArray;
    }

    public static function username($name)
    {
        if ($name) {
            $username = strtolower(str_replace(' ', '', $name)) . rand(1, 999999);
            if (User::where(['username' => $username])->first()) {
                self::username($name);
            }
            return $username;
        }
    }

    public static function name($firstName, $lastName): string
    {
        return $firstName . ' ' . $lastName;
    }

    public static function amountCheck($amount, $attr = 'price'): object
    {
        $response = [
            'status'  => true,
            'message' => ''
        ];

        if (!is_numeric($amount)) {
            $response['status'] = false;
            $response['message'] = "This {$attr} must be integer.";
        }

        if ($amount <= 0) {
            if (!$response['status']) {
                return (object)$response;
            } else {
                $response['status'] = false;
                $response['message'] = "This {$attr} negative amount not allow.";
            }
        }

        $replaceValue = str_replace('.', '', $amount);
        if (strlen($replaceValue) > 12) {
            if (!$response['status']) {
                return (object)$response;
            } else {
                $response['status'] = false;
                $response['message'] = "This {$attr} length can't be greater than 12 digit.";
            }
        }

        return (object)$response;
    }

    public static function currencyAmountFormat($amount): string
    {
        // Round the amount to the nearest whole number for a cleaner look
        $roundedAmount = round($amount);
        $decimalPoint  = 0; // Force 0 decimals for rounding

        if (config('app.currency_position') == CurrencyPosition::LEFT) {
            return config('app.currency_symbol') . ' ' . number_format($roundedAmount, $decimalPoint, '.', '');
        }
        return number_format($roundedAmount, $decimalPoint, '.', '') . ' ' . config('app.currency_symbol');
    }

    public static function flatAmountFormat($amount): string
    {
        return number_format($amount, config('app.currency_decimal_point'), '.', '');
    }

    public static function convertAmountFormat($amount): float
    {
        return (float)number_format($amount, config('app.currency_decimal_point'), '.', '');
    }

    public static function fcmDataBind($request): void
    {
        $cdn = public_path("firebase-cdn.txt");
        $textContent = public_path("firebase-content.txt");
        $file = public_path("firebase-messaging-sw.js");
        
        // Strict sanitization: Ensure values are safely escaped for JS
        $config = [
            'apiKey'            => (string) $request->notification_fcm_api_key,
            'authDomain'        => (string) $request->notification_fcm_auth_domain,
            'projectId'         => (string) $request->notification_fcm_project_id,
            'storageBucket'     => (string) $request->notification_fcm_storage_bucket,
            'messagingSenderId' => (string) $request->notification_fcm_messaging_sender_id,
            'appId'             => (string) $request->notification_fcm_app_id,
            'measurementId'     => (string) $request->notification_fcm_measurement_id,
        ];

        $content = "let config = " . json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . ";\n";
        
        if (File::exists($cdn) && File::exists($textContent)) {
            File::put($file, File::get($cdn) . $content . File::get($textContent));
        }
    }

    public static function defaultPermission($permissions)
    {
        $defaultPermission = (object)[];
        if (count($permissions)) {
            foreach ($permissions as $permission) {
                if ($permission->access) {
                    $defaultPermission = $permission;
                    break;
                }
            }
        }
        return $defaultPermission;
    }

    public static function domain($input): array|string|null
    {
        $input = trim($input, '/');
        if (!preg_match('#^http(s)?://#', $input)) {
            $input = 'http://' . $input;
        }
        $urlParts = parse_url($input);

        $link = '';
        if (isset($urlParts['port'])) {
            $link .= ':' . $urlParts['port'];
        }

        if (isset($urlParts['path'])) {
            $link .= $urlParts['path'];
        }

        return preg_replace('/^www\./', '', ($urlParts['host'] . $link));
    }

    public static function licenseApiResponse($response)
    {
        $header = explode(';', $response->getHeader('Content-Type')[0]);
        $contentType = $header[0];
        if ($contentType == 'application/json') {
            $contents = $response->getBody()->getContents();
            $data = json_decode($contents);
            if (json_last_error() == JSON_ERROR_NONE) {
                return $data;
            }
            return $contents;
        }

        return ['status' => false, 'message' => 'data not found'];
    }


    public static function deleteDir($dirPath): void
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (!str_ends_with($dirPath, '/')) {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    public static function sku($sku)
    {
        $productVariation = ProductVariation::where(['sku' => $sku])->first();
        $product = Product::where(['sku' => $sku])->first();
        if ($productVariation || $product) {
            self::sku(rand(1, 99999999999));
        }
        return $sku;
    }

    public static function recursive($elements, $parentId = 0): array
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = self::recursive($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return $branch;
    }

    public static function tagString($arrays): string
    {
        $string = '';
        $i = 1;
        $count = count($arrays);
        if (count($arrays) > 0) {
            foreach ($arrays as $array) {
                if ($i == $count) {
                    $string .= $array->name;
                } else {
                    $string .= $array->name . ', ';
                }
                $i++;
            }
        }
        return $string;
    }

    public static function taxString($arrays): string
    {
        $string = '';
        $i = 1;
        $count = count($arrays);
        if (count($arrays) > 0) {
            foreach ($arrays as $array) {
                if ($i == $count) {
                    $string .= $array?->tax?->name;
                } else {
                    $string .= $array?->tax?->name . ', ';
                }
                $i++;
            }
        }
        return $string;
    }

    public static function lowerWithReplaceToSpace($string): string
    {
        return strtolower(str_replace($string, '', ' '));
    }
}
