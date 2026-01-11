<?php
// 访问量统计
$statsFile = 'stats.json';
$stats = ['visits' => 0];
if (file_exists($statsFile)) {
    $stats = json_decode(file_get_contents($statsFile), true) ?: ['visits' => 0];
}
$stats['visits']++;
file_put_contents($statsFile, json_encode($stats));
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>最新图片 - 钠云查询</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            min-height: 100vh;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 600px;
            padding: 40px 20px;
            text-align: center;
        }

        .back-btn {
            display: inline-block;
            padding: 12px 28px;
            color: #333;
            background: #fff;
            border: 2px solid #e8e8e8;
            cursor: pointer;
            text-decoration: none;
            margin-bottom: 35px;
            font-size: 15px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .back-btn:hover {
            background: #f8f8f8;
            border-color: #333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .image-container {
            padding: 50px;
            border: 1px solid #e8e8e8;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            background: #fff;
            transition: all 0.3s ease;
        }

        .image-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .image-container img {
            width: 100%;
            max-width: 320px;
            height: auto;
            border-radius: 8px;
        }

        .version-info {
            margin-top: 25px;
            color: #666;
            font-size: 17px;
            font-weight: 500;
        }

        .version-number {
            font-weight: 600;
            color: #333;
            padding: 4px 12px;
            background: linear-gradient(135deg, #333 0%, #555 100%);
            color: #fff;
            border-radius: 8px;
            display: inline-block;
        }

        .update-date {
            color: #999;
            font-size: 14px;
            margin-top: 8px;
        }

        .cache-notice {
            margin-top: 12px;
            color: #999;
            font-size: 14px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            display: inline-block;
        }

        .footer {
            margin-top: auto;
            padding: 30px 20px;
            text-align: center;
            background: rgba(0, 0, 0, 0.3);
            width: 100%;
        }

        .icp-link {
            display: inline-flex;
            align-items: center;
            color: #fff;
            text-decoration: none;
            margin-bottom: 10px;
            transition: opacity 0.3s ease;
        }

        .icp-link:hover {
            opacity: 0.8;
        }

        .icp-link img {
            width: 20px;
            height: 20px;
            margin-right: 8px;
        }

        .ssl-seal img {
            width: 80px;
            opacity: 0.9;
        }

        .ssl-seal a {
            cursor: pointer;
        }

        .copyright {
            color: #fff;
            font-size: 12px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← 返回首页</a>
        
        <div class="image-container">
            <img src="https://tuchuang.naidc.cn/i/2026/01/11/3kvbjy.png" alt="系统Logo">
        </div>

        <div class="version-info">
            版本：<span class="version-number">第1版</span>
        </div>
        <div class="update-date">
            更新日期：2026-01-11
        </div>
        <div class="cache-notice">
            不符请清缓存
        </div>
    </div>

    <div class="footer">
        <a href="https://beian.miit.gov.cn/" target="_blank" class="icp-link">
            <img src="https://mcddos.top/icp.ico" alt="ICP备案图标">
            湘ICP备2025103428号-4
        </a>
        <br>
        <div class="ssl-seal">
            <a href="https://myssl.com/cx.naidc.cn?domain=cx.naidc.cn&status=success" target="_blank">
                <img src="https://tuchuang.naidc.cn/i/2026/01/11/3ycfvf.png" alt="SSL安全认证">
            </a>
        </div>
        <div class="copyright">Copyright © 2025 -2026 By 钠云查询 All Rights Reserved.</div>
    </div>
</body>
</html>
