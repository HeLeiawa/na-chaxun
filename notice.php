<?php
// 读取公告数据
$noticesFile = 'notices.json';
$notices = [];
if (file_exists($noticesFile)) {
    $notices = json_decode(file_get_contents($noticesFile), true) ?: [];
}

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
    <title>公告 - 钠云查询</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            min-height: 100vh;
            background-image: url('https://t.alcy.cc/ycy/');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }

        .container {
            width: 100%;
            max-width: 800px;
            padding: 20px;
            margin-top: 50px;
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

        h1 {
            color: #fff;
            font-size: 2.5em;
            margin-bottom: 45px;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.4);
            font-weight: 300;
            letter-spacing: 2px;
        }

        .notice-list {
            background: rgba(255, 255, 255, 0.95);
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }

        .notice-item {
            padding: 25px;
            border-bottom: 1px solid #e8e8e8;
            text-align: left;
            transition: all 0.3s ease;
        }

        .notice-item:last-child {
            border-bottom: none;
        }

        .notice-item:hover {
            background: #f8f8f8;
            transform: translateX(5px);
        }

        .notice-title {
            color: #333;
            font-size: 1.25em;
            font-weight: 600;
            margin-bottom: 12px;
            letter-spacing: 0.3px;
        }

        .notice-date {
            color: #999;
            font-size: 0.9em;
            margin-bottom: 12px;
        }

        .notice-content {
            color: #333;
            line-height: 1.8;
            font-size: 15px;
        }

        .notice-tag {
            display: inline-block;
            padding: 4px 12px;
            background: linear-gradient(135deg, #333 0%, #555 100%);
            color: #fff;
            font-size: 0.85em;
            margin-right: 10px;
            border-radius: 8px;
            font-weight: 500;
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

        @media (max-width: 768px) {
            h1 {
                font-size: 2em;
            }

            .notice-list {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← 返回首页</a>
        
        <h1>系统公告</h1>

        <div class="notice-list">
            <?php if (!empty($notices)): ?>
                <?php foreach ($notices as $notice): ?>
                    <div class="notice-item">
                        <div class="notice-title">
                            <span class="notice-tag"><?php echo htmlspecialchars($notice['tag']); ?></span>
                            <?php echo htmlspecialchars($notice['title']); ?>
                        </div>
                        <div class="notice-date"><?php echo htmlspecialchars($notice['date']); ?></div>
                        <div class="notice-content">
                            <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="notice-item">
                    <p>暂无公告</p>
                </div>
            <?php endif; ?>
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
        <div class="visit-count">网站总访问量 <?php echo number_format($stats['visits']); ?></div>
        <div class="copyright">Copyright © 2025 -2026 By 钠云查询 All Rights Reserved.</div>
    </div>
</body>
</html>
