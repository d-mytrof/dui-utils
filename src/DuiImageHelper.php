<?php
/**
 * @copyright Copyright &copy; Dmytro Mytrofanov, 2014 - 2023
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;
use yii\imagine\Image;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Box;
use Imagine\Image\Point;

class DuiImageHelper
{
    public const EMPTY_IMAGE_SIZE_MEDIUM = 'medium';

    /**
     * @param string $filePath
     * @param int $coordX1
     * @param int $coordY1
     * @param int $coordX2
     * @param int $coordY2
     * @param string $alias
     * @return mixed
     */
    public static function resizeCropImage(
        string $filePath,
        int $coordX1,
        int $coordY1,
        int $coordX2,
        int $coordY2,
        string $alias
    ): mixed {
        $image = Image::getImagine()->open($filePath);
        $koef = $image->getSize()->getWidth() / Yii::$app->params[$alias . 'PreviewWidth'];
        $coordX1 = floor($coordX1 * $koef);
        $coordY1 = floor($coordY1 * $koef);
        $coordX2 = floor($coordX2 * $koef);
        $coordY2 = floor($coordY2 * $koef);
        $thumbWidth = $image->getSize()->getWidth();
        $thumbHeight = floor($image->getSize()->getHeight() / ($image->getSize()->getWidth() / $thumbWidth));
        if ($coordX1 == 0 && $coordY1 == 0 && $coordX2 == 0 && $coordY2 == 0) {
            $coordX2 = $thumbWidth;
            $coordY2 = $thumbHeight;
        }

        if ($coordX2 > $thumbWidth) {
            $coordX2 = $thumbWidth;
        }
        if ($coordY2 > $thumbHeight) {
            $coordY2 = $thumbHeight;
        }

        //create thumbnail with real image width and calculated height
        $image = $image->thumbnail(new \Imagine\Image\Box($thumbWidth, $thumbHeight), ManipulatorInterface::THUMBNAIL_OUTBOUND);

        //crop image
        if (($coordX1 < $coordX2) && ($coordX1 <= $thumbWidth) && ($coordX2 <= $thumbWidth) &&
                ($coordY1 < $coordY2) && ($coordY1 <= $thumbHeight) && ($coordY2 <= $thumbHeight)) {
            $cropFrameWidth = $coordX2 - $coordX1;
            $cropFrameHeight = $coordY2 - $coordY1;
            $image = $image->crop(new \Imagine\Image\Point($coordX1, $coordY1), new \Imagine\Image\Box($cropFrameWidth, $cropFrameHeight));

            //resize to image size
            return $image->thumbnail(
                new \Imagine\Image\Box(
                    Yii::$app->params[$alias . 'Width'],
                    Yii::$app->params[$alias . 'Height']
                ),
                ManipulatorInterface::THUMBNAIL_FLAG_UPSCALE
            );
        }
        return null;
    }

    /**
     * @param string $value
     * @param string $color
     * @param string $size
     * @return string
     */
    public static function emptyImage(
        string $value,
        string $color = 'dui-color-info',
        string $size = self::EMPTY_IMAGE_SIZE_MEDIUM
    ): string {
        if (empty($value)) {
            return '<span class="' . $color . ' fas fa-3x fa-image"></span>';
        }
        return '';
    }

    /**
     * @param int $value
     * @param bool $onlyActive
     * @param string $activeClass
     * @param string $inactiveClass
     * @param int $maxCount
     * @return string
     */
    public static function getStars(
        int $value,
        bool $onlyActive = true,
        string $activeClass = 'dui-active-color',
        string $inactiveClass = 'dui-inactive-color',
        int $maxCount = 5
    ): string {
        $res = '';
        for ($i = 1; $i <= $value; $i++) {
            $res .= '<span class="fas fa-star ' . $activeClass . '"></span>';
        }
        if (!$onlyActive && $value < $maxCount) {
            for ($i = $maxCount - $value; $i <= $maxCount; $i++) {
                $res .= '<span class="fas fa-star-o ' . $inactiveClass . '"></span>';
            }
        }
        return $res;
    }

    /**
     * @param string $filePath
     * @param int $width
     * @param int $height
     * @return mixed
     */
    public static function cropImage(
        string $filePath,
        int $width,
        int $height
    ): mixed {
        $image = Image::getImagine()->open($filePath);
        $thumbnail = Image::thumbnail($filePath, $width, $height);
        $size = $thumbnail->getSize();
        if ($size->getWidth() < $width or $size->getHeight() < $height) {
            $white = Image::getImagine()->create(new Box($width, $height));
            $thumbnail = $white->paste(
                $thumbnail,
                new Point(ceil($width / 2 - $size->getWidth() / 2), ceil($height / 2 - $size->getHeight() / 2))
            );
        }
        return $thumbnail;
    }
}
