<?php

namespace Sms\Lib\Alidayu;

use Sms\Lib\BaseHelper;

class Helper extends BaseHelper {

    public function send($conf, $to, $param,$areaCode) {
        $c = new TopClient;
        $c->appkey = $conf['appkey'];
        $c->secretKey = $conf['secret'];
        $req = new AlibabaAliqinFcSmsNumSendRequest;

        if (!empty($conf['extend'])) {
            $req->setExtend($conf['extend']);
        }
        $req->setSmsType($conf['type']);
        $req->setSmsFreeSignName($conf['sign']);
        if (!empty($param)) {
            // 如果传入数据不是 json 字符串，将其转化为 json 字符串
            if (is_array($param)) {
                $param = json_encode($param);
            }

            $req->setSmsParam($param);
        }
        $req->setRecNum($to);
        $req->setSmsTemplateCode($conf['template']);

        return $c->execute($req);
    }
}