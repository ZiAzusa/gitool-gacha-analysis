# 原神抽卡记录分析工具（PHP）
这是一个基于PHP的原神抽卡记录分析工具，同时也可作为API使用
#### 建议PHP版本：7.x
## 基本使用方法：
将本仓库clone到您的网站目录下即可开始使用<br>
P.S.目前仅支持国内官服和B服的抽卡记录分析
## API调用指南：
在搭建好网站后，您可以通过**POST**的方式调用http(s)://your-server-name/api.php<br>
调用API的方法应为**application/json**
### 具体调用
| 参数名称 | 数据类型 | 示例 |
| ------ | ------ | ------ |
| url&emsp;&emsp;&emsp; | str&emsp;&emsp;&emsp;<br>(必须) | https://webstatic.mihoyo.com/hk4e/event/e20190909gacha/index.html?authkey_ver=1&sign_type=2&auth_appid=webview_gacha&init_type=301&gacha_id=xxx&lang=zh-cn&device_type=mobile&ext=xxx&game_version=xxx&plat_type=xxx&authkey=xxx&game_biz=hk4e_cn#/log |
| uid&emsp;&emsp;&emsp; | int&emsp;&emsp;&emsp;<br>(可选) | 123456789 |
| type&emsp;&emsp;&emsp; | str&emsp;&emsp;&emsp;<br>(可选) | table |
#### 注意：<br>当传入url参数时，uid参数将无效<br>仅传入uid参数时，若该uid使用过抽卡记录分析工具，则会获取本地保存的数据进行分析输出<br>若传入type=table，则会将分析结果按照html表格的形式输出，当type为空或其他值时，会默认输出json结果
### 调用方法示例（PHP）
```
<?php
$url = "https://webstatic.mihoyo.com/hk4e/event/e20190909gacha/index.html?authkey_ver=1&sign_type=2&auth_appid=webview_gacha&init_type=301&gacha_id=xxx&lang=zh-cn&device_type=mobile&ext=xxx&game_version=xxx&plat_type=xxx&authkey=xxx&game_biz=hk4e_cn#/log";
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode([
            'url' => $url
        ]),
        'timeout' => 60
    ]
]);
$json = file_get_contents('http(s)://your-server-name/api.php', false, $context);
$array = json_decode($json, true);
print_r("<pre>");
print_r($array);
?>
```
### 具体响应
| 参数名称 | 数据类型 | 说明 |
| ------ | ------ | ------ |
| code | int | 正常调用：200<br>URL错误：400<br>找不到数据：404 |
| message | str | 错误信息 |
| data | result arr | 分析结果数组 |
#### result数组
| 参数名称 | 数据类型 | 说明 |
| ------ | ------ | ------ |
| uid | int | 传入URL所属的原神UID |
| full | analysis arr | 全部数据的分析结果 |
| character | analysis arr | 角色池数据的分析结果 |
| arms | analysis arr | 武器池数据的分析结果 |
| resident | analysis arr | 常驻池数据的分析结果 |
| novice | analysis arr | 新手池数据的分析结果 |
#### analysis数组
| 参数名称 | 数据类型 | 说明 |
| ------ | ------ | ------ |
| num | int | 该池总抽卡次数 |
| 4star | info arr | 四星数量和出货率 |
| 5star | info arr | 五星数量和出货率 |
| next5star | int | 距离保底的抽数（不适用于full和novice） |
| content | content arr[] | 五星物品详细信息（不适用于full） |
#### info数组
| 参数名称 | 数据类型 | 说明 |
| ------ | ------ | ------ |
| num | int | 该级别物品获取到的总数 |
| ratio | float | 该级别物品出货率（单位为%） |
#### content数组
| 参数名称 | 数据类型 | 说明 |
| ------ | ------ | ------ |
| name | str | 五星物品名称 |
| avatar | str | 五星物品预览图 |
| count | int | 抽取该物品所用次数 |
| type | str | 物品类型（角色/武器） |
| id | int | 物品ID |
### 调用结果示例（JSON Decoded）
```
Array
(
    [code] => 200
    [message] => success
    [data] => Array
        (
            [uid] => 1xxxxxxxx
            [full] => Array
                (
                    [num] => 110
                    [4star] => Array
                        (
                            [num] => 16
                            [ratio] => 14.55
                        )

                    [5star] => Array
                        (
                            [num] => 2
                            [ratio] => 1.82
                        )

                )

            [character] => Array
                (
                    [num] => 71
                    [4star] => Array
                        (
                            [num] => 11
                            [ratio] => 1
                        )

                    [5star] => Array
                        (
                            [num] => 1
                            [ratio] => 1.41
                        )

                    [next5star] => 71
                    [content] => Array
                        (
                            [0] => Array
                                (
                                    [name] => 魈
                                    [avatar] => https://patchwiki.biligame.com/images/ys/thumb/xxx.png
                                    [count] => 52
                                    [type] => 角色
                                    [id] => 1641531960002136435
                                )

                        )

                )

            [arms] => Array
                (
                    [num] => 19
                    [4star] => Array
                        (
                            [num] => 2
                            [ratio] => 10.53
                        )

                    [5star] => Array
                        (
                            [num] => 0
                            [ratio] => 0
                        )

                    [next5star] => 61
                    [content] => Array
                        (
                        )

                )

            [resident] => Array
                (
                    [num] => 20
                    [4star] => Array
                        (
                            [num] => 3
                            [ratio] => 15
                        )

                    [5star] => Array
                        (
                            [num] => 1
                            [ratio] => 5
                        )

                    [next5star] => 80
                    [content] => Array
                        (
                            [0] => Array
                                (
                                    [name] => 天空之傲
                                    [avatar] => https://patchwiki.biligame.com/images/ys/thumb/xxx.png
                                    [count] => 10
                                    [type] => 武器
                                    [id] => 1641632760000784235
                                )

                        )

                )

            [novice] => Array
                (
                    [num] => 0
                    [4star] => Array
                        (
                            [num] => 0
                            [ratio] => 0
                        )

                    [5star] => Array
                        (
                            [num] => 0
                            [ratio] => 0
                        )

                    [content] => Array
                        (
                        )

                )

        )

)
```
### 网页效果图
![image](https://github.com/0803QwQ/gitool-gacha-analysis/blob/main/data/demo.jpeg)
## 更新记录
#### 2022/4/9:
1.添加了通过解析BiliWiKi的HTML文档获取角色属性的函数<br>
2.将数据保存操作封装为函数<br>
3.支持B服的抽卡记录查询（UID以5开头）<br>
4.移除了开发阶段在api.php遗留的HTML表单提交页面<br>
5.支持通过传入已分析过的UID获取曾保存的抽卡记录<br>
6.移除了一些无意义注释
#### 2022/4/11:
1.支持在HTML表格获取五星物品缩略图<br>
2.支持通过API获取五星物品缩略图<br>
3.改进了getColor函数，更名为getInfo，可以通过解析BiliWiKi的HTML文档获取角色或武器的详细信息<br>
4.添加了切换输出物品缩略图或物品输出的按钮，并添加了相关的CSS样式<br>
5.修复了无法输出多个五星物品的Bug<br>
6.调整了部分元素角色的显示颜色，提高了对比度，使阅读更清晰
## Todo（画饼）
~~1.通过颜色区分角色属性~~<br>
~~2.通过显示角色头像/武器缩略图的方式显示五星物品~~<br>
~~3.在API返回角色头像URL~~<br>
4.支持国际服的抽卡数据分析<br>
5.添加对欧非情况的评判<br>
~~6.通过提交UID的方式获取曾保存的抽卡记录~~<br>
7.添加用户手动上传抽卡记录数据的方式<br>
...
#### 基于米哈游官方抽卡数据API打造
#### Powered by 0803QwQ, Thanks for Your Using.
