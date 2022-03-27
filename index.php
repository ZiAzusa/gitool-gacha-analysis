<?
$htmlBackground = 'https://iw233.cn/api/Random.php';//自定义背景图片，可以设置为任意图片或随机图片的API，默认无背景
$htmlBgBlur = 5;//自定义背景高斯模糊，单位px
$htmlBgOpacity = 0.9;//自定义背景透明度，区间为0-1，0为完全透明，1为完全不透明
$htmlIcon = './icon.ico';//自定义图标

if ($_GET['download'] != null){
    $setuIpInfo = json_decode(urldecode($_GET['download']), true);
    if ($setuIpInfo['url'] != null){
        $setuGetURL = $setuIpInfo['url'];
    }else{
        exit;
    };
    header('Content-Type: application/force-download');
    header('Content-Disposition: attachment; filename="'.basename($setuGetURL).'"');
    header('Content-Transfer-Encoding: binary');
    header('Connection: close');
    readfile($setuGetURL);
    exit;
};
if ($_POST['url'] != null){
    $context = stream_context_create(['http' => ['method' => 'POST', 'header' => 'Content-Type: application/json', 'content' => json_encode(['url' => $_POST['url']]), 'timeout' => 60]]);
    $htmlTable = file_get_contents('https://gitool.moeloli.cyou/gacha/api.php?type=table', false, $context);
};
$htmlBgHeader = get_headers($htmlBackground, 1);
if ($htmlBgHeader['Location'] != null) $htmlBackground = $htmlBgHeader['Location'];
if ($htmlBgHeader['location'] != null) $htmlBackground = $htmlBgHeader['location'];
$bgDownload = urlencode(json_encode(['url' => $htmlBackground]));
print_r("<html><head><meta http-equiv'Content-Type' content='text/html; charset=utf-8'><meta name='viewport' content='width=device-width,maximum-scale=1.0, minimum-scale=1.0, user-scalable=no'><title>原神抽卡记录分析工具</title><link href='{$htmlIcon}' rel='icon' type='image/x-icon' /><style>.overlay{z-index:2;display:flex;align-items:center;justify-content:center;}.overlay:before{background:url({$htmlBackground}) no-repeat;background-size:cover;background-position:center 0;width:100%;height:100%;content:\"\";position:absolute;top:0;left:0;z-index:-1;-webkit-filter:blur(3px);filter:blur({$htmlBgBlur}px);opacity:{$htmlBgOpacity};margin:0;padding:0;position:fixed;}.text-bg{background-color:rgba(255, 255, 255, 0.6);padding:24px;}.input_control{width:360px;margin:20px auto;}.dlbg{z-index:3;position:fixed;bottom:1px;right:1px;}#imgLayer{display:none;z-index:4;position:fixed;width:100%;height:100%;background:rgba(0,0,0,0.6);top:50%;left:50%;transform:translateX(-50%) translateY(-50%);}#imgBoxl{display:none;height:100%;z-index:5;position:fixed;margin:5%;}#bigimg{position:fixed;top:50%;left:50%;transform:translateX(-50%) translateY(-50%);}button[id='download']{color:rgb(0, 0, 0);border:2px solid rgb(0, 0, 0);cursor:pointer;}textarea[id='url'],#btn1,#btn2{box-sizing:border-box;text-align:left;font-size:0.5em;height:15em;border-radius:4px;border:1px solid #c8cccf;color:#6a6f77;-web-kit-appearance:none;-moz-appearance:none;display:block;outline:0;padding:0 1em;text-decoration:none;width:100%;}textareatextarea[id='url']:focus{border:1px solid #ff7496;}input[id='number']:focus{border:1px solid #ff7496;}input[id='submit']{width:360px;margin:20px auto;height:40px;border-width:0px;border-radius:3px;background:#1E90FF;cursor:pointer;outline:none;font-family:Microsoft YaHei;color:white;font-size:17px;-webkit-appearance:none;}input[id='submit']:hover{background:#5599FF;-webkit-appearance:none;}::-moz-placeholder {color:#6a6f77;}::-moz-placeholder{color:#6a6f77;}button[id='dljson']{width:360px;margin:20px auto;height:40px;border-width:0px;border-radius:3px;background:#FF901E;cursor:pointer;outline:none;font-family:Microsoft YaHei;color:white;font-size:17px;-webkit-appearance:none;}button[id='dljson']:hover{background:#FF9955;-webkit-appearance:none;}::-moz-placeholder {color:#6a6f77;}::-moz-placeholder{color:#6a6f77;}input::-webkit-input-placeholder{color:#6a6f77;}.notice{margin:10%auto0;background-color:rgba(255, 255, 255, 0.8);padding:2%5%}p{line-height:2}</style></head><body><div id='overlay' class='overlay'><div class='text-bg'><div class='input_control'><form method='post'><h4>请在下方文本框粘贴抽卡记录地址:</h4><textarea id='url' name='url' style='min-width:100%;max-width:100%'>{$_POST['url']}</textarea><input id='submit' type='submit' value='开始分析抽卡记录',name='submit' onclick=\"alert('请稍作等待，我们正在获取您的全部抽卡记录并分析！切勿刷新页面，这可能会导致存储在本地的数据出现错误！');\"></form><hr>{$htmlTable}<footer id='footer'><p class='copyright'>2022 &copy; Powered by 0803QwQ</p></footer></div></div><div id='dlbg' class='dlbg'><a href=\"JavaScript:openBg()\"><button id='download'>获取背景</button></a></div><div id='imgLayer' onclick=\"closeBg()\"></div><div id='imgBoxl' class='modal'><a href=\"JavaScript:download('{$bgDownload}')\"><img id='bigimg' src='{$htmlBackground}' title='点击图片以保存\n点击空白处以关闭'/></a></div><script>function download(info){alert('正在唤起浏览器下载...');location.href='?download='+info;};function openBg(){var imgLayer=document.getElementById(\"imgLayer\");var imgBoxl=document.getElementById(\"imgBoxl\");imgLayer.style.display=\"block\";imgBoxl.style.display=\"block\";imgSg();};function closeBg(){var imgLayer=document.getElementById(\"imgLayer\");var imgBoxl=document.getElementById(\"imgBoxl\");imgLayer.style.display=\"none\";imgBoxl.style.display=\"none\";};function imgSg(){var img=document.getElementById(\"bigimg\");var imgw=img.naturalWidth;var imgh=img.naturalHeight;var userw=document.body.clientWidth;var userh=document.body.clientHeight;if (imgw>=(userw * 0.8) && imgh<=(userh * 0.8)){img.style.width=\"80%\";img.style.height=\"auto\";}else if (imgh>=(userh * 0.8)){img.style.width=\"auto\";img.style.height=\"80%\";}else{img.style.width=\"auto\";img.style.height=\"auto\";};};</script></body></html>");
?>
