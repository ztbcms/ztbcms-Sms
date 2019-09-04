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
     * @return array
     */
    public static function sendSms($template, $to, $param = null, $action = 'none', $operator = null) {


        //如果没有传入短信平台，则使用后台配置的开启的短信平台发送
        if (null == $operator) {
            $operator = M('smsOperator')->where("enable='1'")->find()['tablename'];
        }

        // 获取短信模板配置
        $model = M('sms_' . $operator);
        $conf = $model->find($template);

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
            $result = json_encode($helper->send($conf, $to, $param));

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
            if ($data['Code'] == 'OK') {
                if ($sms_log_id) {
                    //发送状态 => 成功
                    M('sms_log')->where(['id' => $sms_log_id])->save(['send_status' => self::SEND_STATUS_YES]);
                }

                return self::createReturn(true, [], '发送成功');
            } else {
                $error = $data['Message'];

                return self::createReturn(false, null, '发送失败');
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

    /**
     * 发送阿里云国际版的国际短信
     * @param string $template_id 消息模板
     * @param string $phone 手机号码(注意此处手机号码需要带区号)
     * @param array $param 建议实际使用的时候更换为需要发送的消息
     * @return mixed
     * 短信消息发送是否成功可通过 https://sms-intl.console.aliyun.com 登录查看
     * @throws ClientException
     */
    public static function sendAlibabacloudAbroad($template_id,$phone,$param){
        if(!$template_id) return self::createReturn(false, null, '模板id不能为空');
        if(!$phone) return self::createReturn(false, null, '电话号码不能为空');

        $message = '发送的参数为';
        foreach ($param as $k => $v){
            $message .= $k.':'.$v;
        }

        require dirname(__DIR__) . '/Lib/AlibabaCloud/client/vendor/autoload.php';
        $alibaba = M('sms_alibabacloud_abroad')->where(['id'=>$template_id])->find();
        if ($alibaba['is_open'] != '1') {
            return self::createReturn(false, null, '该短信服务未开启');
        }
        AlibabaCloud::accessKeyClient($alibaba['access_key_id'], $alibaba['access_key_secret'])
            ->regionId('ap-southeast-1')
            ->asGlobalClient();

        $conf = M('sms_alibabacloud_abroad')->find($template_id);
        $log = array(
            'operator' => 'alibabacloud_mainland',
            'template' => json_encode($conf),
            'recv' => $phone,
            'param' => is_array($param) ? json_encode($param) : $param,
            'sendtime' => time(),
            'is_used' => self::SEND_STATUS_NO,
        );

        try {
            $result = AlibabaCloud::rpcRequest()
                ->product('Dysmsapi')
                ->host('dysmsapi.ap-southeast-1.aliyuncs.com')
                ->version('2018-05-01')
                ->action('SendMessageToGlobe')
                ->method('POST')
                ->options([
                    'query' => [
                        "To" => $phone,
                        "Message" => $message,
                    ],
                ])->request();
            $conf = M('sms_alibabacloud_abroad')->find($template_id);

            //发送记录
            $log['result'] = '发送成功';
            M('sms_log')->add($log);
            return self::createReturn(true, null, '请求成功');
        } catch (ClientException $e) {
            $log['result'] = $e->getErrorMessage() . PHP_EOL.'请求失败';
            M('sms_log')->add($log);
            return self::createReturn(false, $e->getErrorMessage() . PHP_EOL, '请求失败');
        } catch (ServerException $e) {
            $log['result'] = $e->getErrorMessage() . PHP_EOL.'请求失败';
            M('sms_log')->add($log);
            return self::createReturn(false, $e->getErrorMessage() . PHP_EOL, '请求失败');
        }
    }

    /**
     * 发送阿里云国际版的大陆短信
     * @param string $template_id 消息模板
     * @param string $phone
     * @param array $param （对应的参数）
     * @return array
     * @throws ClientException
     */
    public static function sendAlibabacloudMainland($template_id, $phone, $param){
        if(!$template_id) return self::createReturn(false, '', '模板id不能为空');
        if(!$phone) return self::createReturn(false, '', '电话号码不能为空');

        require dirname(__DIR__) . '/Lib/AlibabaCloud/client/vendor/autoload.php';
        $alibaba = M('sms_alibabacloud_mainland')->where(['id'=>$template_id])->find();
        if ($alibaba['is_open'] != '1') {
            return self::createReturn(false, '', '该短信服务未开启');
        }
        if(!$alibaba['sms_from'] || !$alibaba['sms_template_code']){
            return self::createReturn(false, '', '该短信服务未开启');
        }
        AlibabaCloud::accessKeyClient($alibaba['access_key_id'], $alibaba['access_key_secret'])
            ->regionId('ap-southeast-1')
            ->asGlobalClient();
        $TemplateParam = json_encode($param,true);
        $query = [
            'To' => $phone,
            'From' => $alibaba['sms_from'], //短信签名
            'TemplateCode' => $alibaba['sms_template_code'], //短信内容
            'TemplateParam' => $TemplateParam  //短信的内容
        ];
        $conf = M('sms_alibabacloud_mainland')->find($template_id);
        $log = array(
            'operator' => 'alibabacloud_mainland',
            'template' => json_encode($conf),
            'recv' => $phone,
            'param' => is_array($param) ? json_encode($param) : $param,
            'sendtime' => time(),
            'is_used' => self::SEND_STATUS_NO,
        );
        try {
            $result = AlibabaCloud::rpcRequest()
                ->product('Dysmsapi')
                ->host('dysmsapi.ap-southeast-1.aliyuncs.com')
                ->version('2018-05-01')
                ->action('SendMessageWithTemplate')
                ->method('POST')
                ->options([
                    'query' => $query,
                ])
                ->request();
            //发送记录
            $log['result'] = '请求成功';
            M('sms_log')->add($log);
            return self::createReturn(true, null, '请求成功');
        } catch (ClientException $e) {
            $log['result'] = $e->getErrorMessage().PHP_EOL.'请求失败';
            M('sms_log')->add($log);
            return self::createReturn(false, $e->getErrorMessage() . PHP_EOL, '请求失败');
        } catch (ServerException $e) {
            $log['result'] = $e->getErrorMessage().PHP_EOL.'请求失败';
            M('sms_log')->add($log);
            return self::createReturn(false, $e->getErrorMessage() . PHP_EOL, '请求失败');
        }
    }
}
