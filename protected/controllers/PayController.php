<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;

class PayController extends Controller
{

    private $_APPID = '';
    private $_MCHID = '';
    private $_KEY = '';
    private $_APPSECRET = '';

    public function actionIndex()
    {      
        // ECHO '<PRE>';
        // print_r($this->unifiedOrder());
        return $this->unifiedOrder();
    	$dataIn = $_REQUEST;
    	$appid = $dataIn['appid'];
    	$openid = $dataIn['openid'];
    	$mch_id = $dataIn['mch_id'];
    	$is_subscribe = $dataIn['is_subscribe'];
    	$nonce_str = $dataIn['nonce_str'];
    	$product_id = $dataIn['product_id'];
    	$sign = $dataIn['sign'];

    	$dataOut = [];
        $dataOut['return_code'] = 'SUCCESS';
    	
    }

    private function unifiedOrder()
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

        $dataIn = [];
        $dataIn['appid'] = $this->_APPID;
        $dataIn['mch_id'] = $this->_MCHID;
        $dataIn['nonce_str'] = self::getNonceStr();        
        $dataIn['body'] = 'test';
        $dataIn['out_trade_no'] = $this->_MCHID.date("YmdHis");
        $dataIn['total_fee'] = '1';
        $dataIn['spbill_create_ip'] = Yii::$app->request->userIp;
        $dataIn['notify_url'] = 'http://www.xxx.com/pay/callback';
        $dataIn['trade_type'] = 'NATIVE';
        $dataIn['sign'] = $this->MakeSign($dataIn);

        $xml =  $this->ToXml($dataIn);
        
        return $response = self::postXmlCurl($xml, $url, false, 6);

        $rsXml = $this->FromXml($response);
        //fix bug 2015-06-29
        if($rsXml['return_code'] != 'SUCCESS'){
             return $rsXml;
        }
        $this->CheckSign($rsXml);
        return $rsXml;

        
    }

    public static function getNonceStr($length = 32) 
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {  
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
        } 
        return $str;
    }

    private function MakeSign($dateIn)
    {
        //签名步骤一：按字典序排序参数
        ksort($dateIn);
        $string = $this->ToUrlParams($dateIn);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->_KEY;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    private function ToUrlParams($dateIn)
    {
        $buff = "";
        foreach ($dateIn as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        
        $buff = trim($buff, "&");
        return $buff;
    }

    private function ToXml($dateIn)
    {
        if(!is_array($dateIn) 
            || count($dateIn) <= 0)
        {
            throw new WxPayException("数组数据异常！");
        }
        
        $xml = "<xml>";
        foreach ($dateIn as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml; 
    }

    private function FromXml($xml)
    {   
        if(!$xml){
            throw new WxPayException("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
    }

    private static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {       
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        
        //如果有配置代理这里就设置代理
        // if(WxPayConfig::CURL_PROXY_HOST != "0.0.0.0" 
        //     && WxPayConfig::CURL_PROXY_PORT != 0){
        //     curl_setopt($ch,CURLOPT_PROXY, WxPayConfig::CURL_PROXY_HOST);
        //     curl_setopt($ch,CURLOPT_PROXYPORT, WxPayConfig::CURL_PROXY_PORT);
        // }
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else { 
            $error = curl_errno($ch);
            curl_close($ch);
            die("curl出错，错误码:$error");
        }
    }

    public function CheckSign($dataIn)
    {
        //fix异常
        if(!array_key_exists('sign',$dataIn)){
            die("签名错误！");
        }
        
        $sign = $this->MakeSign($dataIn);
        if($dataIn['sign'] == $sign){
            return true;
        }
        die("签名错误！");
    }
    
}
