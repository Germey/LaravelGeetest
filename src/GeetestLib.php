<?php namespace Germey\Geetest;

use Illuminate\Support\Facades\Config;

class GeetestLib
{
    /**
     * @var const
     */
    const GT_SDK_VERSION = 'php_3.2.0';

    /**
     * @var int
     */
    public static $connectTimeout = 0;

    /**
     * @var int
     */
    public static $socketTimeout = 20;

    /**
     * @var
     */
    private $response;

    /**
     * @var string
     */
    protected $geetest_url = '';

    /**
     * @var
     */
    protected $captcha_id;

    /**
     * @var
     */
    protected $private_key;

    /**
     * @return string
     */
    public function getGeetestUrl()
    {
        return $this->geetest_url;
    }

    /**
     * @param string $geetestUrl
     */
    public function setGeetestUrl($geetest_url)
    {
        $this->geetest_url = $geetest_url;
        return $this;
    }

    /**
     * GeetestLib constructor.
     */
    public function __construct()
    {
        $this->captcha_id = Config::get('geetest.geetest_id');
        $this->private_key = Config::get('geetest.geetest_key');
    }

    /**
     * Check Geetest server is running or not.
     *
     * @param null $user_id
     * @return int
     */
    public function preProcess($user_id = null)
    {
        $url = "http://api.geetest.com/register.php?gt=" . $this->captcha_id;
        if (($user_id != null) and (is_string($user_id))) {
            $url = $url . "&user_id=" . $user_id;
        }
        $challenge = $this->sendRequest($url);

        if (strlen($challenge) != 32) {
            $this->failbackProcess();

            return 0;
        }
        $this->successProcess($challenge);

        return 1;
    }

    /**
     * @param $challenge
     */
    private function successProcess($challenge)
    {
        $challenge = md5($challenge . $this->private_key);
        $result = array(
            'success' => 1,
            'gt' => $this->captcha_id,
            'challenge' => $challenge
        );
        $this->response = $result;
    }

    /**
     *
     */
    private function failbackProcess()
    {
        $rnd1 = md5(rand(0, 100));
        $rnd2 = md5(rand(0, 100));
        $challenge = $rnd1 . substr($rnd2, 0, 2);
        $result = array(
            'success' => 0,
            'gt' => $this->captcha_id,
            'challenge' => $challenge
        );
        $this->response = $result;
    }

    /**
     * @return mixed
     */
    public function getResponseStr()
    {
        return json_encode($this->response);
    }


    /**
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get success validate result.
     *
     * @param      $challenge
     * @param      $validate
     * @param      $seccode
     * @param null $user_id
     * @return int
     */
    public function successValidate($challenge, $validate, $seccode, $user_id = null)
    {
        if (! $this->checkValidate($challenge, $validate)) {
            return 0;
        }
        $data = array(
            "seccode" => $seccode,
            "sdk" => self::GT_SDK_VERSION,
        );
        if (($user_id != null) and (is_string($user_id))) {
            $data["user_id"] = $user_id;
        }
        $url = "http://api.geetest.com/validate.php";
        $codevalidate = $this->postRequest($url, $data);
        if ($codevalidate == md5($seccode)) {
            return 1;
        } else {
            if ($codevalidate == "false") {
                return 0;
            } else {
                return 0;
            }
        }
    }

    /**
     * Get fail result.
     *
     * @param $challenge
     * @param $validate
     * @param $seccode
     * @return int
     */
    public function failValidate($challenge, $validate, $seccode)
    {
        if ($validate) {
            $value = explode("_", $validate);
            $ans = $this->decodeResponse($challenge, $value['0']);
            $bg_idx = $this->decodeResponse($challenge, $value['1']);
            $grp_idx = $this->decodeResponse($challenge, $value['2']);
            $x_pos = $this->getFailbackPicAns($bg_idx, $grp_idx);
            $answer = abs($ans - $x_pos);
            if ($answer < 4) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }

    }

    /**
     * @param $challenge
     * @param $validate
     * @return bool
     */
    private function checkValidate($challenge, $validate)
    {
        if (strlen($validate) != 32) {
            return false;
        }
        if (md5($this->private_key . 'geetest' . $challenge) != $validate) {
            return false;
        }

        return true;
    }

