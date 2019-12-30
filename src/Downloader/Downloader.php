<?php
/**
 * Created by PhpStorm.
 * User: pengyu
 * Date: 2019/12/30
 * Time: 15:57
 */

namespace FileCatcher\Downloader;

class Downloader extends AbstractDownloader
{
    public function download($name, $url)
    {
        $f=fopen($this->dir."/".$name,"w");
        try {
            $ch=curl_init($url);
            curl_setopt($ch,CURLOPT_TIMEOUT,3);
            curl_setopt($ch,CURLOPT_FILE,$f);
            $result=curl_exec($ch);
            fflush($f);
        } catch (\Throwable $throwable) {
            $result=false;
        } finally {
            fclose($f);
            return $result;
        }
    }
}