<?php

namespace app\models;

use yii\db\ActiveRecord;

class WechatAR extends ActiveRecord
{

	public static function tableName()
    {
        return 'trio_wechat';
    }

}