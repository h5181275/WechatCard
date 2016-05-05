<?php

namespace app\models;

use yii\db\ActiveRecord;

class WxError extends ActiveRecord
{
	public static function tableName()
    {
        return 'trio_wx_error';
    }

}