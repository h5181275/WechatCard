<?php 
/**
 * Openid & Openkey签名类
 * @author xiaopengzhu <xp_zhu@qq.com>
 * @version 2.0 2012-04-20
 */
class SnsSign
{
	/**
	 * 生成签名
	 * @param string    $method 请求方法 "get" or "post"
	 * @param string    $url_path
	 * @param array     $params 表单参数
	 * @param string    $secret 密钥
	 */
	public static function makeSig($method, $url_path, $params, $secret)
	{
		$mk = self::makeSource ( $method, $url_path, $params );
		$my_sign = hash_hmac ( "sha1", $mk, strtr ( $secret, '-_', '+/' ), true );
		$my_sign = base64_encode ( $my_sign );
		return $my_sign;
	}

	private static function makeSource($method, $url_path, $params)
	{
		ksort ( $params );
		$strs = strtoupper($method) . '&' . rawurlencode ( $url_path ) . '&';
		$str = "";
		foreach ( $params as $key => $val ) {
			$str .= "$key=$val&";
		}
		$strc = substr ( $str, 0, strlen ( $str ) - 1 );
		return $strs . rawurlencode ( $strc );
	}
}
?>