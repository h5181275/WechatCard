<?php

namespace app\models;
use yii\db\ActiveRecord;

class WechatComponentToken extends ActiveRecord
{

	public static function tableName()
    {
        return 'trio_component_token';
    }

}