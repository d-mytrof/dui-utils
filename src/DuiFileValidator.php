<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;
use yii\web\UploadedFile;

class DuiFileValidator extends \yii\validators\Validator
{

    public $skipIfEmpty = false;
    public $types;
    public $maxSize;

    /**
     * Check file mime type, size...
     * @param type $model
     * @param type $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $file = UploadedFile::getInstanceByName($attribute);
        if (!$file) {
            $file = UploadedFile::getInstance($model, $attribute);
        }
        if ($file && !$this->skipIfEmpty) {
            $model->addError($attribute, Yii::t('basic', 'EMPTY_FILE_IS_NOT_ALLOWED'));
            return false;
        }
        if (!$file) {
            return false;
        }
        
        if ($this->maxSize && is_readable($file->tempName)) {
            $realFileSize = filesize($file->tempName);
            $fileSizeMeasure = $this->maxSize;
            $fileSizeMeasure = strtolower(substr($fileSizeMeasure, -1));
            $fileSize = (int)substr($this->maxSize, 0, -1);
            $fileSize = match ($fileSizeMeasure) {
                'g' => $fileSize * 1024 * 1024 * 1024,
                'm' => $fileSize * 1024 * 1024,
                'k' => $fileSize * 1024,
                default => $fileSize,
            };
            if ($realFileSize > $fileSize) {
                $model->addError($attribute, Yii::t('basic', 'MAX_FILE_SIZE_IS_INCORRECT {SIZE}', ['SIZE' => $this->maxSize / (1024 * 1024)]));
            }
        }
        
        if ($this->types && is_readable($file->tempName)) {
            $mime = mime_content_type($file->tempName);
            $mime = strtolower(substr($mime, strpos($mime, '/') + 1));
            $types = array_map('trim', explode(',', $this->types));
            if (array_search($mime, $types) === false) {
                $model->addError($attribute, Yii::t('basic', 'FILE_TYPE_IS_NOT_ALLOWED {TYPES}', ['TYPES' => $this->types]));
            }
        }
    }
}
