<?php

use Germey\Geetest\Geetest;
use Illuminate\Console\Scheduling\Event;

class GeetestTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $user_id = 'testGeetest';

    /**
     * Test something true.
     */
    public function testProcess()
    {
        $user_id = $this->user_id;
        Geetest::shouldReceive('preProcess')->once()->with($user_id)->andReturn();
    }

    /**
     * Test response str.
     */
    public function testResponseStr()
    {
        Geetest::shouldReceive('getResponseStr')->once()->with()->andReturn();
    }

    /**
     * Test render.
     */
    public function testRender()
    {
        Geetest::shouldReceive('render')->once()->with()->andReturn();
        Geetest::shouldReceive('render')->once()->with('popup')->andReturn();
        Geetest::shouldReceive('render')->once()->with('embed')->andReturn();
    }

}

