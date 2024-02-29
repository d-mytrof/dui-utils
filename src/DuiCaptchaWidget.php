<?php

/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */
/*
  public function actionIcaptcha() {
  $captcha = new DuiCaptchaWidget();
  if (!$captcha->renderCaptchaImage(Yii::$app->request->get('hash'))) {
    $captcha->renderEmpty();
  }


  $captchaHash = Yii::$app->request->get('captcha_hash');
  $captchaValue = Yii::$app->request->get('captcha_value');
  if( $captcha->valid($captchaHash, $captchaValue) ){
  //..save
  }
 */

namespace dmytrof\utils;

use Yii;
use yii\base\Exception;
use yii\base\Widget;

use function mb_strlen;

class DuiCaptchaWidget extends Widget
{
    public const TYPE_NUMBERS_ALPHABET = 0;
    public const TYPE_NUMBERS_ONLY = 1;
    public const TYPE_ALPHABET_ONLY = 2;
    public const VIEW_TYPE_PIN = 'pin';

    public $actionURL = '';
    public $formAlias = null;
    public $captchaType = 0;
    public $width = 120;
    public $height = 40;
    public $textLength = 6;
    public $fontSize = 22;
    public $fontPath;
    //public $text = null;
    public $text = null;
    //init colors
    public $backgroundColor = [255, 255, 255];
    public $noiseRectangleColor = [165, 201, 86];
    public $noiseEllipseColor = [205, 235, 142];
    public $textShadowColor = [200, 200, 200];
    public $textColor = [150, 150, 150];
    public $borderColor = [220, 220, 220];

    public function init()
    {
        parent::init();
        if (!extension_loaded('gd')) {
            throw new Exception('GD is not installed!');
        }
    }

    public function run()
    {
        //render random string
        $this->text = $this->renderString($this->textLength);

        $hiddenHashName = $this->formAlias ? $this->formAlias . '[captcha_hash]' : 'captcha_hash';
        echo '<input type="hidden" name="' . $hiddenHashName . '" value="' .
        Yii::$app->appSecurity->encodeCaptchaString($this->text) . '">';
        echo '<img src="' . $this->actionURL .
        '&h=' . urlencode(Yii::$app->appSecurity->encodeCaptchaString($this->text)) .
        '&rid=' . urlencode(Yii::$app->appSecurity->getKey('RIDPublicKey')) . '|' .
        urlencode(Yii::$app->appSecurity->encodeRID()) .
        '">';
    }

    /**
     * @param string $viewType
     * @return array
     */
    public function renderCaptchaData(string $viewType = null): array
    {
        $type = null;
        $text = $this->renderString($this->textLength);
        if ($viewType == self::VIEW_TYPE_PIN) {
            $type = self::VIEW_TYPE_PIN;
            $this->captchaType = self::TYPE_NUMBERS_ONLY;
            $text = $this->renderString();
        }

        $captcha = new DuiCaptchaWidget();
        $captcha->fontPath = $this->fontPath;
        return [
            'h' => Yii::$app->appSecurity->encodeCaptchaString($text),
            'image' => $captcha->renderCaptchaImageData($type),
        ];
    }

    /**
     * render captcha image
     * @param string $type
     */
    public function renderCaptchaImage(string $type = null)
    {
        //set the content-type
        header('Content-Type: image/png');

        if (!$this->text) {
            $this->captchaType = self::TYPE_NUMBERS_ONLY;
            $this->text = $this->renderString($this->textLength);
        }

        switch ($type) {
            case self::VIEW_TYPE_PIN:
                $this->renderPin();
                break;
            default:
                $this->renderCaptcha();
                break;
        }
    }

    /**
     * render captcha image data
     * @param string $type
     */
    public function renderCaptchaImageData(string $type = null)
    {
        if (!$this->text) {
            $this->captchaType = self::TYPE_NUMBERS_ONLY;
            $this->text = $this->renderString($this->textLength);
        }

        switch ($type) {
            case self::VIEW_TYPE_PIN:
                ob_start();
                $this->renderPin();
                $image = ob_get_contents();
                ob_end_clean();
                return base64_encode($image);
                break;
            default:
                ob_start();
                $this->renderCaptcha();
                $image = ob_get_contents();
                ob_end_clean();
                return base64_encode($image);
                break;
        }

        return null;
    }

    public function renderPin()
    {
        //create image
        $image = imagecreatetruecolor($this->width, $this->height);

        //init colors
        $backgroundColor = imagecolorallocate($image, $this->backgroundColor[0], $this->backgroundColor[1], $this->backgroundColor[2]);
        $textColor = imagecolorallocate($image, $this->textColor[0], $this->textColor[1], $this->textColor[2]);

        imagefilledrectangle($image, 0, 0, $this->width, $this->height, $backgroundColor);

        //add text
        imagettftext($image, $this->fontSize, 0, 0, 25, $textColor, $this->fontPath, $this->text);

        imagejpeg($image);
        imagedestroy($image);
    }

