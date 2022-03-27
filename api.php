<?php
#米哈游API基本信息，若米哈游修改了API地址可以通过这里快速适配，好耶(●'◡'●)
$gachaWeb = "webstatic.mihoyo.com/hk4e/event/e20190909gacha/index.html";
$gachaApi = "hk4e-api.mihoyo.com/event/gacha_info/api/getGachaLog";
$characterPool = 301;//角色UP池ID
$armsPool = 302;//武器UP池ID
$residentPool = 200;//常住池ID
$novicePool = 100;//新手池ID
#获取POST传入的URL值
$gachaUrl = @json_decode(file_get_contents("php://input"), true)['url'];
if ($_GET['type'] == "html"){
    #面向用户的GUI
    $gachaUrl = $_POST['url'];
    print_r("<html><head></head><body><form method='post'><p>抽卡记录URL：<input id='url' type='url' name='url' value='{$gachaUrl}'></p><input id='submit' type='submit' name='submit' value='开始分析'></form></body></html>");//Type为HTML，输出用作填写表单的文档
    if (!$gachaUrl) die();
};//当type参数不是html时将作为输出JSON的API
#处理传入的URL，将其格式化为调用米哈游抽卡记录API角色池第一页输出20条的URL
if (strstr($gachaUrl, $gachaWeb)){
    #传入webstatic，添加参数
    $gachaUrl = str_replace($gachaWeb, $gachaApi, $gachaUrl);
    $gachaUrl = str_replace("#/log", null, $gachaUrl);//避免因#造成拼接的参数无效
    $gachaUrl .= "&gacha_type=301&page=1&size=20&end_id=0";
}else if (strstr($gachaUrl, $gachaApi)){
    #传入hk4e-api，拆分参数处理
    $gachaUrlArr = explode("&", $gachaUrl);
    $infoNameArr = ['gacha_type=', 'page=', 'size=', 'end_id='];
    $infoNameArrX = ['gacha_type=301', 'page=1', 'size=20', 'end_id=0'];
    $gachaUrl = null;
    foreach ($gachaUrlArr as $gachaUrlKey => $gachaUrlValue){
        #循环处理拆分的URL参数
        foreach ($infoNameArr as $infoNameKey => $infoNameValue) if (strstr($gachaUrlValue, $infoNameValue)) $gachaUrlArr[$gachaUrlKey] = $infoNameArrX[$infoNameKey];//如果包含关键词则替换为被替换数组对应键的值
        $gachaUrl .= $gachaUrlArr[$gachaUrlKey]."&";//重新拼接URL
    };
    $gachaUrl = substr($gachaUrl, 0, (strlen($gachaUrl) - 1));
}else{
    #传入错误的URL，结束进程
    die(json_encode(['code' => 400,'message' => '传入的URL不正确','data' => []]));
};
#声明卡池数组
$poolArr = [$characterPool, $armsPool, $residentPool, $novicePool];
$gachaAllArr = [0 => [], 1 => [], 2 => [], 3 => []];
$gachaType = $poolArr[0];
#分别向每个卡池数组添加数据
foreach ($poolArr as $poolKey => $poolValue){
    #初始化请求API参数
    $gachaUrl = str_replace("gacha_type=".$gachaType, "gacha_type=".$poolValue, $gachaUrl);
    $gachaEndID = 0;
    $page = 1;
    $gachaUrlX = $gachaUrl;//声明UrlX变量，避免初始化的URL受影响
    #do...while循环调用米哈游抽卡记录API，获取全部的抽卡数据
    do{
        $gachaEndIDX = $gachaEndID;//继承上一次调用的end_id参数
        #调用API并转为数组处理
        if (!@file_get_contents($gachaUrlX)) die(json_encode(['code' => 404,'message' => 'url not found','data' => []]));
        $gachaData = file_get_contents($gachaUrlX);
        $gachaDataArr = json_decode($gachaData, true);
        $gachaDataArr = (array)$gachaDataArr['data']['list'];//我也不知道为什么要强制转换数组类型，反正删了就报错（；´д｀）ゞ
        if (!$uid) $uid = $gachaDataArr[0]['uid'];//获取UID，便于后续存储
        foreach ($gachaDataArr as $gachaDataKey => &$gachaDataValue) unset($gachaDataValue['uid'], $gachaDataValue['gacha_type'], $gachaDataValue['item_id'], $gachaDataValue['count'], $gachaDataValue['lang']);//删除对抽卡分析无意义的结果，节省存储空间
        $gachaAllArr[$poolKey] = array_merge_recursive($gachaAllArr[$poolKey], $gachaDataArr);//拼接API输出到卡池数组
        #修改API调用参数，为下一次调用做准备
        $page ++;
        $gachaUrlX = str_replace("page=".($page - 1), "page=".$page, $gachaUrlX);//修改page参数
        $gachaEndID  = $gachaDataArr[(count($gachaDataArr) - 1)]['id'];
        $gachaUrlX = str_replace("end_id=".$gachaEndIDX, "end_id=".$gachaEndID, $gachaUrlX);//修改end_id参数
    }while ($gachaDataArr != []);
    $gachaAllArr[$poolKey] = array_reverse($gachaAllArr[$poolKey]);//使卡池数组按时间顺序排列
    $gachaType = $poolValue;//切换下一个卡池数组
};
#将整理好的抽卡数据存入本地
$dataFile = "data/".$uid.".json";
if (!file_exists($dataFile)){
    #第一次使用，新建文件
    $fp = fopen($dataFile, "w+");
    fwrite($fp, json_encode($gachaAllArr));
    fclose($fp);
    $gachaArr = $gachaAllArr;//整合数据
}else{
    #多次使用，将新记录与旧记录合并
    $gachaAllArrX = json_decode(file_get_contents($dataFile), true);
    foreach ($gachaAllArr as $gachaAllKey => $gachaAllValue){
        $gachaAllValue = array_diff($gachaAllValue, $gachaAllArrX[$gachaAllKey]);//取调用API所得数据与本地数据的Value差集
        $gachaAllArrX[$gachaAllKey] = array_merge($gachaAllArrX[$gachaAllKey], $gachaAllValue);//将调用API所得数据与本地数据的差集追加到本地数据后
        ###大无语！什么垃圾PHP！就不能直接以Value取并集是吧！艹！～(　TロT)σ
    };
    $fp = fopen($dataFile, "w+");
    fwrite($fp, json_encode($gachaAllArrX));
    fclose($fp);
    $gachaArr = $gachaAllArrX;//整合数据
};
#对整合的数据进行数据分析
foreach ($gachaArr as $gachaKey => $gachaValue){
    #利用可变变量生成数据记录变量，具体变量如下：
    /*
      0:角色池，1:武器池，2:常住池，3:新手池
      $pool5star(0/1/2/3):五星物品的详细信息
      $pool5starNum(0/1/2/3):五星物品总数
      $pool4starNum(0/1/2/3):四星物品总数
      $poolAllNum(0/1/2/3):抽卡总数
     */#(＠_＠;)
    $gachaName = ['pool5star', 'pool5starCount', 'pool5starCountX', 'pool5starNum', 'pool5starRatio', 'pool4starNum', 'pool4starRatio', 'poolAllNum'];
    foreach ($gachaName as $gachaNameValue) $$gachaNameValue = $gachaNameValue.$gachaKey;//循环声明可变变量
    $$pool5star = [];
    $$pool5starCountX = 0;
    $$pool5starNum = strval(0);
    $$pool4starNum = strval(0);
    $$poolAllNum = strval(0);
    foreach ($gachaValue as $gachaValueX){
        #为每个卡池循环全部抽卡结果并分析
        $$poolAllNum ++;
        if ($gachaValueX['rank_type'] == 4){
            $$pool4starNum ++;
        }else if ($gachaValueX['rank_type'] == 5){
            if ($$pool5star = []) $$pool5starCount = $$poolAllNum;
            $$pool5starNum ++;
            $$pool5starCount = $$poolAllNum - $$pool5starCountX;
            $$pool5starCountX += $$pool5starCount;
            ${$pool5star}[] = ["name" => $gachaValueX['name'], "count" => $$pool5starCount, "type" => $gachaValueX['item_type'], "id" => $gachaValueX['id']];//整理五星数据
        };
    };
    $$pool5starRatio = round(($$poolAllNum != 0 ? ((100 * $$pool5starNum) / $$poolAllNum) : 0), 2);
    $$pool4starRatio = round(($$poolAllNum != 0 ? ((100 * $$pool4starNum) / $$poolAllNum) : 0), 2);
};
#计算各项总数和平均数
$poolAllNumFull = $poolAllNum0 + $poolAllNum1 + $poolAllNum2 + $poolAllNum3;
$pool4starNumFull = $pool4starNum0 + $pool4starNum1 + $pool4starNum2 + $pool4starNum3;
$pool5starNumFull = $pool5starNum0 + $pool5starNum1 + $pool5starNum2 + $pool5starNum3;
$pool4starRatioFull = round(($poolAllNumFull != 0 ? ((100 * $pool4starNumFull) / $poolAllNumFull) : 0), 2);
$pool5starRatioFull = round(($poolAllNumFull != 0 ? ((100 * $pool5starNumFull) / $poolAllNumFull) : 0), 2);
if ($_GET['type'] == "html" || $_GET['type'] == "table"){
    #合并数据分析结果并输出中文Text
    print_r("<a href='data/{$uid}.json'><button id='dljson'>下载抽卡数据表单</button></a><div class='notice'><table border='2' bordercolor='black' width='300' cellspacing='0' cellpadding='5'><tr><td>原神UID</td><td colspan='2'>".$uid."</td></tr><tr><td rowspan='3'>全部数据</td><td>抽卡总数</td><td>{$poolAllNumFull}抽</td></tr><tr><td>四星数量</td><td>{$pool4starNumFull}个({$pool4starRatioFull}%)</td></tr><tr><td>五星数量</td><td>{$pool5starNumFull}个{$pool5starRatioFull}%)</td></tr><td rowspan='".(5 + count($pool5star0))."'>角色池</td><td>抽卡总数</td><td>{$poolAllNum0}抽</td></tr><tr><td>四星数量</td><td>{$pool4starNum0}个({$pool4starRatio0}%)</td></tr><tr><td>五星数量</td><td>{$pool5starNum0}个{$pool5starRatio0}%)</td></tr><tr><td>距离保底</td><td>".(90 - ($poolAllNum0 - $pool5starCountX0))."抽</td></tr><tr><td rowspan='".(1 + count($pool5star0))."'>五星物品</td>");
    foreach ($pool5star0 as $pool5star0Key => $pool5star0Value) print_r("<tr><td>".($pool5star0Key + 1).".".$pool5star0Value['name']."[".$pool5star0Value['count']."]</td></tr>");
    if (count($pool5star0) == 0) print_r("<td>(○´･д･)ﾉ</td>");
    print_r("</tr><td rowspan='".(5 + count($pool5star1))."'>武器池</td><td>抽卡总数</td><td>{$poolAllNum1}抽</td></tr><tr><td>四星数量</td><td>{$pool4starNum1}个({$pool4starRatio1}%)</td></tr><tr><td>五星数量</td><td>{$pool5starNum1}个{$pool5starRatio1}%)</td></tr><tr><td>距离保底</td><td>".(80 - ($poolAllNum1 - $pool5starCountX1))."抽</td></tr><tr><td rowspan='".(1 + count($pool5star1))."'>五星物品</td>");
    foreach ($pool5star1 as $pool5star1Key => $pool5star1Value) print_r("<tr><td>".($pool5star1Key + 1).".".$pool5star1Value['name']."[".$pool5star1Value['count']."]</td></tr>");
    if (count($pool5star1) == 0) print_r("<td>(○´･д･)ﾉ</td>");
    print_r("</tr><td rowspan='".(5 + count($pool5star2))."'>常驻池</td><td>抽卡总数</td><td>{$poolAllNum2}抽</td></tr><tr><td>四星数量</td><td>{$pool4starNum2}个({$pool4starRatio2}%)</td></tr><tr><td>五星数量</td><td>{$pool5starNum2}个{$pool5starRatio2}%)</td></tr><tr><td>距离保底</td><td>".(90 - ($poolAllNum2 - $pool5starCountX2))."抽</td></tr><tr><td rowspan='".(1 + count($pool5star2))."'>五星物品</td>");
    foreach ($pool5star2 as $pool5star2Key => $pool5star2Value) print_r("<tr><td>".($pool5star2Key + 1).".".$pool5star2Value['name']."[".$pool5star2Value['count']."]</td></tr>");
    if (count($pool5star2) == 0) print_r("<td>(○´･д･)ﾉ</td>");
    print_r("</tr><td rowspan='".(4 + count($pool5star3))."'>新手池</td><td>抽卡总数</td><td>{$poolAllNum3}抽</td></tr><tr><td>四星数量</td><td>{$pool4starNum3}个({$pool4starRatio3}%)</td></tr><tr><td>五星数量</td><td>{$pool5starNum3}个{$pool5starRatio3}%)</td></tr><tr><td rowspan='".(1 + count($pool5star3))."'>五星物品</td>");
    foreach ($pool5star3 as $pool5star3Key => $pool5star3Value) print_r("<tr><td>".($pool5star3Key + 1).".".$pool5star3Value['name']."[".$pool5star3Value['count']."]</td></tr>");
    if (count($pool5star3) == 0) print_r("<td>(○´･д･)ﾉ</td>");
    print_r("</tr></table></div>");//输出数据表格
}else{
    #合并数据分析结果并输出JSON
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
    print_r(json_encode($result));//输出JSON
};
die();//结束进程，释放内存
###艹！这终于写完了！进程die了我也快die了`(*>﹏<*)′
?>
