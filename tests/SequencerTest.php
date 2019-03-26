<?php

namespace HughCube\CNNumber\Tests;

use HughCube\SwooleSequencer\Sequencer;
use PHPUnit\Framework\TestCase;

class SequencerTest extends TestCase
{
    /**
     * @return Sequencer
     */
    public function getInstance()
    {
        return new Sequencer();
    }

    /**
     * @param Sequencer $sequencer
     * @return Sequencer
     * @depends getInstance
     */
    public function testGetId(Sequencer $sequencer)
    {
        $id = $sequencer->getId();

        $this->assertGreaterThan(0, $id);
    }
}