    public function renderCaptcha()
    {
        //create image
        $image = imagecreatetruecolor($this->width, $this->height);

        //init colors
        $backgroundColor = imagecolorallocate(
            $image,
            $this->backgroundColor[0],
            $this->backgroundColor[1],
            $this->backgroundColor[2]
        );
        $noiseRectangleColor = imagecolorallocate(
            $image,
            $this->noiseRectangleColor[0],
            $this->noiseRectangleColor[1],
            $this->noiseRectangleColor[2]
        );
        $noiseEllipseColor = imagecolorallocate(
            $image,
            $this->noiseEllipseColor[0],
            $this->noiseEllipseColor[1],
            $this->noiseEllipseColor[2]
        );
        $textShadowColor = imagecolorallocate(
            $image,
            $this->textShadowColor[0],
            $this->textShadowColor[1],
            $this->textShadowColor[2]
        );
        $textColor = imagecolorallocate(
            $image,
            $this->textColor[0],
            $this->textColor[1],
            $this->textColor[2]
        );
        $borderColor = imagecolorallocate(
            $image,
            $this->borderColor[0],
            $this->borderColor[1],
            $this->borderColor[2]
        );

        imagefilledrectangle($image, 0, 0, $this->width, $this->height, $backgroundColor);

        //add rectangle noise
        for ($i = 0; $i < 4; $i++) {
            $cx = rand(0, $this->width);
            $cy = (int) rand(0, $this->width / 5);
            $h = $cy + (int) rand(0, $this->height / 10);
            $w = $cx + (int) rand($this->width / 3, $this->width);
            imagefilledrectangle($image, $cx, $cy, $w, $h, $noiseRectangleColor);
        }

        //add ellipse noise
        for ($i = 0; $i < 20; $i++) {
            $cx = (int) rand(-1 * ($this->width / 2), $this->width + ($this->width / 2));
            $cy = (int) rand(-1 * ($this->height / 2), $this->height + ($this->height / 2));
            $h = (int) rand($this->height / 2, 2 * $this->height);
            $w = (int) rand($this->width / 2, 2 * $this->width);
            imageellipse($image, $cx, $cy, $w, $h, $noiseEllipseColor);
        }

        //add shadow
        imagettftext($image, $this->fontSize, -8, 7, 26, $textShadowColor, $this->fontPath, $this->text);

        //add text
        imagettftext($image, $this->fontSize, -5, 5, 25, $textColor, $this->fontPath, $this->text);

        //draw border
        $this->drawBorder($image, $borderColor);

        imagejpeg($image);
        imagedestroy($image);
    }

    /**
     * draw border
     * @param type $image
     * @param type $color
     * @param type $thickness
     */
    public function drawBorder(&$image, &$color, $thickness = 1)
    {
        $x1 = 0;
        $y1 = 0;
        $x2 = ImageSX($image) - 1;
        $y2 = ImageSY($image) - 1;
        for ($i = 0; $i < $thickness; $i++) {
            ImageRectangle($image, $x1++, $y1++, $x2--, $y2--, $color);
        }
    }

    /**
     * render random string
     * @param type $textLength
     * @return string
     */
    public function renderString($textLength = 4)
    {
        switch ($this->captchaType) {
            case DuiCaptchaWidget::TYPE_NUMBERS_ONLY:
                $characters = '0123456789';
                break;
            case DuiCaptchaWidget::TYPE_ALPHABET_ONLY:
                $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            default:
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }

        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $textLength; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * check values
     * @param type $encoded
     * @param type $checkText
     * @return boolean
     */
    public function valid($encoded, $checkText)
    {
        try {
            $words = explode('|', Yii::$app->appSecurity->decodeCaptchaString($encoded));

            if (!isset($words[0]) ||
                    !isset($words[1]) ||
                    !isset($words[2]) ||
                    !isset($words[3]) ||
                    mb_strlen($words[0], 'UTF-8') == 0 ||
                    $words[0] != $checkText ||
                    $words[1] != Yii::$app->id ||
                    $words[2] != Yii::$app->appSecurity->captchaHashData) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * check captcha lifetime
     * @param type $encoded
     * @return boolean
     */
    public function isAlive($encoded)
    {
        $words = explode('|', Yii::$app->appSecurity->decodeCaptchaString($encoded));
        if (!isset($words[0]) ||
                !isset($words[1]) ||
                !isset($words[2]) ||
                !isset($words[3]) ||
                round(abs($words[3] - time()) / 60) > Yii::$app->appSecurity->captchaLifetime) {
            return false;
        }
        return true;
    }

    /**
     * render empty image 1x1px
     */
    public function renderEmpty()
    {
        header('Content-Type: image/png');
        echo base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej'.
            '3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII='
        );
    }
}
