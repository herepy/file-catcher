## 简介

一个可以抓取固定url模式的下载器，可根据自身需求自定义使用的下载器

## 使用条件

* php >= 5.6.0
* unix操作系统
* php开启curl拓展

## 安装说明

##### git方式安装:
```git
    git clone https://github.com/herepy/file-catcher.git
    cd file-catcher && composer install
```
##### composer方式安装
```comopser
    composer require pengyu/file-catcher
```

## 使用

```
$client=new Pengyu\FileCatcher\Master();
//url前缀
$urlPrefix="http://abc.com/path/";
//文件保存目录
$savePath="/var/img";
$client->init($urlPrefix,$savePath);

//url模式参数
$option=[
    "x" =>  [
        "type"  =>  "exact", //固定值类型
        "value" =>  3
    ],
    "y" =>  [
        "type"  =>  "range", //区间值类型,0,1,2,3,4,5
        "value" =>  [0,5]
    ],
    "z" =>  [
        "type"  =>  "in", //范围值类型，0,2,5
        "value" =>  [0,2,5]
    ],
];

//url命名变量使用{{variable}}格式表示
$client->setNameModel("{{x}}_{{y}}_{{z}}.jpg",$option);
$client->run();
```
