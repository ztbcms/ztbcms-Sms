## 环境依赖
composer 依赖
```shell
php 5.x
```

## 部署步骤

在本地模块进行安装 确保Install目录存在

## 目录结构描述
```shell
D:.
├─Common
├─Controller
├─Install
├─Lib
│  ├─AlibabaCloud  阿里云国籍版模块
│  ├─Aliyun  阿里云模块
│  └─Ucpaas
├─Service
├─Uninstall
└─View
    └─Index
```

##版本内容更新


##### 版本号 ： 3.0.5.2 （2020年8月6日）

功能  | 介绍  
 ---- | ----- 
 初始化项目  | 完善项目的文档说明，添加基本的目录结构介绍 
 
<br> 
<br> 

##### 版本号 ： 3.0.5.3 （2020年8月6日）

功能  | 介绍  
 ---- | ----- 
 短信模块接口进行版本升级，去云之讯等旧短信模块  | areaCode 区号 <br><br>phone 手机号 <br><br>phone 手机号 <br><br>alias 别名 <br><br>operator 平台 <br><br>param 参数 <br><br>interval_time 间隔时间  <br><br>调用方法 ： \Sms\Service\SmsService::sendSmsV2($areaCode,$phone,$alias,$operator,$param,$interval_time);
 

