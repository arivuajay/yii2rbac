<?php

namespace auth\models;

use Yii;

/**
 * This is the model class for table "{{%usertype}}".
 *
 * @property integer $utype_id
 * @property string $utype_name
 *
 * @property User[] $users
 */
class Usertype extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%usertype}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['utype_name'], 'required'],
            [['utype_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'utype_id' => 'User Type',
            'utype_name' => 'Utype Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['usertype' => 'utype_id']);
    }
}
