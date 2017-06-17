<?php

namespace Sms\Lib\Ucpaas;

use Sms\Lib\BaseHelper;

class Helper extends BaseHelper {

    public function send($conf, $to, $param) {

        $ucpass = new Ucpaas($conf);

        return $ucpass->templateSMS($to, $param);

    }
}