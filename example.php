<?php
/**
 * Created by PhpStorm.
 * User: pengyu
 * Date: 2019/12/30
 * Time: 15:28
 */

require_once "vendor/autoload.php";

$client=new Pengyu\FileCatcher\Master();
$client->init("http://tiles.pano.vizen.cn/6E3912F598C4456583B68263BD4A267F/cube/","/vagrant/img");
$option=[
    "x" =>  [
        "type"  =>  "exact",
        "value" =>  3
    ],
    "y" =>  [
        "type"  =>  "range",
        "value" =>  [0,5],
        "option"=>  [
            "width"         =>  2,    //值的位数
            "placeholder"   =>  "0"   //不够位数补位占位符
        ]
    ],
    "z" =>  [
        "type"  =>  "range",
        "value" =>  [0,10],
        "option"=>  [
            "width"         =>  2,
            "placeholder"   =>  "0"
        ]
    ],
];
$client->setNameModel("{{x}}_{{y}}_{{z}}.jpg",$option);
$client->run();