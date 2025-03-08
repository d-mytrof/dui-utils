<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

class DuiFileHelper
{
    /**
     * @param string $directoryPath
     * @return void
     */
    public static function clearDirectory(string $directoryPath): void
    {

        //list of name of files inside
        // specified folder
        $files = glob($directoryPath . '/*');

        //deleting all the files in the list
        foreach ($files as $file) {
            if (is_file($file)) {
                //delete the given file
                unlink($file);
            }
        }
    }

    /**
     * @param string $directoryPath
     * @return string|null
     */
    public static function getFirstDirectory(string $directoryPath): ?string
    {
        $arr = explode('/', $directoryPath);
        if (count($arr) > 1) {
            return $arr[0];
        } else {
            $arr = explode('\\', $directoryPath);
            if (count($arr) > 1) {
                return $arr[0];
            }
        }
        return null;
    }

    /**
     * @param string $filePath
     * @return bool
     */
    public static function deleteFile(string $filePath): bool
    {
        if (is_file($filePath) && is_writable($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    /**
     * @param string $filePath
     * @return array
     */
    public static function getCsvData(string $filePath): array
    {
        $result = [];
        if (is_readable($filePath) && ($open = fopen($filePath, 'r')) !== false) {
            while (($data = fgetcsv($open, 5000, ';')) !== false) {
                $result[] = $data;
            }
            fclose($open);
        }
        return $result;
    }
    
    /**
     * @param string $value
     * @return mixed
     */
    public static function permissionsStringToOctal(string $value): int
    {
        return match($value){
            '0400' => 0400,
            '0440' => 0440,
            '0644' => 0644,
            '0660' => 0660,
            '0664' => 0664,
            '0664' => 0664,
            '0666' => 0666,
            '0700' => 0700,
            '0744' => 0744,
            '0775' => 0775,
            '0777' => 0777,
            default => $value,
        };
    }
}
