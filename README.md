# 原神抽卡记录分析工具（PHP）
这是一个基于原生PHP的原神抽卡记录分析工具，同时也可作为API使用
### 建议PHP版本：7.x
### 基本使用方法：
将本仓库clone到您的网站目录下即可开始使用
### API调用指南：
在搭建好网站后，您可以通过**POST**的方式调用http(s)://your-server-name/api.php
调用API的方法应为**application/json**
#### 具体调用
| 参数名称 | 数据类型 | 示例 |
| ------ | ------ | ------ |
| url&emsp; | str&emsp; | https://webstatic.mihoyo.com/hk4e/event/e20190909gacha/index.html?authkey_ver=1&sign_type=2&auth_appid=webview_gacha&init_type=301&gacha_id=xxx&lang=zh-cn&device_type=mobile&ext=xxx&game_version=xxx&plat_type=xxx&authkey=xxx&game_biz=hk4e_cn#/log |
#### 具体响应
| 参数名称 | 数据类型 | 说明 |
| ------ | ------ | ------ |
| code | int | 正常调用：200<br>URL错误：400<br>找不到数据：404 |
| message | str | 错误信息 |
| data | result[] | 分析结果数组 |
##### result数组
| 参数名称 | 数据类型 | 说明 |
| ------ | ------ | ------ |
| uid | int | 传入URL所属的原神UID |
| full | analysis[] | 全部数据的分析结果 |
| character | analysis[] | 角色池数据的分析结果 |
| arms | analysis[] | 武器池数据的分析结果 |
| resident | analysis[] | 常驻池数据的分析结果 |
| novice | analysis[] | 新手池数据的分析结果 |
##### analysis数组
| 参数名称 | 数据类型 | 说明 |
| ------ | ------ | ------ |
| num | int | 该池总抽卡次数 |
| 4star | info[] | 四星数量和出货率 |
| 5star | info[] | 五星数量和出货率 |
| next5star | int | 距离保底的抽数 |
| resident | content[] | 五星物品详细信息 |
