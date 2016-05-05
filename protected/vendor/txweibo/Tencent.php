<?php 
/**
 * 腾讯微博API调用类
 * @author xiaopengzhu <xp_zhu@qq.com>
 * @version 2.0 2012-04-20
 */
class Tencent
{
	//接口url
	public static $apiUrlHttp = 'http://open.t.qq.com/api/';
	public static $apiUrlHttps = 'https://open.t.qq.com/api/';

	//调试模式
	public static $debug = false;

	/**
	 * 发起一个腾讯API请求
	 * @param $command 接口名称 如：t/add
	 * @param $params 接口参数  array('content'=>'test');
	 * @param $method 请求方式 POST|GET
	 * @param $multi 图片信息
	 * @return string
	 */
	public static function api($command, $params = array(), $method = 'GET', $multi = false)
	{
		$session = new yii\web\Session;
        $session->open();
        
		if (isset($session['t_access_token'])) {//OAuth 2.0 方式
			//鉴权参数
			$params['access_token'] = $session['t_access_token'];
			$params['oauth_consumer_key'] = Yii::$app->params['txWbAkey'];
			$params['openid'] = $session['t_openid'];
			$params['oauth_version'] = '2.a';
			$params['clientip'] = \Common::getClientIp();
			$params['scope'] = 'all';
			$params['appfrom'] = 'php-sdk2.0beta';
			$params['seqid'] = time();
			$params['serverip'] = $_SERVER['SERVER_ADDR'];

			$url = self::$apiUrlHttps.trim($command, '/');
		} elseif (isset($session['t_openid']) && isset($session['t_openkey'])) {//openid & openkey方式
			$params['appid'] = Yii::$app->params['txWbAkey'];
			$params['openid'] = $session['t_openid'];
			$params['openkey'] = $session['t_openkey'];
			$params['clientip'] = \Common::getClientIp();
			$params['reqtime'] = time();
			$params['wbversion'] = '1';
			$params['pf'] = 'php-sdk2.0beta';

			$url = self::$apiUrlHttp.trim($command, '/');
			//生成签名
			$urls = @parse_url($url);
			$sig = SnsSign::makeSig($method, $urls['path'], $params, OAuth::$client_secret.'&');
			$params['sig'] = $sig;
		}

		//请求接口
		$r = Http::request($url, $params, $method, $multi);
		$r = preg_replace('/[^\x20-\xff]*/', "", $r); //清除不可见字符
		$r = iconv("utf-8", "utf-8//ignore", $r); //UTF-8转码
		//调试信息
		if (self::$debug) {
			echo '<pre>';
			echo '接口：'.$url;
			echo '<br>请求参数：<br>';
			print_r($params);
			echo '返回结果：'.$r;
			echo '</pre>';
		}
		return $r;
	}
}

?>