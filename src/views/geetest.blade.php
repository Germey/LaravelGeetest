<script src="https://code.jquery.com/jquery-1.12.3.min.js"></script>
<script src="https://static.geetest.com/static/tools/gt.js"></script>
<div id="embed-captcha"></div>
<p id="wait" class="show">正在加载验证码...</p>
<script>
    var geetest = function(url) {
        var handlerEmbed = function(captchaObj) {
            $("#embed-captcha").closest('form').submit(function(e) {
                var validate = captchaObj.getValidate();
                if (!validate) {
                    alert('请正确完成验证码操作!', 'error');
                    e.preventDefault();
                }
            });
            captchaObj.appendTo("#embed-captcha");
            captchaObj.onReady(function() {
                $("#wait")[0].className = "hide";
            });
        };
        $.ajax({
            url: url + "?t=" + (new Date()).getTime(),
            type: "get",
            dataType: "json",
            success: function(data) {
                initGeetest({
                    gt: data.gt,
                    challenge: data.challenge,
                    product: "{{ $product }}",
                    offline: !data.success
                }, handlerEmbed);
            }
        });
    };
    (function() {
        geetest('{{ $geetest_url?$geetest_url:Illuminate\Support\Facades\Config::get('geetest.geetest_url') }}');
    })();
</script>