<?php

namespace app\models;

use yii\db\ActiveRecord;

class WxLink extends ActiveRecord
{
	public static function tableName()
    {
        return 'trio_wxlink';
    }

}