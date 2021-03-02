# phpGetDragonRaja
一个自动抓取龙族小说的脚本

## 声明
下载使用此脚本均表明你已阅读此声明并认同我的观点。数据均收集自网站 [https://www.shizongzui.cc/longzu/](https://www.shizongzui.cc/longzu/)，如有侵权请联系我 `t@xtzero.me`或提issue，我会尽快删除。程序仅供技术交流用途，请将下载到的电子书资源在24小时内删除，请勿用于商业用途，如造成严重后果开发者不负任何责任。

## 环境要求

+ php 7.4+

## 使用方式

```php
$ php index.php
```

## 文件目录

```
.
├── downloads
│   ├── columns.json        // 目录文件
│   ├── ...                 // 分本目录
│   └── book.txt            // 整本文件
└── index.php               // 脚本程序
```

## 协议

[LICENSE](./LICENSE)