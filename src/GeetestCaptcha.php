<?php namespace Germey\Geetest;


trait GeetestCaptcha
{
    /**
     * Get geetest.
     */
    public function getGeetest()
    {
        $user_id = "test";
        $status = Geetest::preProcess($user_id);
        session()->put('gtserver', $status);
        session()->put('user_id', $user_id);
        echo Geetest::getResponseStr();
    }
}