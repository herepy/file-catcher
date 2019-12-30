<?php
/**
 * Created by PhpStorm.
 * User: pengyu
 * Date: 2019/12/30
 * Time: 15:35
 */

namespace FileCatcher\Downloader;

abstract class AbstractDownloader
{
    /**
     * 保存目录
     * @var string
     */
    protected $dir;

    /**
     * 设置保存目录
     * @param $dir
     */
    public function setSaveDir($dir)
    {
        $this->dir=$dir;
    }

    /**
     * 下载单个文件
     * @param $name
     * @param $url
     * @return mixed
     */
    abstract public function download($name,$url);

}