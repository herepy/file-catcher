<?php
/**
 * Created by PhpStorm.
 * User: pengyu
 * Date: 2019/12/27
 * Time: 16:53
 */

namespace FileCatcher;

use FileCatcher\Downloader\AbstractDownloader;

class Master
{
    /**
     * url前缀
     * @var string
     */
    private $urlPrefix;

    /**
     * 任务文件名
     * @var array
     */
    private $fileNames=[];

    /**
     * 保存目录
     * @var string
     */
    private $savePath;

    /**
     * 命名参数
     * @var string
     */
    private $nameModel;

    /**
     * 命名参数格式数据
     * @var array
     */
    private $option;

    /**
     * 各个命名参数的可用值
     * @var array
     */
    private $valueBox;

    /**
     * 当前在运行的工作进程数
     * @var int
     */
    private $currentWorkerCount;

    /**
     * 下载器
     * @var AbstractDownloader
     */
    protected $downloader;

    /**
     * 每个工作进程分配的任务数
     */
    const countPerWorker=250;


    /**
     * 初始化
     * @param $urlPrefix
     * @param $savePath
     * @param $downloader
     */
    public function init($urlPrefix,$savePath,$downloader=null)
    {
        $this->urlPrefix=$urlPrefix;
        $this->savePath=$savePath;
        if (!file_exists($this->savePath)) {
            mkdir($this->savePath);
        }

        if ($downloader) {
            $this->downloader=$downloader;
        } else {
            $this->downloader=new Downloader\Downloader();
        }
        $this->downloader->setSaveDir($this->savePath);
    }

    /**
     * 设置命名参数
     * @param $model {{x}}_{{y}}_{{z}}.jpg
     * @param $option ["x"=>["type"=>"exact","value"=>[0,10]],"y"=>["type"=>"range","value"=>[0,15]],"z"=>["type"=>"in","value"=>[0,3,6]]]
     */
    public function setNameModel($model,$option)
    {
        $this->nameModel=$model;
        $this->option=$option;
    }

    /**
     * 解析命名参数
     * @throws Exception
     */
    private function parseNameModel()
    {
        if (!$this->nameModel || !$this->option) {
            throw new Exception("please set name model and options use function setNameModel");
        }

        $pattern="/\{\{\w\}\}/";
        $res=preg_match_all($pattern,$this->nameModel,$matches);
        if (!$res) {
            throw new Exception("name model has incorrect format");
        }

        foreach ($this->option as $key => $item) {
            $this->valueBox[$key]=$this->initValue($item["type"],$item["value"]);
        }
    }

    /**
     * 生成并获取命名参数的所有值
     * @param $type
     * @param $value
     * @return mixed
     * @throws Exception
     */
    private function initValue($type,$value)
    {
        switch ($type) {
            case "exact":
                return [$value];
            case "in":
                return $value;
            case "range":
                return range($value[0],$value[1]);
            default:
                throw new Exception("incorrect type:".$type);
        }
    }

    /**
     * 生成要下载的文件名列表
     */
    private function createFileNames()
    {
        $nameList=array_keys($this->valueBox);
        switch (count($this->valueBox)) {
            case 1:
                $pattern="/\{\{".$nameList[0]."\}\}/";
                foreach ($this->valueBox[0] as $value) {
                    $fileName=preg_replace($pattern,$value,$this->nameModel);
                    $this->fileNames[]=$fileName;
                }
                return;
            case 2:
                foreach ($this->valueBox[$nameList[0]] as $valueA) {
                    $pattern="/\{\{".$nameList[0]."\}\}/";
                    $fileName=preg_replace($pattern,$valueA,$this->nameModel);
                    foreach ($this->valueBox[$nameList[1]] as $valueB) {
                        $pattern="/\{\{".$nameList[1]."\}\}/";
                        $this->fileNames[]=preg_replace($pattern,$valueB,$fileName);;
                    }
                }
                return;
            case 3:
                foreach ($this->valueBox[$nameList[0]] as $valueA) {
                    $pattern="/\{\{".$nameList[0]."\}\}/";
                    $fileNameA=preg_replace($pattern,$valueA,$this->nameModel);
                    foreach ($this->valueBox[$nameList[1]] as $valueB) {
                        $pattern="/\{\{".$nameList[1]."\}\}/";
                        $fileNameB=preg_replace($pattern,$valueB,$fileNameA);
                        foreach ($this->valueBox[$nameList[2]] as $valueC) {
                            $pattern="/\{\{".$nameList[2]."\}\}/";
                            $this->fileNames[]=preg_replace($pattern,$valueC,$fileNameB);;
                        }
                    }
                }
                return;
            default:
                throw new Exception("name model has too many variable");
        }
    }

    /**
     * 创建工作进程执行任务
     */
    private function makeWorker()
    {
        $taskList=array_chunk($this->fileNames,self::countPerWorker);
        echo "now is running ".count($taskList)." workers to download file...".PHP_EOL;
        foreach ($taskList as $task) {
            $pid=pcntl_fork();
            if ($pid == -1) {
                throw new Exception("fork fail");
            } else if ($pid == 0) {  //child
                $worker=new Worker();
                $worker->init($this->urlPrefix,$task,$this->savePath,$this->downloader);
                $worker->run();
                $workerPid=posix_getpid();
                echo "worker {$workerPid} run ok".PHP_EOL;
                exit(0);
            }
            $this->currentWorkerCount++;
        }

    }

    /**
     * 运行
     */
    public function run()
    {
        $this->parseNameModel();
        $this->createFileNames();
        $this->makeWorker();
        $this->monitor();
    }

    /**
     * 监控回收工作进程
     */
    private function monitor()
    {
        while (true) {
            $pid=pcntl_wait($status,WUNTRACED);
            if ($pid > 0) {
                $this->currentWorkerCount--;
            } else {
                echo "get error".PHP_EOL;
            }

            if ($this->currentWorkerCount == 0) {
                break;
            }
        }
        echo "run over".PHP_EOL;
    }

}