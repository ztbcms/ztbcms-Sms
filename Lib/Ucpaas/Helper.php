<?php

namespace Sms\Lib\Ucpaas;

use Sms\Lib\BaseHelper;

class Helper extends BaseHelper {

    /**
     *
     * @param array $conf
     * @param string $to
     * @param array $param
     * @return mixed|string
     */
    public function send($conf, $to, $param) {

        // 如果传入数据不是 json 字符串，将其转化为 json 字符串
        if (is_array($param)) {
            $param = json_encode($param);
        }

        $ucpass = new Ucpaas($conf);

        return $ucpass->templateSMS($to, $param);

    }
}