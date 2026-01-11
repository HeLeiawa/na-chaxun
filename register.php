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

$msg = '';
$msgType = '';
$showResult = false;
$generatedCode = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $brandName = isset($_POST['brand_name']) ? trim($_POST['brand_name']) : '';
    $brandWebsite = isset($_POST['brand_website']) ? trim($_POST['brand_website']) : '';
    $brandIntro = isset($_POST['brand_intro']) ? trim($_POST['brand_intro']) : '';
    $brandStatus = isset($_POST['brand_status']) ? trim($_POST['brand_status']) : '';
    $hasLicense = isset($_POST['has_license']) ? trim($_POST['has_license']) : '';
    $contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
    $licenseFile = isset($_FILES['license_file']) ? $_FILES['license_file'] : '';
    $logoFile = isset($_FILES['logo_file']) ? $_FILES['logo_file'] : '';

    if (empty($brandName)) {
        $msg = '品牌名称不能为空！';
        $msgType = 'error';
    } elseif (empty($brandWebsite)) {
        $msg = '品牌官网不能为空！';
        $msgType = 'error';
    } elseif (empty($brandIntro)) {
        $msg = '品牌介绍不能为空！';
        $msgType = 'error';
    } elseif (empty($brandStatus)) {
        $msg = '请选择品牌状态！';
        $msgType = 'error';
    } elseif (empty($hasLicense)) {
        $msg = '请选择是否有营业执照！';
        $msgType = 'error';
    } elseif ($hasLicense == 'yes' && empty($licenseFile['name'])) {
        $msg = '请上传营业执照文件！';
        $msgType = 'error';
    } elseif (empty($logoFile['name'])) {
        $msg = '请上传品牌logo！';
        $msgType = 'error';
    } elseif (empty($contact)) {
        $msg = '联系方式不能为空！';
        $msgType = 'error';
    } else {
        $year = date('Y');
        $generatedCode = 'NY' . $year . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        // 检查识别码是否已存在，如果存在则重新生成
        $codeExists = true;
        while ($codeExists) {
            $codeExists = false;
            foreach ($brands as $brand) {
                if ($brand['code'] === $generatedCode) {
                    $codeExists = true;
                    break;
                }
            }
            if ($codeExists) {
                $generatedCode = 'NY' . $year . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            }
        }

        $licensePath = '';
        if ($hasLicense == 'yes' && !empty($licenseFile['name'])) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileExt = pathinfo($licenseFile['name'], PATHINFO_EXTENSION);
            $fileName = $generatedCode . '_license.' . $fileExt;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($licenseFile['tmp_name'], $targetPath)) {
                $licensePath = $targetPath;
            }
        }

        $logoPath = '';
        if (!empty($logoFile['name'])) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileExt = pathinfo($logoFile['name'], PATHINFO_EXTENSION);
            $fileName = $generatedCode . '_logo.' . $fileExt;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($logoFile['tmp_name'], $targetPath)) {
                $logoPath = $targetPath;
            }
        }

        $newBrand = [
            'code' => $generatedCode,
            'name' => $brandName,
            'website' => $brandWebsite,
            'intro' => $brandIntro,
            'status' => $brandStatus,
            'has_license' => $hasLicense,
            'license_file' => $licensePath,
            'logo' => $logoPath,
            'contact' => $contact,
            'date' => date('Y-m-d H:i:s')
        ];

        $brands[] = $newBrand;
        file_put_contents($brandsFile, json_encode($brands, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $msg = '品牌信息提交成功！';
        $msgType = 'success';
        $showResult = true;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册/投稿 - 钠云查询</title>
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

        .form-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 35px;
            text-align: left;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
            font-size: 15px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 14px 18px;
            font-size: 14px;
            border: 2px solid #e8e8e8;
            outline: none;
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #333;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(51, 51, 51, 0.1);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group input[type="file"] {
            padding: 12px;
            border: 2px dashed #e8e8e8;
            background: #fafafa;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-group input[type="file"]:hover {
            border-color: #333;
            background: #fff;
        }

        .file-upload-hint {
            font-size: 13px;
            color: #999;
            margin-top: 6px;
            line-height: 1.6;
        }

        .radio-group {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .radio-item input[type="radio"] {
            width: auto;
            padding: 0;
            cursor: pointer;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            color: #fff;
            background: linear-gradient(135deg, #333 0%, #555 100%);
            border: none;
            cursor: pointer;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .success-msg {
            background: #d4edda;
            color: #155724;
            padding: 18px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 10px;
            font-weight: 500;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 18px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 10px;
            font-weight: 500;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .result-box {
            background: #f0f0f0;
            padding: 25px;
            margin-top: 20px;
            text-align: center;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .result-box h2 {
            color: #333;
            margin-bottom: 18px;
            font-weight: 600;
        }

        .code-display {
            font-size: 28px;
            color: #333;
            font-weight: 600;
            padding: 20px;
            background: #fff;
            margin: 20px 0;
            border: 2px solid #333;
            border-radius: 12px;
            letter-spacing: 2px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .hidden {
            display: none;
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

        .logo-upload-group {
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2em;
            }

            .form-box {
                padding: 20px;
            }

            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">← 返回首页</a>
        
        <h1>品牌投稿</h1>

        <div class="form-box">
            <?php if ($msg): ?>
                <div class="<?php echo $msgType === 'success' ? 'success-msg' : 'error-msg'; ?>">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="brandForm" class="<?php echo $showResult ? 'hidden' : ''; ?>" enctype="multipart/form-data">
                <div class="form-group">
                    <label>品牌名称 *</label>
                    <input type="text" name="brand_name" placeholder="请输入品牌名称" required>
                </div>

                <div class="form-group logo-upload-group">
                    <label>品牌Logo *</label>
                    <input type="file" name="logo_file" accept="image/*" required>
                    <p class="file-upload-hint">提示：请上传品牌logo图片</p>
                </div>

                <div class="form-group">
                    <label>品牌官网 *</label>
                    <input type="url" name="brand_website" placeholder="请输入品牌官网" required>
                </div>

                <div class="form-group">
                    <label>品牌介绍 *</label>
                    <textarea name="brand_intro" placeholder="请输入品牌介绍" required></textarea>
                </div>

                <div class="form-group">
                    <label>品牌状态 *</label>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" name="brand_status" value="经营" required> 经营
                        </div>
                        <div class="radio-item">
                            <input type="radio" name="brand_status" value="歇业"> 歇业
                        </div>
                        <div class="radio-item">
                            <input type="radio" name="brand_status" value="停业"> 停业
                        </div>
                        <div class="radio-item">
                            <input type="radio" name="brand_status" value="跑路"> 跑路
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>品牌属于 *</label>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" name="has_license" value="yes" onchange="toggleLicenseFile(true)" required> 有营业执照
                        </div>
                        <div class="radio-item">
                            <input type="radio" name="has_license" value="no" onchange="toggleLicenseFile(false)"> 无营业执照
                        </div>
                    </div>
                </div>

                <div class="form-group" id="licenseFileGroup" style="display: none;">
                    <label>营业执照文件（无水印）*</label>
                    <input type="file" name="license_file" accept="image/*,.pdf">
                    <p class="file-upload-hint">提示：系统会自动添加"仅供钠云查询认证使用"水印</p>
                </div>

                <div class="form-group">
                    <label>品牌联系人联系方式 *</label>
                    <input type="text" name="contact" placeholder="请输入联系方式（QQ/微信/手机号）" required>
                </div>

                <button type="submit" class="submit-btn">提交</button>
            </form>

            <?php if ($showResult): ?>
                <div class="result-box">
                    <h2>品牌信息提交成功！</h2>
                    <p style="color: #666; margin-bottom: 10px;">您的专属识别码为：</p>
                    <div class="code-display"><?php echo $generatedCode; ?></div>
                    <p style="color: #999; font-size: 14px;">请妥善保存您的识别码，可用于查询品牌信息</p>
                    <br>
                    <a href="register.php" class="back-btn" style="display: inline-block; margin-top: 10px;">继续投稿</a>
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

    <script>
        function toggleLicenseFile(show) {
            var group = document.getElementById('licenseFileGroup');
            var fileInput = document.querySelector('#licenseFileGroup input');
            if (show) {
                group.style.display = 'block';
                fileInput.setAttribute('required', 'required');
            } else {
                group.style.display = 'none';
                fileInput.removeAttribute('required');
            }
        }
    </script>
</body>
</html>
