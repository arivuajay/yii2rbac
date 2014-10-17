<?php

use yii\helpers\Html;
use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var auth\models\UserSearch $searchModel
 */
$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

<?php //echo $this->render('_search', ['model' => $searchModel]);   ?>

    <p>
<?= Html::a('<i class="glyphicon glyphicon-plus-sign"></i> Create User', ['signup'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            //'id',
            'username',
            'email:email',
            //'password_hash',
            //'password_reset_token',
            // 'auth_key',
            [
                'attribute' => 'status',
                'value' => function ($model) {
            return $model->getStatus();
        }
            ],
            [
                'attribute' => 'role',
                'value' => function ($model) {
            return $model->getUserTypes($model->role);
        }
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'php:Y-m-d H:i:s']
            ],
            // 'create_time',
            // 'update_time',
            // 'delete_time',
            ['class' => 'yii\grid\ActionColumn', 'buttons' => ['view' => function ($url, $model, $key) {
                                                    return false;
                                                }]
            ],
        ],
    ]);
    ?>

</div>
