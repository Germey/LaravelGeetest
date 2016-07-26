<?php

use Germey\Geetest\Geetest;

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
        $status = Geetest::preProcess($user_id);
        $this->assertTrue($status);
    }

    /**
     * Test response str.
     */
    public function testResponseStr()
    {
        $user_id = $this->user_id;
        $status = Geetest::preProcess($user_id);
        Session::put('gtserver', $status);
        Session::put('user_id', $user_id);
        $this->assertJson(Geetest::getResponseStr());
    }

}
