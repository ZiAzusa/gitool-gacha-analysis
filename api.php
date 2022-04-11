<?php
function getInfo($name, $type, $which){
    switch ($type){
        case "角色":
            $table = file_get_contents("https://wiki.biligame.com/ys/%E8%A7%92%E8%89%B2%E7%AD%9B%E9%80%89");
            $count = 12;
            break;
        case "武器":
            $table = file_get_contents("https://wiki.biligame.com/ys/%E6%AD%A6%E5%99%A8%E5%9B%BE%E9%89%B4");
            $count = 10;
            break;
        default:
            return("传入物品类型错误");
            break;
    };
    $table = preg_replace("'<table[^>]*?>'si", "", $table);
    $table = preg_replace("'<tr[^>]*?>'si", "", $table);
    $table = preg_replace("'<td[^>]*?>'si", "", $table);
    $table = str_replace("<img", "{img}", $table);
    $table = str_replace("</tr>", "{tr}", $table);
    $table = str_replace("</td>", "{td}", $table);
    $table = preg_replace("'<[/!]*?[^<>]*?>'si", "", $table);
    $table = preg_replace("'([rn])[s]+'", "", $table);
    $table = str_replace(" ", "", $table);
    $table = str_replace(" ", "", $table);
    $table = explode('{tr}', $table);
    array_pop($table);
    foreach ($table as $key => $tr){
        $td = explode('{td}', $tr);
        array_pop($td);
        if (trim($td[1]) == $name && count($td) == $count){
            $info = $td;
            break;
        };
    };
    foreach ((array)$info as $infoKey => $infoValue) $info[$infoKey] = trim($infoValue);
    $color = ['火' => '#F2523A', '水' => '#009BFF', '风' => '#4DF5B5', '雷' => '#AE81FF', '草' => '#49C82E', '冰' => '#66CCFF', '岩' => '#E0A827'];
    if ($which == "color"){
        switch ($type){
            case "角色":
                return($color[$info[4]]);
                break;
            default:
                return("#000000");
                break;
        };
    };
    if ($which == "avatar") return(explode("\"", $info[0])[3]);
    return("传入数据未找到");
};
function dataUpdate($array, $file){
    $fp = fopen($file, "w+");
    fwrite($fp, json_encode($array));
    fclose($fp);
};
$gachaWeb = "webstatic.mihoyo.com/hk4e/event/e20190909gacha/index.html";
$gachaApi = "hk4e-api.mihoyo.com/event/gacha_info/api/getGachaLog";
$characterPool = 301;
$armsPool = 302;
$residentPool = 200;
$novicePool = 100;
$postInput = @json_decode(file_get_contents("php://input"), true);
$gachaUrl = @$postInput['url'];
$gachaUID = @$postInput['uid'];
if (strstr($gachaUrl, $gachaWeb)){
    $gachaUrl = str_replace($gachaWeb, $gachaApi, $gachaUrl);
    $gachaUrl = str_replace("#/log", null, $gachaUrl);
    $gachaUrl .= "&gacha_type=301&page=1&size=20&end_id=0";
}else if (strstr($gachaUrl, $gachaApi)){
    $gachaUrlArr = explode("&", $gachaUrl);
    $infoNameArr = ['gacha_type=', 'page=', 'size=', 'end_id='];
    $infoNameArrX = ['gacha_type=301', 'page=1', 'size=20', 'end_id=0'];
    $gachaUrl = null;
    foreach ($gachaUrlArr as $gachaUrlKey => $gachaUrlValue){
        foreach ($infoNameArr as $infoNameKey => $infoNameValue) if (strstr($gachaUrlValue, $infoNameValue)) $gachaUrlArr[$gachaUrlKey] = $infoNameArrX[$infoNameKey];
        $gachaUrl .= $gachaUrlArr[$gachaUrlKey]."&";
    };
    $gachaUrl = substr($gachaUrl, 0, (strlen($gachaUrl) - 1));
}else if (is_numeric($gachaUrl) || $gachaUID != null){
    if ($gachaUID != null) $gachaUrl = $gachaUID;
    if(file_exists("data/".$gachaUrl.".json")){
        $uid = $gachaUrl;
        $gachaArr = json_decode(file_get_contents("data/".$gachaUrl.".json"), true);
        goto analysis;
    }else{
        die(json_encode(['code' => 404, 'message' => 'uid not found', 'data' => []]));
    };
}else{
    die(json_encode(['code' => 400, 'message' => '传入的URL不正确', 'data' => []]));
};
$poolArr = [$characterPool, $armsPool, $residentPool, $novicePool];
$gachaAllArr = [0 => [], 1 => [], 2 => [], 3 => []];
$gachaType = $poolArr[0];
foreach ($poolArr as $poolKey => $poolValue){
    $gachaUrl = str_replace("gacha_type=".$gachaType, "gacha_type=".$poolValue, $gachaUrl);
    $gachaEndID = 0;
    $page = 1;
    $gachaUrlX = $gachaUrl;
    do{
        $gachaEndIDX = $gachaEndID;
        if (!@file_get_contents($gachaUrlX)) die(json_encode(['code' => 404, 'message' => 'url not found', 'data' => []]));
        $gachaData = file_get_contents($gachaUrlX);
        $gachaDataArr = json_decode($gachaData, true);
        $gachaDataArr = (array)$gachaDataArr['data']['list'];
        if (!$uid) $uid = $gachaDataArr[0]['uid'];
        foreach ($gachaDataArr as $gachaDataKey => &$gachaDataValue) unset($gachaDataValue['uid'], $gachaDataValue['gacha_type'], $gachaDataValue['item_id'], $gachaDataValue['count'], $gachaDataValue['lang']);
        $gachaAllArr[$poolKey] = array_merge_recursive($gachaAllArr[$poolKey], $gachaDataArr);
        $page ++;
        $gachaUrlX = str_replace("page=".($page - 1), "page=".$page, $gachaUrlX);
        $gachaEndID  = $gachaDataArr[(count($gachaDataArr) - 1)]['id'];
        $gachaUrlX = str_replace("end_id=".$gachaEndIDX, "end_id=".$gachaEndID, $gachaUrlX);
    }while ($gachaDataArr != []);
    $gachaAllArr[$poolKey] = array_reverse($gachaAllArr[$poolKey]);
    $gachaType = $poolValue;
};
$dataFile = "data/".$uid.".json";
if (!file_exists($dataFile)){
    dataUpdate($gachaAllArr, $dataFile);
    $gachaArr = $gachaAllArr;
}else{
    $gachaAllArrX = json_decode(file_get_contents($dataFile), true);
    foreach ($gachaAllArr as $gachaAllKey => $gachaAllValue){
        $gachaAllValue = array_diff($gachaAllValue, $gachaAllArrX[$gachaAllKey]);
        $gachaAllArrX[$gachaAllKey] = array_merge($gachaAllArrX[$gachaAllKey], $gachaAllValue);
    };
    dataUpdate($gachaAllArrX, $dataFile);
    $gachaArr = $gachaAllArrX;
};
analysis:
foreach ($gachaArr as $gachaKey => $gachaValue){
    $gachaName = ['pool5star', 'pool5starCount', 'pool5starCountX', 'pool5starNum', 'pool5starRatio', 'pool4starNum', 'pool4starRatio', 'poolAllNum'];
    foreach ($gachaName as $gachaNameValue) $$gachaNameValue = $gachaNameValue.strval($gachaKey);
    $$pool5star = (array)$$pool5star;
    $$pool5starCountX = 0;
    $$pool5starNum = strval(0);
    $$pool4starNum = strval(0);
    $$poolAllNum = strval(0);
    foreach ($gachaValue as $gachaValueX){
        $$poolAllNum ++;
        if ($gachaValueX['rank_type'] == 4){
            $$pool4starNum ++;
        }else if ($gachaValueX['rank_type'] == 5){
            if ($$pool5star == []) $$pool5starCount = $$poolAllNum;
            $$pool5starNum ++;
            $$pool5starCount = $$poolAllNum - $$pool5starCountX;
            $$pool5starCountX += $$pool5starCount;
            array_push($$pool5star, ["name" => $gachaValueX['name'], "avatar" => getInfo($gachaValueX['name'], $gachaValueX['item_type'], "avatar"), "count" => $$pool5starCount, "type" => $gachaValueX['item_type'], "id" => $gachaValueX['id']]);
        };
    };
    $$pool5starRatio = round(($$poolAllNum != 0 ? ((100 * $$pool5starNum) / $$poolAllNum) : 0), 2);
    $$pool4starRatio = round(($$poolAllNum != 0 ? ((100 * $$pool4starNum) / $$poolAllNum) : 0), 2);
};
$poolAllNumFull = $poolAllNum0 + $poolAllNum1 + $poolAllNum2 + $poolAllNum3;
$pool4starNumFull = $pool4starNum0 + $pool4starNum1 + $pool4starNum2 + $pool4starNum3;
$pool5starNumFull = $pool5starNum0 + $pool5starNum1 + $pool5starNum2 + $pool5starNum3;
$pool4starRatioFull = round(($poolAllNumFull != 0 ? ((100 * $pool4starNumFull) / $poolAllNumFull) : 0), 2);
$pool5starRatioFull = round(($poolAllNumFull != 0 ? ((100 * $pool5starNumFull) / $poolAllNumFull) : 0), 2);
$times = json_decode(file_get_contents("data/view.json"), true);
if ($_GET['type'] == "table"){
    if ($uid != null){
        $times['web'] ++;
        dataUpdate($times, "data/view.json");
    };
    $jsonDownload = urlencode(json_encode(['json' => 'data/'.$uid.'.json']));
    print_r("<a href=\"JavaScript:download('{$jsonDownload}')\"><button id='dljson'>下载抽卡数据表单</button></a><a id='showA' href=\"JavaScript:showAvatar()\"><button id='showB'>显示五星物品预览</button></a><div class='notice'><table border='2' bordercolor='black' width='300' cellspacing='0' cellpadding='5'><tr><td>原神UID</td><td colspan='2'>".$uid."</td></tr><tr><td rowspan='3'>全部数据</td><td>抽卡总数</td><td>{$poolAllNumFull}抽</td></tr><tr><td>四星数量</td><td>{$pool4starNumFull}个({$pool4starRatioFull}%)</td></tr><tr><td>五星数量</td><td>{$pool5starNumFull}个({$pool5starRatioFull}%)</td>");
    $poolNameArr = ['角色池', '武器池', '常驻池', '新手池'];
    for ($poolType = 0; $poolType <= 3; $poolType ++){
        foreach ($gachaName as $gachaNameValue) $$gachaNameValue = $gachaNameValue.strval($poolType);
        print_r("</tr><td rowspan='".(5 + count($$pool5star))."'>{$poolNameArr[$poolType]}</td><td>抽卡总数</td><td>{$$poolAllNum}抽</td></tr><tr><td>四星数量</td><td>{$$pool4starNum}个({$$pool4starRatio}%)</td></tr><tr><td>五星数量</td><td>{$$pool5starNum}个({$$pool5starRatio}%)</td></tr><tr><td>距离保底</td><td>".(90 - ($$poolAllNum - $$pool5starCountX))."抽</td></tr><tr><td rowspan='".(1 + count($$pool5star))."'>五星物品</td>");
        foreach ($$pool5star as $poolTypeKey => $poolTypeValue) print_r("<tr><td style='color:".getInfo($poolTypeValue['name'], $poolTypeValue['type'], "color")."'>".($poolTypeKey + 1).".<span id='".$poolTypeValue['name']."'>".$poolTypeValue['name']."</span>[".$poolTypeValue['count']."]</td></tr>");
        if (count($$pool5star) == 0) print_r("<td>(○´･д･)ﾉ</td>");
    };
    print_r("</tr></table></div><p>抽卡记录分析工具已被使用 {$times['web']} 次</p><script>function showAvatar(){");
    for ($poolType = 0; $poolType <= 3; $poolType ++){
        $pool5star = "pool5star".strval($poolType);
        foreach ($$pool5star as $poolTypeKey => $poolTypeValue) print_r("document.getElementById('".$poolTypeValue['name']."').innerHTML=\"<img src='".$poolTypeValue['avatar']."' style='hight:40px;width:40px' />\";");
    };
    print_r("document.getElementById('showA').href=\"JavaScript:showName()\";document.getElementById('showB').innerHTML=\"显示五星物品名称\";};function showName(){");
    for ($poolType = 0; $poolType <= 3; $poolType ++){
        $pool5star = "pool5star".strval($poolType);
        foreach ($$pool5star as $poolTypeKey => $poolTypeValue) print_r("document.getElementById('".$poolTypeValue['name']."').innerHTML=\"".$poolTypeValue['name']."\";");
    };
   print_r("document.getElementById('showA').href=\"JavaScript:showAvatar()\";document.getElementById('showB').innerHTML=\"显示五星物品预览\";};</script>");
}else{
    if ($uid != null){
        $times['api'] ++;
        dataUpdate($times, "data/view.json");
    };
    $result = [
        'code' => 200,
        'message'=> "success",
        'data' => [
            'uid' => $uid,
            'full' => [
                'num' => $poolAllNumFull,
                '4star' => ['num' => $pool4starNumFull, 'ratio' => $pool4starRatioFull],
                '5star' => ['num' => $pool5starNumFull, 'ratio' => $pool5starRatioFull]
            ],
            'character' => [
                'num' => $poolAllNum0,
                '4star' => ['num' => $pool4starNum0, 'ratio' => 1],
                '5star' => ['num' => $pool5starNum0, 'ratio' => $pool5starRatio0],
                'next5star' => (90 - ($poolAllNum0 - $pool5starCountX0)),
                'content' => $pool5star0
            ],
            'arms' => [
                'num' => $poolAllNum1,
                '4star' => ['num' => $pool4starNum1, 'ratio' => $pool4starRatio1],
                '5star' => ['num' => $pool5starNum1, 'ratio' => $pool5starRatio1],
                'next5star' => (80 - ($poolAllNum1 - $pool5starCountX1)),
                'content' => $pool5star1
            ],
            'resident' => [
                'num' => $poolAllNum2,
                '4star' => ['num' => $pool4starNum2, 'ratio' => $pool4starRatio2],
                '5star' => ['num' => $pool5starNum2, 'ratio' => $pool5starRatio2],
                'next5star' => (90 - ($poolAllNum2 - $pool5starCountX2)),
                'content' => $pool5star2
            ],
            'novice' => [
                'num' => $poolAllNum3,
                '4star' => ['num' => $pool4starNum3, 'ratio' => $pool4starRatio3],
                '5star' => ['num' => $pool5starNum3, 'ratio' => $pool5starRatio3],
                'content' => $pool5star3
            ]
        ]
    ];
    print_r(json_encode($result));
};
?>
