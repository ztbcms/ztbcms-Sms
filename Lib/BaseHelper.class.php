<?php

namespace Sms\Lib;

abstract class BaseHelper {
    /**
     * 短信发送
     *
     * @param $conf   array    配置
     * @param $to     string   短信接收人，多个接收人号码之间使用英文半角逗号隔开
     * @param $param  array    短信参数
     * @return mixed
     */
    abstract function send($conf, $to, $param);
}