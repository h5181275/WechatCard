<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\Session;
use app\components\Common;

class Wechat extends Model
{
	private $_openid  			= null;
	private $_appid   			= 'wx91b0427fc8bb49d3';
	private $_appsecret 		= '0c79e1fa963cd80cc0be99b20a18faeb';
	private $_component_appid	= 'wx686ed4fc66e2e608';
	private $_encodingAesKey 	= "38c655d064da381e3874ec499b55001da75a8b2wefs";
	private $_token 			= "pizzahut";
	private $_timeStamp 		= 0;
	private $_nonce 			= "xxxxxx";
	private $_oauthUrl 			= 'https://open.weixin.qq.com/connect/oauth2/authorize?';
	//private $_redirect			= 'http://wow.kfc.com.cn/interface/callback';
	private $_redirect			= 'http://bucket.kfc.com.cn/interface/callback';
	private $_state				= 'STATE';
	private $_session = null;

	public function init()
	{
		$this->_session = new Session;
        $this->_session->open();

		if($this->_openid===null){
			if(isset($this->_session['openid']))
				$this->_openid = $this->_session['openid'];
		}

		$this->_timeStamp = time();
		$this->_nonce = $this->createNonceStr();

		if(YII_DEBUG){
			$this->_appid 			= 'wx91b0427fc8bb49d3';
			$this->_component_appid = 'wxcafbfc608ddfbe85';
			$this->_appsecret 		= '0c79e1fa963cd80cc0be99b20a18faeb';
			$this->_token   		= 'trioisobar2014';
			$this->_encodingAesKey 	= "38c655d064da381e3874ec499b55001da75a8b2wefs";
			$this->_redirect   		= 'http://wow.kfc.trioisobardev.com/interface/callback';
		}

		
	}

	private function createOauthRul()
	{
		$this->_oauthUrl = $this->_oauthUrl.'appid='.$this->_appid.'&redirect_uri='.urlencode($this->_redirect).'&response_type=code&scope=snsapi_base&state='.$this->_state.'&component_appid='.$this->_component_appid.'#wechat_redirect';  //snsapi_base   snsapi_userinfo
		return true;
	}

	private function createNonceStr($length = 16) 
	{
	    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	    $str = "";
	    for ($i = 0; $i < $length; $i++) {
	      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	    }
	    return $str;
	}

	public function oauth($state)
	{
		$this->_state = urlencode($state);

		$this->createOauthRul();

		if(!$this->_openid){
			return array("code"=>0,"msg"=>$this->_oauthUrl);			
		}

		$rs = WechatAR::find()->where(['openid'=>$this->_openid])->one();

		if(!$rs){
			return ["code"=>0,"msg"=>$this->_oauthUrl];
		}

		$nickname = json_decode($rs->nickname64);
		$this->_session['utype'] = 'wechat';
		$this->_session['uid'] = $rs->id;
		//$this->_session['username'] = $nickname->nickname;
		
		return ["code"=>1,"msg"=>''];
	}

	public function getUrl()
	{
		$url = 'http://wx.ec.api.yum.com.cn/interfaces/wxuser/tr/getFans.do?identity=3A81B4227CFF025AA3A198377F3A849B&openId='.$this->_openid.'&xinId=gh_cb34a9763230';

		$rsF = file_get_contents($url);
		$url = 'http://mp.weixin.qq.com/s?__biz=MzA5MTA5ODAzMA==&mid=206100054&idx=1&sn=9ad62930b2e32f7f9e53f979e712db13#rd';
		if($rsF){
			$rsF = json_decode($rsF,true);
			
			if($rsF['subscribe']==1)
				$url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxc374c56b2c0d78bc&redirect_uri=http://m.4008123123.com/activity.mos/getUserCouponCodeList.do&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
		}

		return $url;
	}

