<?php

namespace HughCube\SwooleSequencer;

use Swoole\Atomic;
use Swoole\Lock;

class Sequencer
{
    /**
     * @var Atomic
     */
    protected $sequencer;

    /**
     * @var Atomic
     */
    protected $lastTimestamp;

    /**
     * @var Lock
     */
    protected $locker;

    /**
     * @var integer 二进制进程id
     */
    protected $binWorkId;

    /**
     * @var integer 每秒最大发号数
     */
    protected $maxSequence;

    /**
     * Server constructor
     */
    public function __construct($workId = 0, $maxSequence = 2097151)
    {
        $this->binWorkId = $this->baseConvert($workId, 10, 2);
        $this->binWorkId = str_pad($this->binWorkId, 10, '0', STR_PAD_LEFT);

        $this->maxSequence = $maxSequence;

        $this->sequencer = new Atomic(0);
        $this->lastTimestamp = new Atomic(0);
        $this->locker = new Lock(SWOOLE_MUTEX);
    }

    /**
     * 获取worKid为以后留一个口
     *
     * @return string
     */
    public function getWorkId()
    {
        $binWorkId = ltrim($this->binWorkId, '0');

        return $this->baseConvert($binWorkId, 2, 10);
    }

    /**
     * 每秒最大发号数
     *
     * @return string
     */
    protected function getMaxSequence()
    {
        return $this->maxSequence;
    }

    public function getId()
    {
        $this->locker->lock();

        Reset:

        /**
         * 如果不是当前秒, 重制发号器
         */
        $timestamp = time();
        if ($timestamp != $this->lastTimestamp->get()){
            $this->lastTimestamp->set($timestamp);
            $this->sequencer->set(-1);
        }

        /**
         * 如果当前时间的号发放完了, 等待
         */
        $sequence = $this->sequencer->add(1);
        if ($sequence > $this->getMaxSequence()){
            $microsecond = (($timestamp + 1) * 1000000) - implode('', explode(" ", microtime()));
            $microsecond > 0 AND usleep($microsecond);

            goto Reset;
        }

        $this->locker->unlock();

        /**
         * 时间 2019-01-01 00:00:00  起
         */
        $binTimestamp = $this->baseConvert(((string)($timestamp - 1546272000)), 10, 2);

        /**
         * WorkId 1 - 1024 10位
         */
        $binWorkId = $this->binWorkId;

        /**
         * 顺序号 1 - 131071    17位
         */
        $binSequence = $this->baseConvert(((string)$sequence), 10, 2);
        $binSequence = str_pad($binSequence, 17, '0', STR_PAD_LEFT);

        /**
         * 拼接
         */
        $binId = "{$binTimestamp}{$binWorkId}{$binSequence}";

        return $this->baseConvert($binId, 2, 10);
    }

    /**
     * 进制转换
     *
     * @param string $number
     * @param integer $frombase
     * @param integer $tobase
     * @return string
     */
    protected function baseConvert($number, $frombase, $tobase)
    {
        return base_convert($number, $frombase, $tobase);
    }
}
