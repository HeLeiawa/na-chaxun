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

$selectedBrand = null;
$searchValue = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $searchValue = trim($_POST['search_value']);
    
    foreach ($brands as $brand) {
        if (stripos($brand['code'], $searchValue) !== false) {
            // 排除跑路和停业状态
            if ($brand['status'] === '跑路' || $brand['status'] === '停业') {
                $errorMsg = '该品牌状态为"' . $brand['status'] . '"，无法生成证书';
            } else {
                $selectedBrand = $brand;
            }
            break;
        }
    }
    
    if (!$selectedBrand && empty($errorMsg)) {
        $errorMsg = '未找到该识别码对应的品牌信息';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>品牌证书下载 - 钠云查询</title>
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
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 900px;
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

        .search-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 35px;
            margin-bottom: 30px;
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
            margin-bottom: 15px;
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

        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-top: 15px;
            border-radius: 10px;
            font-weight: 500;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        /* 证书样式 */
        .certificate-container {
            background: url('zs.png') center top no-repeat;
            background-size: contain;
            padding: 0;
            margin-top: 30px;
            width: 100%;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
            min-height: 900px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .certificate-content {
            padding: 40px 60px;
            position: relative;
            z-index: 1;
        }

        .certificate-header {
            text-align: center;
            margin-bottom: 20px;
            margin-top: 120px;
        }

        .certificate-title {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
            letter-spacing: 6px;
        }

        .certificate-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 4px;
            letter-spacing: 2px;
        }

        .certificate-number {
            font-size: 12px;
            color: #999;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }

        .certificate-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        .certificate-logo {
            width: 140px;
            text-align: center;
            margin-bottom: 12px;
        }

        .certificate-logo img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            background: none;
            border-radius: 0;
            box-shadow: none;
        }

        .certificate-info {
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        .certificate-row {
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #f0f0f0;
        }

        .certificate-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .certificate-label {
            font-size: 12px;
            color: #999;
            margin-bottom: 4px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .certificate-value {
            font-size: 14px;
            color: #333;
            font-weight: 600;
            word-break: break-all;
            letter-spacing: 0.5px;
        }

        .certificate-value.code {
            font-size: 18px;
            color: #333;
            letter-spacing: 2px;
            font-family: 'Courier New', monospace;
            font-weight: 700;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 15px;
            letter-spacing: 1px;
        }

        .status-经营 {
            background: #c8f7dc;
            color: #0f5132;
        }

        .status-歇业 {
            background: #ffe4cc;
            color: #856404;
        }

        .certificate-footer {
            margin-top: 10px;
            text-align: center;
            position: relative;
            padding-top: 10px;
        }

        .certificate-footer p {
            font-size: 12px;
            color: #666;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .certificate-footer .certify-text {
            font-size: 14px;
            color: #333;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .certificate-footer .seal {
            margin-top: 10px;
            font-size: 11px;
            color: #999;
            letter-spacing: 0.5px;
        }

        /* 公章样式 */
        .seal-stamp {
            position: absolute;
            top: 250px;
            right: 80px;
            width: 140px;
            height: 140px;
            z-index: 10;
        }

        .seal-stamp img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            opacity: 0.85;
        }

        .seal-stamp-inline {
            display: inline-block;
            vertical-align: middle;
            margin-left: 20px;
            width: 80px;
            height: 80px;
        }

        .seal-stamp-inline img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            opacity: 0.85;
        }

        .certificate-row-with-seal {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .certificate-row-with-seal .certificate-label,
        .certificate-row-with-seal .certificate-value {
            margin-bottom: 0;
        }

        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            align-items: center;
            margin-top: 30px;
        }

        .download-btn {
            padding: 15px 40px;
            font-size: 16px;
            color: #fff;
            background: linear-gradient(135deg, #333 0%, #555 100%);
            border: none;
            cursor: pointer;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            display: inline-block;
            text-decoration: none;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
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

        .visit-count {
            color: #fff;
            font-size: 12px;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .certificate-header {
                margin-top: 100px;
                margin-bottom: 25px;
            }

            .certificate-title {
                font-size: 24px;
                letter-spacing: 3px;
            }

            .certificate-body {
                gap: 12px;
            }

            .certificate-logo {
                margin-bottom: 10px;
            }

            .certificate-logo img {
                width: 100px;
                height: 100px;
            }

            .certificate-info {
                width: 100%;
                text-align: center;
                max-width: 100%;
            }

            .certificate-value {
                font-size: 13px;
            }

            .certificate-value.code {
                font-size: 16px;
                letter-spacing: 1px;
            }

            .certificate-footer {
                margin-top: 10px;
                padding-top: 10px;
            }

            .certificate-footer p {
                font-size: 11px;
            }

            .seal-stamp {
                width: 100px;
                height: 100px;
                top: 180px;
                right: 30px;
            }

            .seal-stamp-inline {
                width: 60px;
                height: 60px;
                margin-left: 10px;
            }

            .certificate-row-with-seal {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .button-group {
                flex-direction: column;
            }
        }

        @media print {
            body {
                background: #fff;
            }

            .search-box,
            .back-btn,
            .download-btn,
            .button-group,
            .footer {
                display: none !important;
            }

            .container {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }

            .certificate-container {
                box-shadow: none;
                margin: 0 auto;
                padding: 0;
                width: 800px;
                min-height: 900px;
                background: url('zs.png') center top no-repeat;
                background-size: contain;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                position: relative;
            }

            .certificate-content {
                padding: 40px 60px;
            }

            .certificate-header {
                margin-top: 120px;
            }

            .certificate-body {
                flex-direction: column;
                align-items: center;
            }

            .certificate-info {
                text-align: center;
            }

            h1 {
                display: none;
            }

            @page {
                size: A4;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← 返回首页</a>
        
        <h1>品牌证书下载</h1>

        <div class="search-box">
            <form method="POST" action="">
                <input type="text" name="search_value" class="search-input" placeholder="请输入品牌识别码生成证书..." required value="<?php echo htmlspecialchars($searchValue); ?>">
                <button type="submit" class="search-btn">生成证书</button>
            </form>

            <?php if ($errorMsg): ?>
                <div class="error-msg"><?php echo $errorMsg; ?></div>
            <?php endif; ?>
        </div>

        <?php if ($selectedBrand): ?>
        <div class="certificate-container" id="certificate">
            <div class="certificate-content">
                <div class="certificate-header">
                    <div class="certificate-title">品牌认证证书</div>
                    <div class="certificate-subtitle">钠云查询官方认证</div>
                    <div class="certificate-number">证书编号：<?php echo htmlspecialchars($selectedBrand['code']); ?></div>
                </div>

                <div class="certificate-body">
                    <div class="certificate-logo">
                        <?php if (!empty($selectedBrand['logo'])): ?>
                            <img src="<?php echo htmlspecialchars($selectedBrand['logo']); ?>" alt="<?php echo htmlspecialchars($selectedBrand['name']); ?> Logo">
                        <?php else: ?>
                            <img src="https://tuchuang.naidc.cn/i/2026/01/11/2bny7p.png" alt="钠云查询Logo">
                        <?php endif; ?>
                    </div>

                    <div class="certificate-info">
                        <div class="certificate-row">
                            <div class="certificate-label">品牌名称</div>
                            <div class="certificate-value"><?php echo htmlspecialchars($selectedBrand['name']); ?></div>
                        </div>

                        <div class="certificate-row">
                            <div class="certificate-label">品牌识别码</div>
                            <div class="certificate-value code"><?php echo htmlspecialchars($selectedBrand['code']); ?></div>
                        </div>

                        <div class="certificate-row">
                            <div class="certificate-label">品牌官网</div>
                            <div class="certificate-value"><?php echo htmlspecialchars($selectedBrand['website']); ?></div>
                        </div>

                        <div class="certificate-row">
                            <div class="certificate-label">经营状态</div>
                            <span class="status-badge status-<?php echo htmlspecialchars($selectedBrand['status']); ?>">
                                <?php echo htmlspecialchars($selectedBrand['status']); ?>
                            </span>
                        </div>

                        <div class="certificate-row certificate-row-with-seal">
                            <div class="certificate-label">注册时间</div>
                            <div class="certificate-value"><?php echo htmlspecialchars($selectedBrand['date']); ?></div>
                            <div class="seal-stamp-inline">
                                <img src="cqgz.png" alt="公章">
                            </div>
                        </div>

                        <div class="certificate-row">
                            <div class="certificate-label">品牌类型</div>
                            <div class="certificate-value">
                                <?php echo $selectedBrand['has_license'] === 'yes' ? '已认证品牌（有营业执照）' : '普通品牌（无营业执照）'; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="certificate-footer">
                    <p class="certify-text">兹证明上述品牌已通过钠云查询平台认证</p>
                    <p>本证书仅用于证明该品牌已在钠云查询平台注册登记</p>
                    <p>证书生成时间：<?php echo date('Y-m-d H:i:s'); ?></p>
                    <div class="seal">钠云查询官方认证 · 官方网址：cx.naidc.cn</div>


                </div>
            </div>
        </div>

        <div class="button-group">
            <button class="download-btn" onclick="downloadCertificate()">下载证书</button>
            <a href="physical-certificate.php?code=<?php echo $selectedBrand ? htmlspecialchars($selectedBrand['code']) : ''; ?>" class="download-btn" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">领取实体证书</a>
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

    <script>
        function downloadCertificate() {
            window.print();
        }
    </script>
</body>
</html>