	//回调
	public function callback($data)
	{
		$acessToken = $this->getaAcessToken($data['code']);
		
		if(!isset($acessToken['openid'])){
			return ["code"=>1,"msg"=>$data['state']];
		}

		$rs = WechatAR::find()->where(['openid'=>$acessToken['openid']])->asArray()->one();
		
		if($rs){
			$this->_openid = $this->_session['openid'] = $rs['openid'];
			$this->_session['utype'] = 'wechat';
			$this->_session['uid'] = $rs['id'];
			//$nickname = json_decode($rs['nickname64']);
			//$this->_session['username'] = $nickname->nickname;

			return ["code"=>1,"msg"=>$data['state']];
		}

		//$userInfo = $this->getUserInfo($acessToken['access_token'],$acessToken['openid']);

		//$nickname64 = json_encode(array('nickname'=>$userInfo['nickname']));

		$wechat = new WechatAR();
		$wechat->openid = $acessToken['openid'];
		//$wechat->openid 	= $userInfo['openid'];
		// $wechat->nickname 	= $userInfo['nickname'];
		// $wechat->nickname64 = $nickname64;
		// $wechat->sex 		= $userInfo['sex'];
		// $wechat->country 	= $userInfo['country'];
		// $wechat->province 	= $userInfo['province'];
		// $wechat->city 		= $userInfo['city'];
		// $wechat->headimgurl = $userInfo['headimgurl'];
		$wechat->ip 		= Yii::$app->request->userIp;
		$wechat->insert();

		//$this->_openid = $this->_session['openid'] = $userInfo['openid'];
		$this->_openid = $this->_session['openid'] = $acessToken['openid'];

		$this->_session['utype'] = 'wechat';
		$this->_session['uid'] = $wechat->id;
		//$this->_session['username'] = $userInfo['nickname'];

		return ["code"=>1,"msg"=>$data['state']];
	}

	private function getUserInfo($acessToken,$openid)
	{
		$uri = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$acessToken.'&openid='.$openid.'&lang=zh_CN';

		$rsData = $this->httpRequest($uri);

		return json_decode($rsData,true);
	}


	private function getaAcessToken($code)
	{		
		$api_component_token = $this->getComponentToken();

		$uri = 'https://api.weixin.qq.com/sns/oauth2/component/access_token?appid='.$this->_appid.'&code='.$code.'&grant_type=authorization_code&component_appid='.$this->_component_appid.'&component_access_token='.$api_component_token;

		$rsData = $this->httpRequest($uri);
		
		return json_decode($rsData,true);
	}

	private function getComponentToken()
	{
		//$api_component_token = WechatComponentToken::findBySql("SELECT acesstoken FROM trio_component_token WHERE unixtime+7000>'".(time())."'")->scalar();

		$api_component_token = WechatComponentToken::findOne('1');
		if($api_component_token && $api_component_token->unixtime+7000>time())
			return $api_component_token->acesstoken;

		$data = array();
		$data['component_appid'] = $this->_component_appid;
		$data['component_appsecret'] = $this->_appsecret;
		$data['component_verify_ticket'] = $this->getTicket();

		$url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
		
		$rsData = $this->dataPost($this->decodeUnicode(json_encode($data)),$url);		

		if($api_component_token && isset($rsData['component_access_token'])){			
			$api_component_token->acesstoken = $rsData['component_access_token'];
			$api_component_token->unixtime = time();
			$api_component_token->insert();

			return $rsData['component_access_token'];
		}else if(!$api_component_token){
			$wechatComponentToken = new WechatComponentToken();
			$wechatComponentToken->id = 1;
			$wechatComponentToken->acesstoken = $rsData['component_access_token'];
			$wechatComponentToken->unixtime = time();
			$wechatComponentToken->insert();

			return $rsData['component_access_token'];
		}
		return;
	}

