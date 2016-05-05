<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\ApiInterface;

class QrcodeController extends Controller
{

    public function actionIndex()
    {      
    	return $this->redirect(yii::$aliases['@web'].'/site');
    	$model = new ApiInterface();
        $model->scanQrcode();
        return $this->redirect(yii::$aliases['@web'].'/site');
    }
    
}
