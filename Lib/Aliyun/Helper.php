<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Sms\Lib\Aliyun;

//引入阿里云短信服务SDK
require_once(dirname(__FILE__) . '/vendor/autoload.php');

use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
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
        // 加载区域结点配置
        Config::load();

        // 短信API产品名
        $product = "Dysmsapi";

        // 短信API产品域名
        $domain = "dysmsapi.aliyuncs.com";

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";

        // 初始化用户Profile实例
        $profile = DefaultProfile::getProfile($region, $conf['access_id'], $conf['access_key']);

        // 增加服务结点
        DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

        // 初始化AcsClient用于发起请求
        $acsClient = new DefaultAcsClient($profile);

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置雉短信接收号码
        $request->setPhoneNumbers($to);

        // 必填，设置签名名称
        $request->setSignName($conf['sign']);

        // 必填，设置模板CODE
        $request->setTemplateCode($conf['template']);

        // 可选，设置模板参数
        if($param) {
            $request->setTemplateParam(json_encode($param));
        }

        // 发起访问请求
        $acsResponse = $acsClient->getAcsResponse($request);

        // 打印请求结果
//         var_dump($acsResponse);

         return $acsResponse;

    }
}