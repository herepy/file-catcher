<?php
/**
 * Created by PhpStorm.
 * User: pengyu
 * Date: 2019/12/27
 * Time: 16:11
 */

namespace FileCatcher;

use FileCatcher\Downloader\AbstractDownloader;

class Worker
{
    /**
     * url前缀
     * @var string
     */
    private $urlPrefix;

    /**
     * 任务文件名列表
     * @var array
     */
    private $fileNames=[];

    /**
     * 文件保存路径
     * @var string
     */
    private $savePath;

    /**
     * 成功下载数量
     * @var int
     */
    private $count;

    /**
     * 下载失败文件url列表
     * @var array
     */
    private $failUrls=[];

    /**
     * 下载器
     * @var AbstractDownloader
     */
    protected $downloader;

    /**
     * 初始化
     * @param $urlPrefix
     * @param $fileNames
     * @param $savePath
     * @param $downloader
     */
    public function init($urlPrefix,$fileNames,$savePath,$downloader)
    {
        $this->urlPrefix=$urlPrefix;
        $this->fileNames=$fileNames;
        $this->savePath=$savePath;
        $this->downloader=$downloader;
    }

    /**
     * 运行
     */
    public function run()
    {
        foreach ($this->fileNames as $name) {
            $url=$this->urlPrefix.$name;
            if(!$this->download($name,$url)) {
                $this->failUrls[$name]=$url;
                continue;
            }
            $this->count++;
            usleep(50);
        }

        if (count($this->failUrls) != 0) {
            $this->reTry();
        }
        $this->stat();
    }

    /**
     * 结果概要
     */
    private function stat()
    {
        $content="success:".$this->count;
        if (count($this->failUrls) != 0) {
            foreach ($this->failUrls as $url) {
                $content.="\r\n".$url;
            }
        }
        $pid=posix_getpid();
        file_put_contents($this->savePath."/{$pid}-stat.txt",$content);
    }

    /**
     * 下载单个文件
     * @param $name
     * @param $url
     * @return bool|string
     */
    private function download($name,$url)
    {
        return $this->downloader->download($name,$url);
    }

    /**
     * 重试失败的文件
     */
    private function reTry()
    {
        foreach ($this->failUrls as $name => $url) {
            if ($this->download($name,$url)) {
                unset($this->failUrls[$name]);
            }
        }
    }

}