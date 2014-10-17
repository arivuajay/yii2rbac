<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model {

    public $username;
    public $password;
    public $rememberMe = true;
    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params) {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login() {
        if ($this->validate()) {
            $this->getUser();
            return $this->autoLogin();
        }

        return false;
    }

    public function autoLogin($user = null) {
        if($user !== null)
            $this->_user = $user;
        
        if (Yii::$app->user->login($this->_user, $this->rememberMe ? 3600 * 24 * 30 : 0)) {
            \Yii::$app->session->set('user.role', $this->_user->role);
            \Yii::$app->session->set('user.ctrler', $this->roleController());
            return true;
        }
        
        return false;
    }

    public function roleController() {
        $uRole = \Yii::$app->session->get('user.role');

        switch ($uRole) {
            case User::ROLE_SYS_ADMIN:
                $returnUrl = 'sysadmin';
                break;
            case User::ROLE_SUPER_ADMIN:
                $returnUrl = 'superadmin';
                break;
            case User::ROLE_COMPANY_ADMIN:
                $returnUrl = 'companyadmin';
                break;
            case User::ROLE_MANAGER:
                $returnUrl = 'manager';
                break;
            case User::ROLE_EMPLOYEE:
                $returnUrl = 'employee';
                break;
            default :
                $returnUrl = 'site';
                break;
        }

        return $returnUrl;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser() {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

}
