<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\models\User;


/**
 * @var yii\web\View $this
 * @var auth\models\User $model
 * @var yii\widgets\ActiveForm $form
 */

$this->title = 'Update Profile';
$this->params['breadcrumbs'][] ='Profile';
?>
<?php $form = ActiveForm::begin(); ?>
<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title">
			<?= Html::encode($this->title) ?>
		</h3>
	</div>
	<div class="panel-body user-update">
		<?= $form->field($model, 'username')->textInput(['maxlength' => 64]) ?>

		<?= $form->field($model, 'email')->textInput(['maxlength' => 128, 'type' => 'email']) ?>

		<?= $form->field($model, 'password')->passwordInput() ?>

		<?= $form->field($model, 'role')->dropDownList((new User)->getUserTypes(), ['prompt' => '-Choose Role-']); ?>

		<div class="">
		</div>

	</div>
	<div class="panel-footer">
		<?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	</div>
</div>
<?php ActiveForm::end(); ?>
