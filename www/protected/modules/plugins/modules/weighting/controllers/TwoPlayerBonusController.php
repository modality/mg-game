<?php

class TwoPlayerBonusController extends GxController
{
	public $defaultAction='view';  
    
	public function filters() {
    return array(
      'IPBlock',
      'accessControl', 
     );
  }
  
  public function accessRules() {
    return array(
        array('allow', 
          'actions'=>array('view', 'update'),
          'roles'=>array(ADMIN),
          ),
        array('deny', 
          'users'=>array('*'),
          ),
        );
  }  
  
  public function actionView() {
    $model = $this->loadModel(array("unique_id" => "weighting-TwoPlayerBonusPlugin"), 'TwoPlayerBonus');  
    $model->fbvLoad();
    
    $this->render('view', array(
      'model' => $model,
    ));
  }
  
  public function actionUpdate() {
    $model = $this->loadModel(array("unique_id" => "weighting-TwoPlayerBonusPlugin"), 'TwoPlayerBonus');
    $model->fbvLoad();
    
    $this->performAjaxValidation($model, 'twoplayerbonus-form');
    if (isset($_POST['TwoPlayerBonus'])) {
      $model->setAttributes($_POST['TwoPlayerBonus']);
      
      if ($model->save()) {
        $model->fbvSave();
        MGHelper::log('update', 'Plugin ' . $model->getPluginID() . ' updated');
        Flash::add('success', $model->getPluginID() . ' ' . Yii::t('app', "Updated"));
        $this->redirect(array('view'));
      }
    }
    
    $this->render('update', array(
      'model' => $model,
      ));
  }
}