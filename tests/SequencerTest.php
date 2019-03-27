<?php

namespace HughCube\CNNumber\Tests;

use HughCube\SwooleSequencer\Sequencer;
use PHPUnit\Framework\TestCase;

class SequencerTest extends TestCase
{
    /**
     * @return Sequencer
     */
    public function testInstance()
    {
        $sequencer = new Sequencer();

        $this->assertInstanceOf(Sequencer::class, $sequencer);

        return $sequencer;
    }

    /**
     * @param Sequencer $sequencer
     * @return Sequencer
     * @depends testInstance
     */
    public function testGetId(Sequencer $sequencer)
    {
        $lastId = 0;
        for($i = 1; $i <= 100000; $i++){

            if (0 === $i % 10000){
                sleep(1);
            }

            $id = $sequencer->getId();
            $this->assertGreaterThan($lastId, $id);

            $lastId = $id;
        }
    }
}
