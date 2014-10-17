<?php

namespace frontend\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $role
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface {

    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_SUSPENDED = 3;
    
    const ROLE_SYS_ADMIN = 10;
    const ROLE_SUPER_ADMIN = 9;
    const ROLE_COMPANY_ADMIN = 8;
    const ROLE_MANAGER = 7;
    const ROLE_EMPLOYEE = 6;

    private $statuses = [
        self::STATUS_DELETED => 'Deleted',
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_SUSPENDED => 'Suspended',
    ];
    private $roles = [
        self::ROLE_SYS_ADMIN => 'System Administrator',
        self::ROLE_SUPER_ADMIN => 'Super Admin',
        self::ROLE_COMPANY_ADMIN => 'Comapny Admin',
        self::ROLE_MANAGER => 'Manager',
        self::ROLE_EMPLOYEE => 'Employee',
    ];
    
    public $password;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'message' =>  'This email address has already been taken.'],
            ['email', 'exist', 'message' =>  'There is no user with such email.', 'on' => 'requestPasswordResetToken'],
            
            ['password', 'required', 'on' => 'signup'],
            ['password', 'string', 'min' => 6],

            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED,self::STATUS_INACTIVE,self::STATUS_ACTIVE,self::STATUS_SUSPENDED] ],
            
            ['role', 'required'],
            ['role', 'in', 'range' => [self::ROLE_SYS_ADMIN, self::ROLE_SUPER_ADMIN, self::ROLE_COMPANY_ADMIN, self::ROLE_MANAGER,self::ROLE_EMPLOYEE]],
        ];
    }
    
    public function scenarios() {
        return [
            'signup' => ['username', 'email', 'password','role'],
            'profile' => ['username', 'email', 'password','role'],
            'resetPassword' => ['password'],
            'requestPasswordResetToken' => ['email'],
            ] + parent::scenarios();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id) {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username) {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token) {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
                    'password_reset_token' => $token,
                    'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token) {
        if (empty($token)) {
            return false;
        }
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId() {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey() {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password) {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey() {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken() {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken() {
        $this->password_reset_token = null;
    }

    public function getStatus($status = null) {
        if ($status === null) {
            return $this->statuses[$this->status];
        }
        return $this->statuses[$status];
    }

    public function getUserTypes($role = null) {
        if ($role !== null) {
            return $this->roles[$role];
        }
        return $this->roles;
    }
    
    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            if (($this->isNewRecord || in_array($this->getScenario(), ['resetPassword', 'profile'])) && !empty($this->password)) {
                $this->setPassword($this->password);
            }
            if ($this->isNewRecord) {
                $this->generateAuthKey();
            }
            if ($this->getScenario() !== \yii\web\User::EVENT_AFTER_LOGIN) {
                $this->setAttribute('updated_at', new Expression('CURRENT_TIMESTAMP'));
            }

            return true;
        }
        return false;
    }
    
    public function delete() {
        $this->status = self::STATUS_DELETED;
        $this->save(FALSE);
        return true;
    }

}
