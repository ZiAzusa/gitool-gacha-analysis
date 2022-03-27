# 原神抽卡记录分析工具（PHP）
这是一个基于原生PHP的原神抽卡记录分析工具，同时也可作为API使用
#### 建议PHP版本：7.x
## 基本使用方法：
将本仓库clone到您的网站目录下即可开始使用
## API调用指南：
在搭建好网站后，您可以通过**POST**的方式调用http(s)://your-server-name/api.php
调用API的方法应为**application/json**
### 具体调用
| 参数名称 | 数据类型 | 示例 |
| ------ | ------ | ------ |
| url&emsp;&emsp;&emsp; | str&emsp;&emsp;&emsp; | https://webstatic.mihoyo.com/hk4e/event/e20190909gacha/index.html?authkey_ver=1&sign_type=2&auth_appid=webview_gacha&init_type=301&gacha_id=xxx&lang=zh-cn&device_type=mobile&ext=xxx&game_version=xxx&plat_type=xxx&authkey=xxx&game_biz=hk4e_cn#/log |
### 调用方法示例（PHP）
```
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode(['url' => $_POST['url']]),
        'timeout' => 60
    ]
]);
$json = file_get_contents('http(s)://your-server-name/api.php', false, $context);
$array = json_decode($json, true);
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
| next5star | int | 距离保底的抽数 |
| content | content arr[] | 五星物品详细信息 |
#### info数组
| 参数名称 | 数据类型 | 说明 |
| ------ | ------ | ------ |
| num | int | 该级别物品获取到的总数 |
| ratio | int | 该级别物品出货率（单位为%） |
#### content数组
| 参数名称 | 数据类型 | 说明 |
| ------ | ------ | ------ |
| name | str | 五星物品名称 |
| count | int | 抽取该物品所用次数 |
| tyoe | str | 物品类型（角色/武器） |
| id | int | 物品ID |

#### 调用结果示例（JSON Decoded）
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
