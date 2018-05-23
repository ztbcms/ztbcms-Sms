<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">

<div class="wrap J_check_wrap" id="app" v-cloak>
    <Admintemplate file="Common/Nav"/>
    <div class="h_a">短信发送测试</div>
    <form id="form" class="J_ajaxForm">
        <div class="table_full">
            <table width="100%" class="table_form contentWrap">
                <tr>
                    <th width="120px;"><strong>发送平台</strong></th>
                    <td>
                        <template v-if="operatorData && operatorData.operator && operatorData.operator.name">
                            {{ operatorData.operator.name }}
                        </template>
                    </td>
                </tr>
                <tr>
                    <th  width="120px;"><strong>短信模板</strong></th>
                    <td>
                        <template v-if="templateData && templateData.template">
                            {{ templateData.template }}
                        </template>
                    </td>
                </tr>

                <tr>
                    <th><strong>发送参数</strong></th>
                    <td>
                        <p>手机号码:<input v-model="phone" type="text" class="input" ></p>
                        <p>自定义参数：<button @click="clickAddParam" type="button" class="btn btn-success">新增</button></p>
                        <template v-for="(param, index) in params">
                            <p>
                                <input v-model="param.key" type="text" class="input" placeholder="模板变量名">:<input  v-model="param.value" type="text" class="input" placeholder="模板变量值">
                                <button @click="deleteParam(param, index)" type="button" class="btn btn-danger">删除</button>
                            </p>
                        </template>
                    </td>
                </tr>

                <tr>
                    <th><strong>发送内容预览</strong></th>
                    <td>
                        <template v-if="templateData && templateData.content">
                            {{ previewContent }}
                        </template>
                    </td>
                </tr>
            </table>
            <div style="margin-top:1rem;">
                <button @click="doSendMessage" class="btn btn_submit mr10 J_ajax_submit_btn" type="button">确认发送</button>
            </div>
        </div>
    </form>

</div>

<script>
    $(document).ready(function () {
        new Vue({
            el: "#app",
            data: {
                operator: "{:I('get.operator', '')}",
                template_id: "{:I('get.template_id', '')}",
                operatorData: null,
                templateData: null,
                params: [],
                phone: ''
            },
            computed: {
                previewContent: function(){
                    if(this.templateData && this.templateData.content){
                        var result = this.templateData.content;
                        this.params.forEach(function(param, index){
                            result = result.replace('${' + param.key + '}', param.value);
                        });

                        return result;
                    }else{
                        return '';
                    }
                }
            },
            methods: {
                //获取发送渠道信息
                fetchOperatorData: function(){
                    var that = this;
                    if(that.operator === '' || that.template_id === '' ){
                        return;
                    }
                    $.ajax({
                        url: "{:U('Sms/Index/get_modules')}&operator=" + that.operator + '&id='+ that.template_id,
                        dataType: 'json',
                        success: function(res){
                            if(res.status){
                                that.operatorData = res.data;
                                that.templateData = res.data.modules;
                            }
                        }
                    })
                },
                clickAddParam: function(){
                    this.params.push({
                        key: '',
                        value: ''
                    })
                },
                deleteParam: function(param, index){
                    this.params.splice(index, 1);
                },
                doSendMessage: function(){
                    var that = this;
                    if(that.phone.length !== 11 ){
                        layer.msg('请正确填写手机号码');
                        return;
                    }

                    var param_result = {};
                    this.params.forEach(function(param){
                        param_result[param.key] = param.value;
                    });

                    var data = {
                        phone: that.phone,
                        operator: that.operator,
                        template_id: that.template_id,
                        param: param_result
                    };

                    $.ajax({
                        url: "{:U('Sms/Index/doTestSend')}",
                        type: 'post',
                        data: data,
                        dataType: 'json',
                        success: function(res){
                            if(res.status){
                                layer.msg('发送操作完成');
                            }else{
                                layer.msg('网络繁忙,请稍后再试');
                            }
                        }
                    });
                }
            },
            mounted: function () {
                this.fetchOperatorData();
            }
        });
    });
</script>
</body>
</html>
