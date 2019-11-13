<?php

namespace app\models\traits;


use yii\web\HttpException;

trait CheckForm
{

    public function checkParam($param = '')
    {
        if (!isset($this->$param))  return  false;
        if (empty(self::findOne([$param => $this->$param]))) return  false;

        return true;
    }


}