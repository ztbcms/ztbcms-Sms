<?php

namespace Sms\Lib\Alisms;

use AliyunMNS\Client;
use AliyunMNS\Model\BatchSmsAttributes;
use AliyunMNS\Model\MessageAttributes;
use AliyunMNS\Model\SubscriptionAttributes;
use AliyunMNS\Requests\PublishMessageRequest;
use AliyunMNS\Requests\CreateTopicRequest;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Topic;

use Sms\Lib\BaseHelper;

class Helper extends BaseHelper {

    protected $client;

    function send($conf, $to, $param) {

        require_once(dirname(__FILE__) . '/mns-autoloader.php');

        /**
         * Step 1. 初始化Client
         */
        $this->client = new Client($conf['end_point'], $conf['access_id'], $conf['access_key']);
        /**
         * Step 2. 获取主题引用
         */
        $topic = $this->client->getTopicRef($conf['topic_name']);
        /**
         * Step 3. 生成SMS消息属性
         */
        // 3.1 设置发送短信的签名（SMSSignName）和模板（SMSTemplateCode）
        $batchSmsAttributes = new BatchSmsAttributes($conf['sign'], $conf['template']);
        // 3.2 （如果在短信模板中定义了参数）指定短信模板中对应参数的值
        $to = explode(',', $to);
        foreach ($to as $phone) {
            $batchSmsAttributes->addReceiver($phone, $param);
        }
        $messageAttributes = new MessageAttributes(array($batchSmsAttributes));
        /**
         * Step 4. 设置SMS消息体（必须）
         *
         * 注：目前暂时不支持消息内容为空，需要指定消息内容，不为空即可。
         */
        $messageBody = $conf['message_body'];
        /**
         * Step 5. 发布SMS消息
         */
        $request = new PublishMessageRequest($messageBody, $messageAttributes);

        try {
            return $topic->publishMessage($request);
        } catch (MnsException $e) {
            return $e;
        }

    }

    /**
     * 创建短信主题
     * @param $topicName
     * @return bool
     */
    protected function createTopic($topicName) {
        $request = new CreateTopicRequest($topicName);
        try {

            $res = $this->client->createTopic($request);
            echo "TopicCreated! \n";
            return true;
        } catch (MnsException $e) {
            // 2. 可能因为网络错误，或者Topic已经存在等原因导致CreateTopic失败，这里CatchException并做对应的处理
            echo "CreateTopicFailed: " . $e . "\n";
            echo "MNSErrorCode: " . $e->getMnsErrorCode() . "\n";
            return false;
        }
    }

    /**
     * 删除短信主题
     * @param $topicName
     * @return bool
     */
    protected function deleteTopic($topicName) {
        try {
            $this->client->deleteTopic($topicName);
            echo "DeleteTopic Succeed! \n";
            return true;
        } catch (MnsException $e) {
            echo "DeleteTopic Failed: " . $e;
            return false;
        }
    }

    /**
     * 创建订阅
     * @param $subscriptionName
     * @return bool
     */
    protected function subscript($subscriptionName) {
        // 1. 生成SubscriptionAttributes，这里第二个参数是Subscription的Endpoint。
        // 1.1 这里设置的是刚才启动的http server的地址
        // 1.2 更多支持的Endpoint类型可以参考：help.aliyun.com/document_detail/27479.html
        $attributes = new SubscriptionAttributes($subscriptionName, 'http://' . $this->ip . ':' . $this->port);
        try {
            $this->topic->subscribe($attributes);
            // 2. 订阅成功
            echo "Subscribed! \n";
            return true;
        } catch (MnsException $e) {
            // 3. 可能因为网络错误，或者同名的Subscription已存在等原因导致订阅出错，这里CatchException并做对应的处理
            echo "SubscribeFailed: " . $e . "\n";
            echo "MNSErrorCode: " . $e->getMnsErrorCode() . "\n";
            return false;
        }
    }

    /**
     * 删除订阅
     * @param $subscriptionName
     * @return bool
     */
    protected function deleteSubscript($subscriptionName) {
        try {
            $this->topic->unsubscribe($subscriptionName);
            echo "Unsubscribe Succeed! \n";
            return true;
        } catch (MnsException $e) {
            echo "Unsubscribe Failed: " . $e;
            return false;
        }
    }

    /**
     * 推送消息
     * @return bool
     */
    protected function pushMessage() {
        $messageBody = "test";
        // 1. 生成PublishMessageRequest
        // 1.1 如果是推送到邮箱，还需要设置MessageAttributes，可以参照Tests/TopicTest.php里面的testPublishMailMessage
        $request = new PublishMessageRequest($messageBody);
        try {
            $res = $this->topic->publishMessage($request);
            // 2. PublishMessage成功
            echo "MessagePublished! \n";
            return true;
        } catch (MnsException $e) {
            // 3. 可能因为网络错误等原因导致PublishMessage失败，这里CatchException并做对应处理
            echo "PublishMessage Failed: " . $e . "\n";
            echo "MNSErrorCode: " . $e->getMnsErrorCode() . "\n";
            return false;
        }
    }
}
