<?php 
/**
 * OAuth授权类
 * @author xiaopengzhu <xp_zhu@qq.com>
 * @version 2.0 2012-04-20
 */
class OAuth
{
	public static $client_id = '';
	public static $client_secret = '';

	private static $accessTokenURL = 'https://open.t.qq.com/cgi-bin/oauth2/access_token';
	private static $authorizeURL = 'https://open.t.qq.com/cgi-bin/oauth2/authorize';

	/**
	 * 初始化
	 * @param $client_id 即 appid
	 * @param $client_secret 即 appkey
	 * @return
	 */
	public static function init($client_id, $client_secret)
	{
		if (!$client_id || !$client_secret) exit('client_id or client_secret is null');
		self::$client_id = $client_id;
		self::$client_secret = $client_secret;
	}

	/**
	 * 获取授权URL
	 * @param $redirect_uri 授权成功后的回调地址，即第三方应用的url
	 * @param $response_type 授权类型，为code
	 * @param $wap 用于指定手机授权页的版本，默认PC，值为1时跳到wap1.0的授权页，为2时同理
	 * @return string
	 */
	public static function getAuthorizeURL($redirect_uri, $response_type = 'code', $wap = false,$type=null)
	{
		$params = array(
				'client_id' => self::$client_id,
				'redirect_uri' => $redirect_uri,
				'response_type' => $response_type,
				'type' => $type
		);
		return self::$authorizeURL.'?'.http_build_query($params);
	}

	/**
	 * 获取请求token的url
	 * @param $code 调用authorize时返回的code
	 * @param $redirect_uri 回调地址，必须和请求code时的redirect_uri一致
	 * @return string
	 */
	public static function getAccessToken($code, $redirect_uri)
	{
		$params = array(
				'client_id' => self::$client_id,
				'client_secret' => self::$client_secret,
				'grant_type' => 'authorization_code',
				'code' => $code,
				'redirect_uri' => $redirect_uri
		);
		return self::$accessTokenURL.'?'.http_build_query($params);
	}


	/**
	 * 验证授权是否有效
	 */
	public static function checkOAuthValid()
	{
		$r = json_decode(Tencent::api('user/info'), true);
		if ($r['data']['name']) {
			return true;
		} else {
			self::clearOAuthInfo();
			return false;
		}
	}

	/**
	 * 清除授权
	 */
	public static function clearOAuthInfo()
	{
		if (isset($_SESSION['t_access_token'])) unset($_SESSION['t_access_token']);
		if (isset($_SESSION['t_expire_in'])) unset($_SESSION['t_expire_in']);
		if (isset($_SESSION['t_code'])) unset($_SESSION['t_code']);
		if (isset($_SESSION['t_openid'])) unset($_SESSION['t_openid']);
		if (isset($_SESSION['t_openkey'])) unset($_SESSION['t_openkey']);
		if (isset($_SESSION['t_oauth_version'])) unset($_SESSION['t_oauth_version']);
	}
}
?>