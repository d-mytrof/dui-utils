<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;

class DuiUser extends \yii\web\User
{
    public $tokenEntityClassName;
    
    /**
     * Finds user by access token
     *
     * @param string $token
     * @return static|null
     */
    public function findByAccessToken($token)
    {
        $entity = new $this->tokenEntityClassName;
        $model = $entity::find()
            ->where(['=', 'token', $token])
            ->andWhere(['>', 'expired_at', time()])
            ->one();

        return $model ? $model->user : null;
    }

    /**
     * check image file exists
     * @param type $alias
     * @param type $field
     * @return boolean
     */
    public function imageExists($alias, $field, $uid = null)
    {
        if (!empty($field) && is_readable(Yii::$app->user->imageStorageFilePath($alias, $field, $uid))) {
            return true;
        } else {
            false;
        }
    }

    /**
     * delete image
     * @param type $alias
     * @param type $field
     * @return boolean
     */
    public function deleteImage($alias, $field)
    {
        if (!empty($field) && is_writable(Yii::$app->user->imageStorageFilePath($alias, $field))) {
            return unlink(Yii::$app->user->imageStorageFilePath($alias, $field));
        } else {
            false;
        }
    }

    /**
     * Delete file from storage
     * @param string $path
     * @param string $uid
     * @return boolean
     */
    public function deleteImageFromStorage(string $path, string $uid)
    {
        $filePath = Yii::$app->params['imageStoragePath'] . DIRECTORY_SEPARATOR . $uid.
                DIRECTORY_SEPARATOR. $path;
        if (is_writable($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    /**
     * check image file exists by UID
     * @param type $alias
     * @param type $field
     * @return boolean
     */
    public function imageExistsByUID($uid, $alias, $fileName)
    {

        $url = Yii::$app->params['imageStoragePath'] . DIRECTORY_SEPARATOR .
                $uid . ($alias ? '/' . $alias : '') .
                ($fileName ? '/' . $fileName : '');

        if (is_readable($url) && is_file($url)) {
            return true;
        } else {
            false;
        }
    }

    /**
     * Get current user file storage path
     * @return string|null
     */
    public function imageStoragePath(): ?string
    {
        if (!Yii::$app->user->isGuest) {
            $path = Yii::$app->params['imageStoragePath'] .
                    DIRECTORY_SEPARATOR . Yii::$app->user->identity->uid;

            if (is_readable($path)) {
                return $path;
            }
        }
        return null;
    }

    /**
     * Example: image_storage/d/d001/d00100000002/profile/profile.jpg
     * @param string $fileName
     * @param string $uid
     * @return string
     */
    public function imageStorageBaseURL(string $fileName = null, string $uid = null): ?string
    {
        if (!empty($fileName)) {
            return Yii::$app->params['imageStorageHostURL'] . '/' .
                    Yii::$app->params['imageStorageURL'] . '/' . $uid.
                    '/'. $fileName;
        }
        return null;
    }

    /**
     * Create user storage directories
     * @param string $uid
     * @return bool
     */
    public function createDirectories(string $uid): bool
    {
        $userDirectory = Yii::$app->params['imageStoragePath'] . DIRECTORY_SEPARATOR . $uid;

        //create user directory
        if (!file_exists($userDirectory)) {
            $old = umask(0);
            mkdir($userDirectory, DuiFileHelper::permissionsStringToOctal(Yii::$app->params['imageStorageDefaultPermissions']), true);
            umask($old);
        }

        //check directory created
        if (!is_writable($userDirectory)) {
            return false;
        }

        $directoriesCreate = Yii::$app->params['userSignupCreateDirectories'];

        //create other default directories
        for ($i = 0; $i < count($directoriesCreate); $i++) {
            if (!file_exists($userDirectory . DIRECTORY_SEPARATOR . $directoriesCreate[$i])) {
                mkdir($userDirectory . DIRECTORY_SEPARATOR . $directoriesCreate[$i], 0777);
            }

            //check directory created
            if (!is_writable($userDirectory . DIRECTORY_SEPARATOR . $directoriesCreate[$i])) {
                return false;
            }
        }

        return true;
    }

    public function imageEmpty($male = true)
    {
        return $male ? DuiURL::fullURL('images/base/no-image-male.png') :
                DuiURL::fullURL('images/base/no-image-female.png');
    }
}
