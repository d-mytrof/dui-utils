<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

class DuiAccessRule extends \yii\filters\AccessRule
{

    protected function matchRole($user)
    {
        if (empty($this->roles)) {
            return true;
        }
        foreach ($this->roles as $role) {
            if ($role === '?') {
                if ($user->getIsGuest()) {
                    return true;
                }
            } elseif ($role === '@') {
                if (!$user->getIsGuest()) {
                    return true;
                }
                //check if the user is logged in and the roles match
            } elseif (!$user->getIsGuest() && $role === $user->identity->role_id) {
                return true;
            }
        }

        return false;
    }
}
