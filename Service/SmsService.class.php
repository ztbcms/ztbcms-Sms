<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Sms\Service;

use System\Service\BaseService;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class SmsService extends BaseService {

    const SEND_STATUS_YES = 1;//发送成功
    const SEND_STATUS_NO = 0;//发送失败
    const USED_STATUS_YES = 1; //已使用
    const USED_STATUS_NO = 0; //为使用

    /**
     * 发送短信
     *
     * @param string $template 短信模板ID，从后台配置获取
     * @param string $to       短信接收人，多个接收人号码之间使用英文半角逗号隔开
     * @param array  $param    短信模板变量，数组或者json字符串
     * @param string $action   例如:register,login
     * @param array  $operator 短信平台，不传入时，使用后台配置的默认短信平台
     * @param array  $areaCode 手机的区号（国际版阿里云需要使用）
     * @return array
     */
    public static function sendSms($template, $to, $param = null, $action = 'none', $operator = null,$areaCode = null) {


        //如果没有传入短信平台，则使用后台配置的开启的短信平台发送
        if (null == $operator) {
            $operator = M('smsOperator')->where("enable='1'")->find()['tablename'];
        }

        // 获取短信模板配置
        $model = M('sms_' . $operator);
        $conf = $model->find($template);


        if($operator == 'alibabacloud_mainland' || $operator == 'alibabacloud_abroad'){
            $operator = 'AlibabaCloud';
        }

        //检查是否存在指定的文件
        $file = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "Lib" . DIRECTORY_SEPARATOR . ucfirst($operator) . DIRECTORY_SEPARATOR . "Helper.php";

        // 保证所有的参数都是字符串类型
        if (is_array($param)) {
            foreach ($param as $k => $v) {
                $param[$k] = $v . "";
            }
        }
        if (file_exists($file)) {
            //导入当前模块下Lib目录下的指定文件
            require_once(PROJECT_PATH . "Application/Sms/Lib/" . ucfirst($operator) . "/Helper.php");
            $className = "\\Sms\\Lib\\" . ucfirst($operator) . "\\Helper";
            $helper = new $className();
            $result = json_encode($helper->send($conf, $to, $param, $areaCode));
            //发送结果存入数据库
            $log = array(
                'operator' => $operator,
                'template' => json_encode($conf),
                'recv' => $to,
                'param' => is_array($param) ? json_encode($param) : $param,
                'sendtime' => time(),
                'result' => $result,
                'send_status' => self::SEND_STATUS_NO,
                'is_used' => self::SEND_STATUS_NO,
                'action' => $action,
            );
            if (M('sms_log')->create($log)) {
                $sms_log_id = M('sms_log')->add($log);
            }
            $data = json_decode($result, true);
            if ($data['Code'] == 'OK' || $data['code'] == 'OK') {
                if ($sms_log_id) {
                    //发送状态 => 成功
                    M('sms_log')->where(['id' => $sms_log_id])->save(['send_status' => self::SEND_STATUS_YES]);
                }
                return self::createReturn(true, [], '发送成功');
            } else {
                $error = $data['msg'];
                return self::createReturn(false, $error, '发送失败');
            }
        } else {
            return self::createReturn(false, null, '不支持的短信平台');
        }
    }

    /**
     * 验证
     *
     * @param $phone  string 手机号
     * @param $code   string 验证码
     * @param $action string 例如:register,login
     * @return bool
     */
    static function checkSmsCode($phone, $code, $action = 'none') {
        $code = (int)$code;

        $count = M('SmsLog')->where([
            'recv' => $phone,
            'action' => $action,
            'is_used' => self::USED_STATUS_NO, //未使用
            'send_status' => self::SEND_STATUS_YES, //发送成功
            'param' => ['LIKE', '%"' . $code . '"%'],
        ])->count();
        if ($count) {
            M('SmsLog')->where([
                'recv' => $phone,
                'action' => $action,
                'send_status' => self::SEND_STATUS_YES,
                'param' => ['LIKE', '%"' . $code . '"%']
            ])->save(['is_used' => self::USED_STATUS_YES]);

            return true;
        } else {
            return false;
        }
    }
}
