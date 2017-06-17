<?php

namespace Sms\Lib;

abstract class BaseHelper {
    /**
     * 短信发送
     *
     * @param $conf array 配置
     * @param $to string 接受人
     * @param $param array 短信参数
     * @return mixed
     */
    abstract function send($conf, $to, $param);
}