<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Sms\Controller;


use Common\Controller\AdminBase;
use Sms\Service\SmsService;

class IndexController extends AdminBase {

    protected $operatorModel;

    //初始化
    protected function _initialize() {
        parent::_initialize();
        $this->operatorModel = M('smsOperator');
    }

    /**
     * 展示短信平台列表页面
     */
    public function operators() {
        $this->display();
    }

    /**
     * 展示短信平台模型页面
     */
    public function model() {
        $operator = I('get.operator');
        $this->assign("operator", $operator);
        $this->display();
    }

    /**
     * 展示配置短信模版页面
     */
    public function modules() {
        $this->display();
    }

    /**
     * 设置默认短信平台
     */
    public function choose() {

        $operator = I('get.operator', "", "trim");

        //取消所有平台的选中
        $data['enable'] = 0;
        $this->operatorModel->where(TRUE)->save($data);

        //启用所选平台
        $data['enable'] = 1;
        $this->operatorModel->where(array("tablename" => $operator))->save($data);

        $this->success("平台变更成功，使用前请确认平台配置");
    }

    /**
     * 获取短信平台
     */
    public function get_operators() {

        $operators = $this->operatorModel->select();

        $error = $this->operatorModel->getError();
        empty($error) ? $result['status'] = TRUE : $result['status'] = FALSE;
        $result['error'] = $error;
        $result['datas']['count'] = count($operators);
        $result['datas']['operators'] = $operators;

        $this->ajaxReturn($result);
    }

    /**
     * 获取表字段详细信息
     */
    public function get_fields() {
        //获取表字段
        $tablename = C('DB_PREFIX') . "sms_" . I('get.operator', "", "trim");
        $Model = new \Think\Model();
        $fields = $Model->query("show full fields from $tablename");
        unset($fields[0]);

        $result = array(
            'status' => TRUE,
            'datas' => array(
                'operator' => $this->operatorModel->where("tablename='%s'", I('get.operator'))->find(),
                'fields' => $fields,
            ),
        );

        $this->ajaxReturn($result);
    }

    /**
     * 获取记录及字段详细信息
     */
    public function get_modules() {

        //获取表字段
        $tablename = C('DB_PREFIX') . "sms_" . I('get.operator', "", "trim");
        $Model = new \Think\Model();
        $fields = $Model->query("show full fields from $tablename");
        unset($fields[0]);

        //获取记录
        $tablename = "sms_" . I('get.operator', "", "trim");
        $Model = M($tablename);
        if (I('get.id')) {
            $modules = $Model->find(I('get.id'));
        } else {
            $modules = $Model->select();
        }

        $result = array(
            'status' => TRUE,
            'data' => array(
                'operator' => $this->operatorModel->where("tablename='%s'", I('get.operator'))->find(),
                'fields' => $fields,
                'modules' => $modules,
            ),
        );

        $this->ajaxReturn($result);
    }

    /**
     * 添加短信平台
     */
    public function operator_add() {
        if (IS_POST) {
            $name = I('post.name', "", "trim");
            $tablename = I('post.tablename', "", "trim");
            $full_tablename = C('DB_PREFIX') . "sms_" . $tablename;
            $remark = I('post.remark', "", "trim");


            $data['name'] = $name;
            $data['tablename'] = $tablename;
            $data['remark'] = $remark;
            $data['enable'] = 0;

            if ($this->operatorModel->create($data)) {

                //新建短信平台配置表
                $sql = "CREATE TABLE `$full_tablename` (`id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                $Model = new \Think\Model();

                try {
                    if (FALSE === $Model->execute($sql)) {
                        throw new \Exception();
                    }
                    $this->operatorModel->add($data);

                    $this->success("创建平台成功，请前往平台设置字段。");

                } catch (\Exception $e) {

                    $this->error("创建短信平台失败");

                }
            } else {
                $this->error($this->operatorModel->getError());
            }


        } else {
            $this->display();
        }
    }

    /**
     * 删除平台表
     */
    public function operator_del() {

        $tablename = C('DB_PREFIX') . "sms_" . I('post.operator', "", "trim");

        $sql = "DROP TABLE IF EXISTS `$tablename`;";
        $Model = new \Think\Model();

        try {
            if (FALSE === $Model->execute($sql)) {
                throw new \Exception();
            }

            $where['tablename'] = I('post.operator', "", "trim");
            $this->operatorModel->where($where)->delete();

            $this->ajaxReturn(array(
                'status' => TRUE,
            ));

        } catch (\Exception $e) {

            $this->ajaxReturn(array(
                'status' => FALSE,
            ));

        }
    }

