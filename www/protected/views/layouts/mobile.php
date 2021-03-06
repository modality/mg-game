<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="content-language" content="en" />
  <meta id="Viewport" name="viewport" content="width=device-width, initial-scale=.5, maximum-scale=1, user-scalable=yes" />
  <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />
  <!-- blueprint CSS framework -->
  <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/normalize.css" media="screen, projection" />
  <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/mmenu.css" />
  <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/mmenu-positioning.css" />
  <title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>
<div id="ppitest" style="width:1in;height:0px;padding:0px"></div>
<?php echo $content; ?>

</body>
</html>