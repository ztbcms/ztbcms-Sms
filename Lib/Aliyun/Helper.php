<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Sms\Lib\Aliyun;

use Sms\Lib\BaseHelper;

class Helper extends BaseHelper {

    /**
     * 短信发送
     *
     * @param $conf   array    配置
     * @param $to     string   短信接收人，多个接收人号码之间使用英文半角逗号隔开
     * @param $param  array    短信参数
     * @return mixed
     */
    function send($conf, $to, $param) {
        require_once(dirname(__FILE__) . '/vendor/autoload.php');




    }
}