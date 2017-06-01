<?php if (!defined('CMS_VERSION')) exit(); ?>
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
    </div>
</div>


<script src="//cdn.bootcss.com/vue/2.1.5/vue.min.js"></script>
<script>
    new Vue({
        el: "#app",
        data: {
            logs: []
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
                $.get("{:U('Sms/Index/get_log')}", null, function (data) {
                    if (data.status) {
                        vm.logs = data.data;
                    } else {
                        alert('网络繁忙');
                    }
                }, 'json');
            },
            showJson: function (json) {
                console.log(json)
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
