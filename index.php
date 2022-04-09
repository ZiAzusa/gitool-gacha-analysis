<?php
$htmlBackground = 'https://iw233.cn/api/Random.php';//自定义背景图片，可以设置为任意图片或随机图片的API，默认无背景
$htmlBgBlur = 0;//自定义背景高斯模糊，单位px
$htmlBgOpacity = 1;//自定义背景透明度，区间为0-1，0为完全透明，1为完全不透明
$htmlIcon = './icon.ico';//自定义图标
if ($_GET['download'] != null){
    $downloadInfo = json_decode(urldecode($_GET['download']), true);
    if ($downloadInfo['json'] != null){
        if ($_SERVER["SERVER_PORT"] != ("80" || "443")) $serverPort = ":".$_SERVER["SERVER_PORT"];
        $downloadURL = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['SERVER_NAME'].$serverPort.dirname($_SERVER['PHP_SELF'])."/".$downloadInfo['json'];
    }else if ($downloadInfo['url'] != null){
        $downloadURL = $downloadInfo['url'];
    }else{
        exit;
    };
    header('Content-Type: application/force-download');
    header('Content-Disposition: attachment; filename="'.basename($downloadURL).'"');
    header('Content-Transfer-Encoding: binary');
    header('Connection: close');
    readfile($downloadURL);
    exit;
};
if ($_POST['url'] != null){
    $context = stream_context_create(['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json', 'content' => json_encode(['url' => $_POST['url']]), 'timeout' => 60]]);
    $htmlTable = file_get_contents('https://gitool.moeloli.cyou/gacha/api.php?type=table', false, $context);
    if ($htmlTableX = @json_decode($htmlTable, true)) $htmlTable = $htmlTableX['code']."<br>".$htmlTableX['message'];
};
$htmlBgHeader = get_headers($htmlBackground, 1);
if ($htmlBgHeader['Location'] != null) $htmlBackground = $htmlBgHeader['Location'];
if ($htmlBgHeader['location'] != null) $htmlBackground = $htmlBgHeader['location'];
$bgDownload = urlencode(json_encode(['url' => $htmlBackground]));
?>
<html>
<head>
    <meta http-equiv'Content-Type' content='text/html; charset=utf-8'>
    <meta name='viewport' content='width=device-width,maximum-scale=1.0, minimum-scale=1.0, user-scalable=no'>
    <title>原神抽卡记录分析工具</title>
    <link href='{$htmlIcon}' rel='icon' type='image/x-icon' />
    <style>
    <?php print_r(file_get_contents("index.css"));?>
    .overlay:before {
        background:url(<?php print_r($htmlBackground);?>) no-repeat;
        background-size:cover;
        background-position:center 0;
        width:100%;
        height:100%;
        content:"";
        position:absolute;
        top:0;
        left:0;
        z-index:-1;
        -webkit-filter:blur(3px);
        filter:blur(<?php print_r($htmlBgBlur);?>px);
        opacity:<?php print_r($htmlBgOpacity);?>;
        margin:0;
        padding:0;
        position:fixed;
    }
    </style>
</head>
<body>
    <div id='overlay' class='overlay'>
        <div class='text-bg'>
            <div class='input_control'>
                <form method='post'>
                    <h4>请在下方文本框粘贴抽卡记录地址:</h4>
                    <textarea id='url' name='url' style='min-width:100%;max-width:100%;min-height:15em'><?php print_r($_POST['url']);?></textarea>
                    <input id='submit' type='submit' value='开始分析抽卡记录',name='submit' onclick="alert('请稍作等待，我们正在获取您的全部抽卡记录并分析！切勿刷新页面，这可能会导致存储在本地的数据出现错误！');">
                </form><hr>
                <?php print_r($htmlTable);?>
                <footer id='footer'>
                    <p class='copyright'>2022 &copy; Powered by 0803QwQ</p>
                </footer>
            </div>
        </div>
        <div id='dlbg' class='dlbg'>
            <a href="JavaScript:openBg()">
                <button id='download'>获取背景</button>
            </a>
        </div>
        <div id='imgLayer' onclick="closeBg()" />
        <div id='imgBoxl' class='modal'>
            <a href="JavaScript:download('<?php print_r($bgDownload);?>')">
                <img id='bigimg' src='<?php print_r($htmlBackground);?>' title='点击图片以保存\n点击空白处以关闭' />
            </a>
        </div>
    </div>
    <script src="index.js"></script>
</body>
</html>
