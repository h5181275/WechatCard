<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Session;
use app\models\Product;
use app\models\ApiInterface;
use app\models\Wechat;

/**
 * MembersController implements the CRUD actions for Members model.
 */
class InterfaceController extends Controller
{
    private $_session = null;

    public function init()
    {
        $this->_session = new Session;
        $this->_session->open();

    }

    public function actionIndex()
    {
       return $this->redirect(yii::$aliases['@web'].'/site');
    }

    public function actionGetwxconfig()
    {
        $model = new ApiInterface();
        return $model->getConfig();
    }

    public function actionIslogin()
    {
        $model = new ApiInterface();
        return json_encode($model->islogin(Yii::$app->request->post()));
    }

    public function actionCallback()
    {
        $model = new Wechat();
        $rs = $model->callback(Yii::$app->request->get());
       // print_r($rs);die;
        return $this->redirect($rs['msg']);
        //return json_encode($model->callback(Yii::$app->request->get()));
    }

    public function actionSavemyvicoe()
    {
        $model = new ApiInterface();
        return json_encode($model->saveMyVicoe(Yii::$app->request->post()));        
    }

    public function actionSavefriendvicoe()
    {
        $model = new ApiInterface();
        return json_encode($model->saveFriendVicoe(Yii::$app->request->post()));
    }

    public function actionGetmydb()
    {
        $model = new ApiInterface();
        return json_encode($model->getMyDb(Yii::$app->request->post()));
    }

    public function actionGetcode()
    {
        $model = new ApiInterface();
        return json_encode($model->getCode(Yii::$app->request->post()));
    }

    public function actionGetmycode()
    {
        $model = new ApiInterface();
        return json_encode($model->getMyCode(Yii::$app->request->post()));
    }

    public function actionSavemsg()
    {
        $model = new ApiInterface();
        return json_encode($model->saveMsg(Yii::$app->request->post()));
    }

    //朋友点击链接进来获取信息
    public function actionGetuserinfo()
    {
        $model = new ApiInterface();
        return json_encode($model->getUserInfo(Yii::$app->request->post()));
    }

    //判断超过50万次
    public function actionGetcountbyday()
    {
        $model = new ApiInterface();
        return json_encode($model->getCountByDay(Yii::$app->request->post()));
    }

    //保存到卡包
    public function actionSavewxcard()
    {
        $model = new ApiInterface();
        return json_encode($model->saveWxCard(Yii::$app->request->post()));
    }

    public function actionAwardstate()
    {
        $model = new ApiInterface();
        return json_encode($model->awardstate(Yii::$app->request->post()));
    }

    public function actionGetusermsg()
    {
        $model = new ApiInterface();
        return json_encode($model->getusermsg(Yii::$app->request->post()));
    }

    public function actionSetsession()
    {        
        $this->_session['utype'] = 'wechat';
        $this->_session['uid'] = $_GET['id'];
    }

    public function actionSaveshare()
    {
        $model = new ApiInterface();
        return json_encode($model->saveShare(Yii::$app->request->post()));
    }    
}
