<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\User;

/**
 * UserSearch represents the model behind the search form about `auth\models\User`.
 */
class UserSearch extends User {

    public function rules() {
        return [
            [['id', 'status'], 'integer'],
            [['username', 'email', 'password_hash', 'password_reset_token', 'auth_key', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function scenarios() {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params) {
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

//        For Below User Role
        $uRole = \Yii::$app->session->get('user.role');
        $query->andFilterWhere(['<', 'role', $uRole]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
                ->andFilterWhere(['like', 'email', $this->email])
                ->andFilterWhere(['like', 'password_hash', $this->password_hash])
                ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
                ->andFilterWhere(['like', 'auth_key', $this->auth_key])
//            ->andFilterWhere(['like', 'last_visit_time', $this->last_visit_time])
                ->andFilterWhere(['like', 'created_at', $this->created_at])
                ->andFilterWhere(['like', 'updated_at', $this->updated_at])
//            ->andFilterWhere(['like', 'delete_time', $this->delete_time])
        ;

        return $dataProvider;
    }

}
