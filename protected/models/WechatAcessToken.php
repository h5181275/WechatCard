<?php

namespace app\models;
use yii\db\ActiveRecord;
class WechatAcessToken extends ActiveRecord
{
    public static function tableName()
    {
        return 'trio_qrcode';
    }
}