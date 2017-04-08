<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    

    <title>跳转提示</title>
    <meta name="keywords" content="{$Think.config.site.keywords}">
    <meta name="description" content="{$Think.config.site.description}">

    <link rel="shortcut icon" href="favicon.ico"> <link href="{$Think.config.site.resource_url}css/bootstrap.min.css?v=3.3.5" rel="stylesheet">
    <link href="{$Think.config.site.resource_url}css/font-awesome.min.css?v=4.4.0" rel="stylesheet">

    <link href="{$Think.config.site.resource_url}css/animate.min.css" rel="stylesheet">
    <link href="{$Think.config.site.resource_url}css/style.min.css?v=4.0.0" rel="stylesheet">

</head>

<body class="gray-bg">
    <div class="middle-box text-center animated fadeInDown">
         <?php switch ($code) {?>
            <?php case 1:?>
            <h1>:)</h1>
            <?php break;?>
            <?php default:?>
            <h1>:(</h1>
            <?php break;?>
        <?php } ?>
        <h3 class="font-bold"><?php echo(strip_tags($msg));?></h3>

        <div class="error-desc">
            <p class="jump">
            页面自动 <a id="href" href="<?php echo($url);?>">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b>, <a id="href" href="/">跳转到首页</a>
			</p>
            <br/>
			<?php if (0 == $code) {?><a href="javascript:history.go(-1)" class="btn btn-primary m-t">返回</a> <?php } ?>
			<a href="<?php echo($url);?>" class="btn btn-info m-t">立即跳转</a>
        </div>
    </div>
    <script src="{$Think.config.site.resource_url}js/jquery.min.js?v=2.1.4"></script>
    <script src="{$Think.config.site.resource_url}js/bootstrap.min.js?v=3.3.5"></script>
<script type="text/javascript">
        (function(){
            var wait = document.getElementById('wait'),
                href = document.getElementById('href').href;
            var interval = setInterval(function(){
                var time = --wait.innerHTML;
                if(time <= 0) {
                    location.href = href;
                    clearInterval(interval);
                };
            }, 1000);
        })();
    </script>
</body>

</html>