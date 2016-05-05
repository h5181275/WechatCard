<?php

namespace app\models;
use yii\db\ActiveRecord;
class WechatTicket extends ActiveRecord
{
    public static function tableName()
    {
        return 'trio_qrcode';
    }
}