    /**
     * 添加字段
     */
    public function field_add() {

        $tablename = C('DB_PREFIX') . "sms_" . I("post.operator", "", "trim");
        $fieldname = trim(I('post.name', "", "trim"));
        $default = trim(I('post.default', "", "trim"));
        $comment = trim(I('post.comment', "", "trim"));

        $sql = "ALTER TABLE `$tablename` ADD `$fieldname` VARCHAR(255) DEFAULT '$default' COMMENT '$comment'";
        $Model = new \Think\Model();

        FALSE === $Model->execute($sql) ? $result = array(
            'status' => FALSE,
        ) : $result = array(
            'status' => TRUE,
        );

        $this->ajaxReturn($result);
    }

    /**
     * 删除字段
     */
    public function field_del() {

        $tablename = C('DB_PREFIX') . "sms_" . I("post.operator", "", "trim");

        $field = I('post.field', "", "trim");

        $sql = "alter table `$tablename` drop column `$field`";

        $Model = new \Think\Model();

        FALSE === $Model->execute($sql) ? $result = array(
            'status' => FALSE,
        ) : $result = array(
            'status' => TRUE,
        );

        $this->ajaxReturn($result);
    }

    /**
     * 添加模板
     */
    public function module_add() {

        //去除多余的空格字符
        foreach ($_POST as $k => $v) {
            $_POST[$k] = trim($v);
        }

        try {
            $tableName = "sms_" . $_POST['operator'];
            $table = M($tableName);

            if ($table->create($_POST) !== FALSE) {
                $table->add($_POST);
                $this->ajaxReturn(array(
                    'status' => TRUE,
                ));
            } else {
                $this->ajaxReturn(array(
                    'status' => FALSE,
                    'error' => $table->getError(),
                ));
            }
        } catch (\Exception $e) {
            $this->ajaxReturn(array(
                'status' => FALSE,
                'error' => $e->getMessage(),
            ));
        }
    }

    /**
     * 删除模板
     */
    public function module_del() {

        $tableName = "sms_" . I('post.operator', NULL);
        $id = I('post.id', NULL);
        try {
            $table = M($tableName);

            if (FALSE !== $table->delete($id)) {
                $this->ajaxReturn(array(
                    'status' => TRUE,
                ));
            } else {
                $this->ajaxReturn(array(
                    'status' => FALSE,
                    'error' => $table->getError(),
                ));
            }
        } catch (\Exception $e) {
            $this->ajaxReturn(array(
                'status' => FALSE,
                'error' => $e->getMessage(),
            ));
        }
    }

    /**
     * 修改模板
     */
    public function module_edit() {

        if (IS_POST) {

            //去除多余的空格字符
            foreach ($_POST as $k => $v) {
                $_POST[$k] = trim($v);
            }

            $tableName = "sms_" . I('post.operator', NULL);
            unset($_POST['operator']);

            try {
                $Model = M($tableName);

                if (FALSE !== $Model->save($_POST)) {
                    $this->ajaxReturn(array(
                        'status' => TRUE,
                    ));
                } else {
                    $this->ajaxReturn(array(
                        'status' => FALSE,
                        'error' => $Model->getError(),
                    ));
                }
            } catch (\Exception $e) {

                $this->ajaxReturn(array(
                    'status' => FALSE,
                    'error' => $e->getMessage(),
                ));
            }

        } else if (IS_GET) {
            $this->display();
        }
    }

    /**
     * 发送日志
     */
    function log() {
        $this->display();
    }

    /**
     * 获取日志
     */
    public function get_log() {
        $page = I('page', 1);
        $limit = I('limit', 20);
        $where = [];
        if (I('get.start') || I('get.end')) {
            $start = I('get.start', null, 'timeFormat');
            $end = I('get.start', time(), 'timeFormat');
            $where['inputtime'] = array('BETWEEN', [$start, $end]);
        }

        $count = M('smsLog')->where($where)->count();
        $total_page = ceil($count / $limit);
        $Logs = M('smsLog')->where($where)->page($page)->limit($limit)->order('id desc')->select();

        $data = [
            'items' => $Logs,
            'page' => $page,
            'limit' => $limit,
            'total_page' => $total_page,
        ];
        $this->ajaxReturn(self::createReturn(true, $data, '网络繁忙。。'));

    }

    /**
     * 测试发送短信模板页
     */
    function testSend(){
        $this->display();
    }

    /**
     * 发送测试短信操作
     * @throws \AlibabaCloud\Client\Exception\ClientException
     */
    function doTestSend(){
        $template_id = I('post.template_id');
        $to = I('post.phone');
        $operator = I('post.operator');
        $param = I('post.param');
        if($operator == 'alibabacloud_abroad'){
            //发送阿里云国际版（国际短信）
            SmsService::sendAlibabacloudAbroad($template_id,$to,$param);
        } else if($operator == 'alibabacloud_mainland'){
            //发送阿里云国际版（大陆短信）
            SmsService::sendAlibabacloudMainland($template_id, $to, $param);
        } else {
            SmsService::sendSms($template_id, $to, $param, 'test', $operator);
        }
        $this->ajaxReturn(self::createReturn(true, null, '发送操作完成'));
    }
}