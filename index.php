<?php
$brandsFile = 'brands.json';
$brands = [];
if (file_exists($brandsFile)) {
    $brands = json_decode(file_get_contents($brandsFile), true) ?: [];
}

// 访问量统计
$statsFile = 'stats.json';
$stats = ['visits' => 0];
if (file_exists($statsFile)) {
    $stats = json_decode(file_get_contents($statsFile), true) ?: ['visits' => 0];
}
$stats['visits']++;
file_put_contents($statsFile, json_encode($stats));

// 读取友链数据
$friendsFile = 'friends.json';
$friends = [];
if (file_exists($friendsFile)) {
    $friends = json_decode(file_get_contents($friendsFile), true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>钠云查询 - 简约查询系统</title>
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

        .logo {
            margin-bottom: 40px;
        }

        .logo img {
            width: auto;
            height: 80px;
        }

        .logo h1 {
            color: #fff;
            font-size: 2.5em;
            margin-top: 18px;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.4);
            font-weight: 300;
            letter-spacing: 2px;
        }

        .search-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }

        .search-input {
            width: 100%;
            padding: 14px 18px;
            font-size: 14px;
            border: 2px solid #e8e8e8;
            outline: none;
            margin-bottom: 12px;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .search-input:focus {
            border-color: #333;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(51, 51, 51, 0.1);
        }

        .search-btn {
            width: 100%;
            padding: 14px;
            font-size: 15px;
            color: #fff;
            background: linear-gradient(135deg, #333 0%, #555 100%);
            border: none;
            cursor: pointer;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .search-result {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            text-align: left;
            margin-top: 15px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }

        .search-result h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.2em;
            font-weight: 600;
        }

        .brand-item {
            padding: 20px;
            border: 1px solid #e8e8e8;
            margin-bottom: 15px;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: #fff;
        }

        .brand-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.1);
            border-color: #d0d0d0;
        }

        .brand-name {
            font-size: 19px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #333;
            letter-spacing: 0.5px;
        }

        .brand-info {
            margin-bottom: 6px;
            color: #666;
            font-size: 14px;
            line-height: 1.8;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: bold;
            color: #333;
            border: none;
        }

        .status-经营 {
            background: #c8f7dc;
            border-radius: 15px;
        }

        .status-歇业 {
            background: #ffe4cc;
            border-radius: 12px;
        }

        .status-停业 {
            background: #ffcccc;
            border-radius: 12px;
        }

        .status-跑路 {
            background: #ffd0d5;
            border-radius: 12px;
        }

        .buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 30px;
        }

        .btn {
            padding: 14px 25px;
            font-size: 15px;
            color: #333;
            background: #fff;
            border: 2px solid #e8e8e8;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .btn:hover {
            background: #f8f8f8;
            border-color: #333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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

        .friends-section {
            background: rgba(255, 255, 255, 0.95);
            padding: 35px;
            margin-top: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }

        .friends-section h3 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.4em;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .friends-grid {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .friend-item {
            padding: 20px 30px;
            border: 2px solid #e8e8e8;
            text-align: center;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .friend-item:hover {
            border-color: #333;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .friend-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .friend-logo:hover {
            transform: scale(1.15);
        }

        .friend-link {
            display: block;
            color: #333;
            text-decoration: none;
        }

        .friend-link:hover {
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            .logo h1 {
                font-size: 2em;
            }

            .btn {
                padding: 12px 25px;
                font-size: 16px;
            }

            .friends-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="https://tuchuang.naidc.cn/i/2026/01/11/2bny7p.png" alt="钠云查询Logo">
            <h1>钠云查询</h1>
        </div>

        <div class="search-box">
            <form method="POST" action="">
                <input type="text" name="search_value" class="search-input" placeholder="请输入品牌名称、识别码或关键词..." required>
                <button type="submit" name="search" class="search-btn">查询</button>
            </form>

            <?php
            if (isset($_POST['search'])) {
                $searchValue = trim($_POST['search_value']);

                echo '<div class="search-result">';
                echo '<h3>查询结果</h3>';

                $results = [];
                foreach ($brands as $brand) {
                    if (stripos($brand['code'], $searchValue) !== false ||
                        stripos($brand['name'], $searchValue) !== false ||
                        stripos($brand['intro'], $searchValue) !== false ||
                        stripos($brand['website'], $searchValue) !== false) {
                        $results[] = $brand;
                    }
                }

                if (!empty($results)) {
                    foreach ($results as $brand) {
                        echo '<div class="brand-item">';
                        if (!empty($brand['logo'])) {
                            echo '<div style="text-align: center; margin-bottom: 10px;">';
                            echo '<img src="' . htmlspecialchars($brand['logo']) . '" alt="' . htmlspecialchars($brand['name']) . ' Logo" style="max-width: 100px; max-height: 100px; object-fit: contain;">';
                            echo '</div>';
                        }
                        echo '<div class="brand-name">' . htmlspecialchars($brand['name']) . '</div>';
                        echo '<div class="brand-info"><strong>识别码：</strong>' . htmlspecialchars($brand['code']) . '</div>';
                        echo '<div class="brand-info"><strong>官网：</strong>' . htmlspecialchars($brand['website']) . '</div>';
                        echo '<div class="brand-info"><strong>状态：</strong><span class="status-badge status-' . htmlspecialchars($brand['status']) . '">' . htmlspecialchars($brand['status']) . '</span></div>';
                        echo '<div class="brand-info"><strong>营业执照：</strong>' . htmlspecialchars($brand['has_license']) . '</div>';
                        echo '<div class="brand-info"><strong>联系方式：</strong>' . htmlspecialchars($brand['contact']) . '</div>';
                        echo '<div class="brand-info"><strong>提交时间：</strong>' . htmlspecialchars($brand['date']) . '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>未找到相关品牌信息</p>';
                }

                echo '</div>';
            }
            ?>
        </div>

        <div class="buttons">
            <a href="query.php" class="btn">查询</a>
            <a href="notice.php" class="btn">公告</a>
            <a href="register.php" class="btn">注册/投稿</a>
            <a href="gallery.php" class="btn">查看最新图片</a>
            <a href="https://qm.qq.com/q/YBCGF0jDAC" target="_blank" class="btn">加入Q群</a>
            <a href="brands.php" class="btn">查看全部品牌</a>
            <a href="certificate.php" class="btn">证书下载</a>
        </div>

            <?php if (!empty($friends)): ?>
        <div class="friends-section">
            <h3>友情链接</h3>
            <div class="friends-grid">
                <?php foreach ($friends as $friend): ?>
                    <div class="friend-item">
                        <a href="<?php echo htmlspecialchars($friend['url']); ?>" target="_blank" class="friend-link">
                            <img src="<?php echo htmlspecialchars($friend['logo']); ?>" alt="<?php echo htmlspecialchars($friend['name']); ?>" class="friend-logo" title="<?php echo htmlspecialchars($friend['name']); ?>">
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
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