	private function httpRequest($url,$method='get',$params=array()){
		if(trim($url)==''||!in_array($method,array('get','post'))||!is_array($params)){
			return false;
		}
		$curl=curl_init();
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl,CURLOPT_HEADER,0 ) ;
		switch($method){
			case 'get':
				$str='?';
				foreach($params as $k=>$v){
					$str.=$k.'='.$v.'&';
				}
				$str=substr($str,0,-1);
				$url.=$str;//$url=$url.$str;
				curl_setopt($curl,CURLOPT_URL,$url);
			break;
			case 'post':
				curl_setopt($curl,CURLOPT_URL,$url);
				curl_setopt($curl,CURLOPT_POST,1 );
				curl_setopt($curl,CURLOPT_POSTFIELDS,$params);
			break;
			default:
				$result='';
			break;
		}
		$result=curl_exec($curl);
		curl_close($curl);
		return $result;
	}

	private function decodeUnicode($str) {
		return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function( '$matches', 'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");' ), $str);
	}

	private function dataPost($post_string, $url) 
	{
		$context = array (
				'http' => array ('method' => "POST", 
				'header' => "Content-type: application/x-www-form-urlencoded\r\n User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) \r\n Accept: */*", 
				'content' => $post_string ));

		$stream_context = stream_context_create ($context);

		$data = file_get_contents ($url, FALSE, $stream_context);
		$rs = json_decode($data,true);
		
		return $rs;;
	}

	//获取JS SDK 配置信息
	public function getConfig()
	{
		$str = array('appId'=>$this->_appid, 'timestamp'=>$this->_timeStamp, 'nonceStr'=>$this->_nonce,'signature'=>$this->signature());
		return json_encode($str);
	}

	public function signature()
	{
		$string = "jsapi_ticket=".$this->getJsTicket()."&noncestr=".$this->_nonce."&timestamp=".$this->_timeStamp."&url=http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		return sha1($string);
	}

	private function getJsTicket()
	{
		//$ticket = WechatTicket::findBySql("SELECT ticket FROM trio_wechat_ticket WHERE unixtime+7000>'".(time())."'")->scalar();
		// $aa = WechatTicket::findOne('1');
		// $aa->delete();die;
		$ticket = WechatTicket::findOne('1');
		if($ticket && $ticket->unixtime+7000>time())
			return $ticket->ticket;

		$uri = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$this->getApi_authorizer_token().'&type=jsapi';

		$rsData = file_get_contents($uri);
		$rsData = json_decode($rsData,true);

		if($ticket && isset($rsData['ticket'])){			
			$ticket->ticket = $rsData['ticket'];
			$ticket->unixtime = time();
			$ticket->update();

			return $rsData['ticket'];
		}else if(!$ticket){
			$wechatTicket = new WechatTicket();
			$wechatTicket->id = 1;
			$wechatTicket->ticket = $rsData['ticket'];
			$wechatTicket->unixtime = time();
			$wechatTicket->insert();

			return $rsData['ticket'];
		}
		return;
	}
	

	public function getApi_authorizer_token()
	{
		//$access_token = WechatAcessToken::findBySql("SELECT acesstoken FROM trio_wechat_acesstoken WHERE unixtime+7000>'".(time())."'")->scalar();
		// $aa = WechatAcessToken::findOne('1');
		// $aa->delete();die;
		//$access_token = WechatAcessToken::findOne('1');
		$access_token = WechatAcessToken::find()->orderBy('id DESC')->one();

		if($access_token && $access_token->unixtime+7000>time())
			return $access_token->acesstoken;

		$api_component_token = $this->getComponentToken();

		$url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token='.$api_component_token;

		$data = array();
		$data['component_appid'] = $this->_component_appid;
		$data['authorizer_appid'] = $this->_appid;
		$data['authorizer_refresh_token'] = 'refreshtoken@@@a6dRXjSp1PAh-u_v7v_ntQPeid0bPiI1P0NSoAPSFrc';

		if(YII_DEBUG)
			$data['authorizer_refresh_token'] = 'refreshtoken@@@U39Y0WNIMk_pRhNLR0q9SqRjBnuOBHQF0GdBuOXW0g0';//'refreshtoken@@@a6dRXjSp1PAh-u_v7v_ntQPeid0bPiI1P0NSoAPSFrc';

		$rsData = $this->dataPost($this->decodeUnicode(json_encode($data)),$url);
		//return 'TXYwktJIsDwHe3-ElrwtzgUdvaXzMtk9ufaHu6FK7u8aOW4XJiDldOzI6PkhcVWE4MQwATUOGZOMPtoOBgklX7ZoGZ0w68-XuIx0HktDmKaoxzHiiSG-_rh-w6dHk7co';
		//print_r($rsData);die;
		if($access_token && isset($rsData['authorizer_access_token'])){
			//$wechatAcessToken = new WechatAcessToken();
			$access_token->acesstoken = $rsData['authorizer_access_token'];
			$access_token->unixtime = time();
			$access_token->update();

			return $rsData['authorizer_access_token'];
		}else if(!$access_token){
			$wechatAcessToken = new WechatAcessToken();
			$wechatAcessToken->id = 1;
			$wechatAcessToken->acesstoken = $rsData['authorizer_access_token'];
			$wechatAcessToken->unixtime = time();
			$wechatAcessToken->insert();

			return $rsData['authorizer_access_token'];
		}

		return;
	}

	public function saveWechatAuth($data)
	{
		$ticket = WechatAuth::findOne('1');
		if($ticket){
			$ticket->msg = $data;
			$ticket->createtime = date('Y-m-d H:i:s');
			$ticket->update();
			return;
		}

		$WechatAuth = new WechatAuth();
		$WechatAuth->id = 1;
		$WechatAuth->msg = $data;
		$WechatAuth->createtime = date('Y-m-d H:i:s');
		$WechatAuth->insert();
	}

	private function getTicket()
	{
		require str_replace('\\','/',\Yii::$app->basePath.'/vendor/wxcrypt/wxBizMsgCrypt.php');
		//$xml = WechatAR::findBySql("SELECT msg FROM trio_wechat_auth ORDER BY id DESC LIMIT 1")->scalar();
		//$ticket = WechatAuth::findOne('1');
		$ticket = WechatAuth::find()->orderBy('id DESC')->one();
		$xml = $ticket->msg;

		$xml_tree = new \DOMDocument();
		$xml_tree->loadXML($xml);
		$array_e = $xml_tree->getElementsByTagName('Encrypt');
		$encrypt = $array_e->item(0)->nodeValue;

		$sha1 = new \SHA1;
		$array = $sha1->getSHA1($this->_token, $this->_timeStamp, $this->_nonce, $encrypt);
		$ret = $array[0];
		if ($ret != 0) {
			return false;
		}
		$signature = $array[1];

		$pc = new \WXBizMsgCrypt($this->_token, $this->_encodingAesKey, $this->_component_appid);

		$msg = '';
		$errCode = $pc->decryptMsg($signature, $this->_timeStamp, $this->_nonce, $xml, $msg);

		//$xml_tree = new \DOMDocument();
		$xml_tree->loadXML($msg);
		$array_e = $xml_tree->getElementsByTagName('ComponentVerifyTicket');
		$ComponentVerifyTicket = $array_e->item(0)->nodeValue;

		return $ComponentVerifyTicket;
	}

	//创建卡劵
	public function createCard($data)
	{
		// $_type=Common::getValue($data,'type');
		// $token = $this->getApi_authorizer_token();

		// $url = 'https://api.weixin.qq.com/card/create?access_token='.$token;

		// $wxCard = new \app\models\WxCard;
		// $rs = $this->dataPost($this->decodeUnicode($wxCard->setBaseInfo($_type)),$url);

		// print_r($rs);
		// return $rs;
	}

	public function getWxCardTitck()
	{
		$ticket = WechatTicketWxCard::findOne('1');
		if($ticket && $ticket->unixtime+7000>time())
			return $ticket->ticket;

		$uri = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$this->getApi_authorizer_token().'&type=wx_card';

		$rsData = file_get_contents($uri);
		$rsData = json_decode($rsData,true);

		if($ticket && isset($rsData['ticket'])){			
			$ticket->ticket = $rsData['ticket'];
			$ticket->unixtime = time();
			$ticket->update();

			return $rsData['ticket'];
		}else if(!$ticket){
			$WechatTicketWxCard = new WechatTicketWxCard();
			$WechatTicketWxCard->id = 1;
			$WechatTicketWxCard->ticket = $rsData['ticket'];
			$WechatTicketWxCard->unixtime = time();
			$WechatTicketWxCard->insert();

			return $rsData['ticket'];
		}
		return;
	}

	//添加白名单
	public function setTestList()
	{
		$token = $this->getApi_authorizer_token();
		$url = 'https://api.weixin.qq.com/card/testwhitelist/set?access_token='.$token;
		$data = ['openid'=>['o85O7t7KmOnN6Ti_VL5x4MhZuMiw']];
		$rs = $this->dataPost($this->decodeUnicode(json_encode($data)),$url);
		print_r($rs);
		return $rs;
	}

	public function queyAuth()
    {

        $data = array();
        $data['component_appid'] = $this->_component_appid;
        $data['component_appsecret'] = $this->_appsecret;
        $data['component_verify_ticket'] = $this->getTicket();

        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';        

        $AA = $this->dataPost($this->decodeUnicode(json_encode($data)),$url);
        // print_r($AA);die;

        $ysqcodeUrl = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token='.$AA['component_access_token'];
        $data = array();
        $data['component_appid'] = $this->_component_appid;

        $bb = $this->dataPost($this->decodeUnicode(json_encode($data)),$ysqcodeUrl);
        //print_r($bb);
        if(YII_DEBUG)
        	return '<a href="https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid='.$this->_component_appid.'&pre_auth_code='.$bb['pre_auth_code'].'&redirect_uri=http%3A%2F%2Fwow.kfc.trioisobardev.com%2Fsite%2Fcallback1"><img src="https://open.weixin.qq.com/zh_CN/htmledition/res/assets/res-design-download/icon_button3_1.png"></a>';
        else 
        	return '<a href="https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid='.$this->_component_appid.'&pre_auth_code='.$bb['pre_auth_code'].'&redirect_uri=http%3A%2F%2Fwow.kfc.com.cn%2Fsite%2Fcallback1"><img src="https://open.weixin.qq.com/zh_CN/htmledition/res/assets/res-design-download/icon_button3_1.png"></a>';       
    }

    public function callback1($data1)
    {

        $data = array();
        $data['component_appid'] = $this->_component_appid;
        $data['component_appsecret'] = $this->_appsecret;
        $data['component_verify_ticket'] = $this->getTicket();

        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';        

        $AA = $this->dataPost($this->decodeUnicode(json_encode($data)),$url);
        
        $data = array();
        $data['component_appid'] = $this->_component_appid;
        $data['authorization_code'] = $data1['auth_code'];

        $uri = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token='.$AA['component_access_token'];

        $rr = $this->dataPost($this->decodeUnicode(json_encode($data)),$uri);
        //print_r($rr);
        $f = fopen(yii::$aliases['@webroot'].'/protected/runtime/authmsg.txt','a');
        fwrite($f,json_encode($rr)."\r\n");
        fclose($f);
        echo 'success';
        // Yii::app()->end();
    }

    public function wxCardSignature($time,$code,$cardId){    	
        $data = [
            'api_ticket'	=> $this->getWxCardTitck(),
            'card_id'		=> $cardId,
            'timestamp'		=> $time,
            'code'			=> $code,
            'openid'		=> '',
            'nonce_str'		=> $this->_nonce,
            //'balance'		=> '100'
        ];

        sort( $data, SORT_STRING );
        return sha1( implode( $data ) );
    }

    //创建卡劵信息
    public function createWxCardJsConfig($code = '123456789012',$type=1){    	
        // $codeModel = Cdoe4::find()->where(['tag'=>'0'])->limit(1)->one();
        // $code = $codeModel->code;

        // $codeModel->tag = '1';
        // $codeModel->update();
        // 1为蛋挞 2为玉米饮 3为香辣鸡翅 4为音乐桶
    	if(YII_DEBUG){
	    	if($type==4)
	    		$cardId = 'p85O7tyqWWwrVPSwkLk2GywApcxc';
	    	else if($type==3)
	    		$cardId = 'p85O7tyIArxa2eVDdHRRCBW-TvsE';
	    	else if($type==2)
	    		$cardId = 'p85O7t6v9VtGBiKSAymzOAh4TMF4';
	    	else 
	    		$cardId = 'p85O7txjGLGE74hoIWUFm1MY7mZw';
	    }else{
	    	if($type==4)
	    		$cardId = 'p85O7tyqWWwrVPSwkLk2GywApcxc';
	    	else if($type==3)
	    		$cardId = 'p85O7tyIArxa2eVDdHRRCBW-TvsE';
	    	else if($type==2)
	    		$cardId = 'p85O7t6v9VtGBiKSAymzOAh4TMF4';
	    	else 
	    		$cardId = 'p85O7txjGLGE74hoIWUFm1MY7mZw';
	    }

        $time = time();
        $data = [
            'cardId'=>$cardId,
            'cardExt'=>json_encode([
                'code'		=> $code,
                'openid'	=> '',
                'timestamp'	=> $time,
                'signature'	=> $this->wxCardSignature($time,$code,$cardId),
                'nonce_str'	=> $this->_nonce,
                //'balance'		=> '100'
            ])
        ];

        return $data;
    }

    public function decryptCode($dataIn)
    {
    	$token = $this->getApi_authorizer_token();
    	$url = 'https://api.weixin.qq.com/card/code/decrypt?access_token='.$token;
    	$data = ['encrypt_code'=>$dataIn['encrypt_code']];
    	$rr = $this->dataPost($this->decodeUnicode(json_encode($data)),$url);
    	$this->consume(['code'=>$rr['code'],'card_id'=>$dataIn['card_id']]);
    	print_r($rr);die;
    	return $rr;

    }

    public function consume($dataIn)
    {
    	$token = $this->getApi_authorizer_token();
    	$url = 'https://api.weixin.qq.com/card/code/consume?access_token='.$token;
    	$data = ['code'=>$dataIn['code'],'card_id'=>$dataIn['card_id']];
    	$rr = $this->dataPost($this->decodeUnicode(json_encode($data)),$url);
    	print_r($rr);die;
    	return $rr;
    }

    public function getHtml()
    {
    	$token = $this->getApi_authorizer_token();
    	$url = 'https://api.weixin.qq.com/card/mpnews/gethtml?access_token='.$token;
    	$data = ['card_id'=>'p85O7t2G1iQ1OGspFlpcDuSs5S98'];
    	$rr = $this->dataPost($this->decodeUnicode(json_encode($data)),$url);
    	//print_r($rr);die;
    	return $rr;
    }

    public function uploadnews()
    {
    	$html = $this->getHtml();
    	$html=$html['content'];

    	$token = $this->getApi_authorizer_token();
    	$url = 'https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token='.$token;
    	$data = ['articles'=>[['thumb_media_id'=>'KUpSN1Sn_N2NrxvhrKDRjn975deW969qoHiqhMKVMo1BMnR5-NH3_Q8EpGB9Q5eM','author'=>'tom','title'=>'wow','content_source_url'=>'','content'=>str_replace('&amp;', '&', $html),'digest'=>'','show_cover_pic'=>1]]];
    	
    	$rr = $this->dataPost($this->decodeUnicode(json_encode($data)),$url);
    	print_r($rr);die;
    	return $rr;
    }

    public function sendNews()
    {
    	$token = $this->getApi_authorizer_token();
    	$url = 'https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=rNbhMfrBXCCk7uaYfdLp3bqMfyRFFHN9s0c0zQ7ZmN8W1S1Yy7p0Nuv3U9QyvovdrLnp4WQjGHHJ_AEc39eBu8Ekri3aNuXxwlsY-SRvoyw';
    	$data = ['touser'=>'o85O7t7yj6l31WOP6ChRcSWQlNuI','mpnews'=>['media_id'=>'j33xFEirdlDlB8vMqhMGTjzMt5xTTCd3CmyATgGOpiV49ObKdXErdJMz3YB72ug6'],'msgtype'=>'mpnews'];
    	print_r(json_encode($data));
    	$rr = $this->dataPost($this->decodeUnicode(json_encode($data)),$url);
    	print_r($rr);die;
    	return $rr;
    }
}