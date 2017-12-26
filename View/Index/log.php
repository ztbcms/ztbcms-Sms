<?php if (!defined('CMS_VERSION')) {
    exit();
} ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
    <Admintemplate file="Common/Nav"/>
    <div id="app">
        <div class="h_a">发送日志</div>

        <table class="table">
            <tr>
                <th style="width: 10%">ID</th>
                <th style="width: 10%">发送平台</th>
                <th style="width: 15%">短信模版</th>
                <th style="width: 15%">接收人</th>
                <th style="width: 15%">短信参数</th>
                <th style="width: 10%">发送时间</th>
                <th style="width: 15%">发送结果</th>
            </tr>
            <tr v-for="(log, key) in logs">
                <th>{{ log.id }}</th>
                <th>{{ log.operator }}</th>
                <th @click="showJson(log.template)">{{ log.template }}</th>
                <th>{{ log.recv }}</th>
                <th @click="showJson(log.param)">{{ log.param }}</th>
                <th>{{ log.sendtime|dataFormat }}</th>
                <th @click="showJson(log.result)">{{ log.result }}</th>
            </tr>
        </table>

        <!-- Modal -->
        <div class="modal fade" id="paramModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">json</h4>
                    </div>
                    <div class="modal-body">
                        <code>{</code><br>
                        </tba><div v-for="(item,key) in param" :key="key">
                            &nbsp;&nbsp;<code>"{{ key }}" : </code>
                            <template v-if="'object' !== typeof(param[key])">
                                <code>"{{ param[key] }}",</code>
                            </template>
                            <template v-else >
                                <br>&emsp;&nbsp;&nbsp;&nbsp;<code>{</code>
                                <template v-for="(i,k) in param[key]" :k="k">
                                    <br>&emsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<code>"{{ k }}" : "{{ param[key][k] }}",</code>
                                </template>
                                <br>&emsp;&nbsp;&nbsp;&nbsp;<code>}</code>
                            </template>
                        </div>
                        <code>}</code><br>
                    </div>
                </div>
            </div>
        </div>
        <!-- 分页 -->
        <div style="text-align: center">
            <ul class="pagination pagination-sm no-margin">
                <button @click="toPage( parseInt(where.page) - 1 )" class="btn btn-primary">上一页</button>
                {{ where.page }} / {{ total_page }}
                <button @click="toPage( parseInt(where.page) + 1 )" class="btn btn-primary">下一页</button>
                <span style="line-height: 30px;margin-left: 10px;"><input id="ipt_page"
                                                                          style="width:50px;text-align: center;"
                                                                          type="text" v-model="temp_page"></span>
                <span><button class="btn btn-primary" @click="toPage( temp_page )">跳转</button></span>
            </ul>
        </div>
    </div>
</div>

<link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css"/>
<script src="//cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="//cdn.bootcss.com/vue/2.1.5/vue.js"></script>
<script>
    new Vue({
        el: "#app",
        data: {
            where: {
                page: 1,
                limit: 20,
            },
            logs: [],
            param: [],
            temp_page: 1,
            total_page: 0,
        },
        filters: {
            dataFormat: function (time) {
                var day = new Date(time * 1000);
                return day.getFullYear() + '-' + (day.getMonth() + 1) + '-' + day.getDate()
            }
        },
        methods: {
            getData: function () {
                var vm = this;
                $.get("{:U('Sms/Index/get_log')}", vm.where, function (data) {
                    if (data.status) {
                        vm.logs = data.data.items;
                        vm.page = data.data.page;
                        vm.total_page = data.data.total_page;
                        vm.limit = data.data.limit;
                    } else {
                        alert('网络繁忙');
                    }
                }, 'json');
            },
            toPage: function(page){
                this.where.page = page;
                if (this.where.page < 1){
                    this.where.page = 1;
                }
                if (this.where.page > this.total_page){
                    this.where.page = this.total_page;
                }
                this.getData();
            },
            showJson: function (param) {
                this.param = JSON.parse(param);
                $('#paramModel').modal('show');
            }
        },
        mounted: function () {
            $(this.$el).removeClass('hidden');
            this.getData();
        }
    });
</script>
</body>
</html>
