<?php
/**
 * Created by PhpStorm.
 * User: cycle_3
 * Email: 953006367@qq.com
 * Date: 2019/9/4
 * Time: 16:38
 */
namespace Sms\Lib\AlibabaCloud;

use Sms\Lib\BaseHelper;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class Helper extends BaseHelper {

    const HINTERLAND_PHONE = '86';

    public function send($conf, $to, $param, $areaCode) {
        if(!$areaCode) return self::createReturn(false, '', '手机区号不能为空','NO');
        //区分使用大陆短信还是国籍短信
        if($areaCode == self::HINTERLAND_PHONE){
            //发送大陆短信
            $phone = $areaCode.$to;
            return $this->sendAlibabacloudMainland($conf, $phone, $param);
        } else {
            //发送国籍短信
            $phone = $areaCode.$to;
            return $this->sendAlibabacloudAbroad($conf, $phone, $param);
        }
    }

    /**
     * 发送阿里云国际版的大陆消息
     * @param $conf 消息模板
     * @param $to 手机号码(注意此处手机号码需要带区号)
     * @param $param （对应的参数）
     */
    public function sendAlibabacloudMainland($conf, $to, $param){
        if(!$conf) return self::createReturn(false, '', '模板不存在','NO');
        if(!$to) return self::createReturn(false, '', '电话号码不能为空','NO');
        require dirname(dirname(__DIR__)). '/Lib/AlibabaCloud/client/vendor/autoload.php';
        if ($conf['is_open'] != '1') return self::createReturn(false, '', '该短信服务未开启','NO');
        if(!$conf['sms_from'] || !$conf['sms_template_code']) return self::createReturn(false, '', '该短信服务未开启','NO');
        AlibabaCloud::accessKeyClient($conf['access_key_id'], $conf['access_key_secret'])
            ->regionId('ap-southeast-1')
            ->asGlobalClient();
        $TemplateParam = json_encode($param,true);
        $query = [
            'To' => $to,
            'From' => $conf['sms_from'], //短信签名
            'TemplateCode' => $conf['sms_template_code'], //短信内容
            'TemplateParam' => $TemplateParam  //短信的内容
        ];
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
            if($result->toArray()['ResponseDescription'] == 'OK'){
                //发送记录
                return self::createReturn(true, $result->toArray(), '请求成功','OK');
            } else {
                return self::createReturn(false, $result->toArray(), '请求失败','NO');
            }
        } catch (ClientException $e) {
            return self::createReturn(false, $e->getErrorMessage() . PHP_EOL, '请求失败','NO');
        } catch (ServerException $e) {
            return self::createReturn(false, $e->getErrorMessage() . PHP_EOL, '请求失败','NO');
        }
    }

    /**
     * 发送阿里云国际版的国际消息
     * @param $template_id 消息模板
     * @param $phone 手机号码(注意此处手机号码需要带区号)
     * @param $param 建议实际使用的时候更换为需要发送的消息
     * @return mixed
     * 短信消息发送是否成功可通过 https://sms-intl.console.aliyun.com 登录查看
     */
    public function sendAlibabacloudAbroad($conf,$phone,$message){
        if(!$conf) return self::createReturn(false, null, '模板不能为空');
        if(!$phone) return self::createReturn(false, null, '电话号码不能为空');
        require dirname(dirname(__DIR__)). '/Lib/AlibabaCloud/client/vendor/autoload.php';
        if ($conf['is_open'] != '1') {
            return self::createReturn(false, null, '该短信服务未开启');
        }
        AlibabaCloud::accessKeyClient($conf['access_key_id'], $conf['access_key_secret'])
            ->regionId('ap-southeast-1')
            ->asGlobalClient();
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
            if($result->toArray()['ResponseCode'] == 'OK'){
                //发送记录
                return self::createReturn(true, $result->toArray(), '请求成功','OK');
            } else {
                return self::createReturn(false, $result->toArray(), '请求失败','NO');
            }
        } catch (ClientException $e) {
            return self::createReturn(false, $e->getErrorMessage() . PHP_EOL, '请求失败','NO');
        } catch (ServerException $e) {
            $log['result'] = $e->getErrorMessage() . PHP_EOL.'请求失败';
            return self::createReturn(false, $e->getErrorMessage() . PHP_EOL, '请求失败','NO');
        }
    }

    static function createReturn($status, $data = [], $msg = '', $code = null, $url = '') {
        //默认成功则为200 错误则为400
        if(empty($code)){
            $code = $status ? 200 : 400;
        }
        return [
            'status' => $status,
            'code'   => $code,
            'data'   => $data,
            'msg'    => $msg,
            'url'    => $url,
        ];
    }

}