<?php namespace Germey\Geetest;

use Germey\Geetest\Geetest;

trait CaptchaGeetest
{
    /**
     * Get geetest.
     */
    public function getGeetest()
    {
        $user_id = "test";
        $status = Geetest::pre_process($user_id);
        session()->put('gtserver', $status);
        session()->put('user_id', $user_id);
        echo Geetest::get_response_str();
    }
}