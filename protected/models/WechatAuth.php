<?php

namespace app\models;
use yii\db\ActiveRecord;

class WechatAuth extends ActiveRecord
{
	public static function tableName()
    {
        return 'trio_wechat_auth';
    }

}