    /**
     * GET
     *
     * @param $url
     * @return mixed|string
     */
    private function sendRequest($url)
    {

        if (function_exists('curl_exec')) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connectTimeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::$socketTimeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $data = curl_exec($ch);

            if (curl_errno($ch)) {
                $err = sprintf("curl[%s] error[%s]", $url, curl_errno($ch) . ':' . curl_error($ch));
                $this->triggerError($err);
            }

            curl_close($ch);
        } else {
            $opts = array(
                'http' => array(
                    'method' => "GET",
                    'timeout' => self::$connectTimeout + self::$socketTimeout,
                )
            );
            $context = stream_context_create($opts);
            $data = file_get_contents($url, false, $context);
        }

        return $data;
    }

    /**
     * @param       $url
     * @param array $postdata
     * @return mixed|string
     */
    private function postRequest($url, $postdata = '')
    {
        if (! $postdata) {
            return false;
        }

        $data = http_build_query($postdata);
        if (function_exists('curl_exec')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connectTimeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::$socketTimeout);

            //不可能执行到的代码
            if (! $postdata) {
                curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            } else {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            $data = curl_exec($ch);

            if (curl_errno($ch)) {
                $err = sprintf("curl[%s] error[%s]", $url, curl_errno($ch) . ':' . curl_error($ch));
                $this->triggerError($err);
            }

            curl_close($ch);
        } else {
            if ($postdata) {
                $opts = array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => "Content-type: application/x-www-form-urlencoded\r\n" . "Content-Length: " . strlen($data) . "\r\n",
                        'content' => $data,
                        'timeout' => self::$connectTimeout + self::$socketTimeout
                    )
                );
                $context = stream_context_create($opts);
                $data = file_get_contents($url, false, $context);
            }
        }

        return $data;
    }


    /**
     * Decode random para.
     *
     * @param $challenge
     * @param $string
     * @return int
     */
    private function decodeResponse($challenge, $string)
    {
        if (strlen($string) > 100) {
            return 0;
        }
        $key = array();
        $chongfu = array();
        $shuzi = array("0" => 1, "1" => 2, "2" => 5, "3" => 10, "4" => 50);
        $count = 0;
        $res = 0;
        $array_challenge = str_split($challenge);
        $array_value = str_split($string);
        for ($i = 0; $i < strlen($challenge); $i ++) {
            $item = $array_challenge[$i];
            if (in_array($item, $chongfu)) {
                continue;
            } else {
                $value = $shuzi[$count % 5];
                array_push($chongfu, $item);
                $count ++;
                $key[$item] = $value;
            }
        }

        for ($j = 0; $j < strlen($string); $j ++) {
            $res += $key[$array_value[$j]];
        }
        $res = $res - $this->decodeRandBase($challenge);

        return $res;
    }


    /**
     * @param $x_str
     * @return int
     */
    private function getXPosFromStr($x_str)
    {
        if (strlen($x_str) != 5) {
            return 0;
        }
        $sum_val = 0;
        $x_pos_sup = 200;
        $sum_val = base_convert($x_str, 16, 10);
        $result = $sum_val % $x_pos_sup;
        $result = ($result < 40) ? 40 : $result;

        return $result;
    }

    /**
     * @param $full_bg_index
     * @param $img_grp_index
     * @return int
     */
    private function getFailbackPicAns($full_bg_index, $img_grp_index)
    {
        $full_bg_name = substr(md5($full_bg_index), 0, 9);
        $bg_name = substr(md5($img_grp_index), 10, 9);

        $answer_decode = "";
        // 通过两个字符串奇数和偶数位拼接产生答案位
        for ($i = 0; $i < 9; $i ++) {
            if ($i % 2 == 0) {
                $answer_decode = $answer_decode . $full_bg_name[$i];
            } elseif ($i % 2 == 1) {
                $answer_decode = $answer_decode . $bg_name[$i];
            }
        }
        $x_decode = substr($answer_decode, 4, 5);
        $x_pos = $this->getXPosFromStr($x_decode);

        return $x_pos;
    }

    /**
     * Decode rand base by two random number.
     *
     * @param $challenge
     * @return mixed
     */
    private function decodeRandBase($challenge)
    {
        $base = substr($challenge, 32, 2);
        $tempArray = array();
        for ($i = 0; $i < strlen($base); $i ++) {
            $tempAscii = ord($base[$i]);
            $result = ($tempAscii > 57) ? ($tempAscii - 87) : ($tempAscii - 48);
            array_push($tempArray, $result);
        }
        $decodeRes = $tempArray['0'] * 36 + $tempArray['1'];

        return $decodeRes;
    }

    /**
     * @param $err
     */
    private function triggerError($err)
    {
        trigger_error($err);
    }

    /**
     * @param string $product
     */
    public function render($product = 'float')
    {
        return view('geetest::geetest', [
            'product' => $product,
            'geetest_url' => $this->geetest_url
        ]);
    }

}
