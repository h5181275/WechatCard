<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\WxCardBaseInfo;

class WxCard extends Model
{
	
	public function setBaseInfo()
	{
		$baseInfo = new WxCardBaseInfo();

		$baseInfo->setBaseInfo( "http://www.xxxx.com/upload/Logo-300X300.png", "企业名称",0, "卡券名称",  "Color100", "下单时请输入以上优惠代码", "","说明", ["type"=>1, "begin_timestamp"=>strtotime('2015-08-10'), "end_timestamp"=>strtotime('2015-08-30')], ["quantity"=>50000000]);
		$baseInfo->set_sub_title( "网订任意消费，即可享受" );
		$baseInfo->set_use_limit( 1 );
		$baseInfo->set_get_limit( 1 );
		$baseInfo->set_use_custom_code( true );
		$baseInfo->set_bind_openid( false );
		$baseInfo->set_can_share( true );
		//$baseInfo->set_url_name_type( 2 );
		$baseInfo->set_custom_url( "立即使用地址Url" );
		$baseInfo->set_custom_url_name( "立即使用" );
		$deal_detail = "优惠券有效期： 2015年8月10日-8月30日10:00-22:00"  ;

		$arr = ['card'=>['card_type'=>'GENERAL_COUPON','general_coupon'=>['base_info'=>$baseInfo,'default_detail'=>$deal_detail]]];
		//echo "<pre>";print_r(urldecode(json_encode($arr)));
		return urldecode(json_encode($arr));
	}

}