<?php

/**
 * 目录文件路径
 */
const COLUMN_FILE_PATH = __DIR__ . "/downloads/columns.json";

/**
 * 把网页的换行啥的都整没
 */
function pureWebpage($webpage) {
    return str_replace(["\n", "\r", "\t", " ", "　"], "", $webpage);
}

/**
 * 递归创建目录
 */
function mkdirR($dir) {
    $dirs = explode("/", $dir);
    $_dir = [];
    foreach ($dirs as $k => $v) {
        $_dir[] = $v;
        if (!is_dir(implode('/', $_dir))) @mkdir(implode('/', $_dir));
    }
}

/**
 * 获取目录创建json
 */
function getColumns() {
    $webpage = pureWebpage(file_get_contents("https://www.shizongzui.cc/longzu/"));
    $astr = '<divclass="booklistclearfix">';
    $a = strpos($webpage, $astr);
    $bstr = '<divid="i_adbottom"class="clearfix">';
    $b = strpos($webpage, $bstr);
    $columnDom = substr($webpage, $a + 34, $b - $a - 51);

    $lines = explode('</span><span', $columnDom);
    $res = [];
    $_title = "";
    foreach($lines as $k => $v) {
        if (stripos($v, "class=") !== false) {
            $_title = substr($v, 10);
            $res[$_title] = [];
        } else {
            $aastr = '><ahref="';
            $bbstr = '">';
            $bb = strpos($v, $bbstr);
            $ccstr = '</a>';
            $cc = strpos($v, $ccstr);
            $_url = substr($v, strlen($aastr), $bb - strlen($aastr));
            $_subtitle = substr($v, $bb + strlen($bb), $cc - $bb - strlen($bb));
            $res[$_title][] = [
                "subtitle" => $_subtitle,
                "url" => $_url
            ];
        }
    }

    file_put_contents(COLUMN_FILE_PATH, json_encode($res));
}

/**
 * 如果目录文件不存在则抓取创建
 */
if (!file_exists(COLUMN_FILE_PATH)) {
    echo "\n 获取目录中 \n" . PHP_EOL;
    getColumns();
}

function webpageToArticle($item, $cheapterName, $index) {
    echo "\n 开始处理 {$cheapterName} {$item['subtitle']}" . PHP_EOL;
    $webpage = file_get_contents($item['url']);
    $webpage = pureWebpage($webpage);

    $as = '<divclass="bookcontentclearfix"id="BookText">';
    $bs = '<divid="p_ad_t3"><script';
    $a = strpos($webpage, $as);
    $b = strpos($webpage, $bs);
    $articleDom = substr($webpage, $a + strlen($as), $b - $a - strlen($as) - 10);

    $lines = explode('<br/><br/>', $articleDom);
    
    $lines[] = "\n\n{$cheapterName}  {$item['subtitle']}  原文链接 {$item['url']}";
    mkdirR(__DIR__ . "/downloads/{$cheapterName}");
    file_put_contents(__DIR__ . "/downloads/{$cheapterName}/{$index}.{$item['subtitle']}.txt", implode("\n", $lines));
}

function mergeFiles() {
    echo "\n\n 开始合并文件" . PHP_EOL;

    $bookDir = __DIR__ . "/downloads/book.txt";
    if (!is_dir($bookDir)) {
        touch($bookDir);
    }
    $dirs = scandir(__DIR__ . "/downloads");
    foreach ($dirs as $v) {
        if (!in_array($v, ['.', '..', 'columns.json', 'book.txt'])) {
            $files = scandir(__DIR__ . "/downloads/{$v}");
            foreach ($files as $vv) {
                echo "\n\n 正在追加写入 {$v} {$vv}" . PHP_EOL;
                if (stripos($vv, ".txt") !== false) {
                    file_put_contents(
                        $bookDir,
                        implode("\n", [
                            "{$v} {$vv}", "", "",
                            file_get_contents(__DIR__ . "/downloads/{$v}/{$vv}"),
                            "本书由二狗亲爱的姐姐整理",
                            str_repeat("=", 20),
                            "","","",
                        ]),
                        FILE_APPEND
                    );
                }
            }
        }
    }

    echo "\n\n 写入完毕，请访问 {$bookDir}" . PHP_EOL;
}

$columnJson = file_get_contents(COLUMN_FILE_PATH);
$columnArr = json_decode($columnJson, true);

$taskTotal = array_sum(array_map(function($v) {
    return count($v);
}, $columnArr));

echo "开始处理，任务总数：{$taskTotal}" . PHP_EOL;

$currentTaskIndex = 1;

foreach ($columnArr as $k => $v) {
    foreach ($v as $kk => $vv) {
        echo "\n({$currentTaskIndex}/{$taskTotal})" . PHP_EOL;
        webpageToArticle($vv, $k, $kk);
        $currentTaskIndex ++;
    }
}

mergeFiles();