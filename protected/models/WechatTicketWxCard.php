<?php

namespace app\models;


class WechatTicketWxCard extends \yii\redis\ActiveRecord
{
	public function attributes()
    {
        return ['id', 'ticket', 'unixtime', 'createtime'];
    }

}