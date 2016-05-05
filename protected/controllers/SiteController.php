<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Session;
use app\models\WechatAuth;
use app\models\WxCard;

class SiteController extends Controller
{
    public function actionIndex()
    {      
    	// if(Yii::$app->request->userIP!='180.168.26.106')
    	// 	return $this->redirect('http://www.4008123123.com/');
        // if(date('Ymd')>=20150413){
        //     $this->layout='end';
        // }

        
        $model = new \app\models\Wechat();
        $model->getShopList();
        die();
        return $this->render('index');
    }

    public function actionAuth()
	{
		$model = new \app\models\Wechat();
		$model->saveWechatAuth(file_get_contents("php://input"));
		return 'success';
	}

    public function actionError()
    {

        //return $this->redirect(yii::$aliases['@web'].'/site');
    }

    public function actionLogin()
    {
        $wechat = new \app\models\Wechat;
        $rs = $wechat->oauth('state');
        print_r($rs);die;
    }

    public function actionCallback()
    {
        $wechat = new \app\models\Wechat;
        $rs = $wechat->callback($_GET);
        print_r($rs);die;
    }

    public function actionTest()
    {
        $WxCard = new \app\models\Wechat;
        $WxCard->createCard();  
    }

    public function actionTestlist()
    {
        $wechat = new \app\models\Wechat;
        $rs = $wechat->setTestList($_GET);
        print_r($rs);die;
    }

    public function actionQueyauth()
    {
        $WxCard = new \app\models\Wechat;
        return $WxCard->queyAuth();
       
    }

    public function actionCallback1()
    {
        $WxCard = new \app\models\Wechat;
        return $WxCard->callBack1($_GET);
    }

    public function actionUsed()
    {
        $WxCard = new \app\models\Wechat;
        return $WxCard->decryptCode($_GET);
    }

    public function actionGethtml()
    {
        $WxCard = new \app\models\Wechat;
        return $WxCard->getHtml();
    }

    public function actionUploadnews()
    {
        $WxCard = new \app\models\Wechat;
        return $WxCard->uploadnews();
    }

    public function actionSendnews()
    {
        $WxCard = new \app\models\Wechat;
        return $WxCard->sendNews();
    }
}
