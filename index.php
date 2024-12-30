<?php
// مسیر فایل JSON برای ذخیره اطلاعات
$data_file = 'data.json';

// بارگذاری داده‌ها از فایل JSON
if (file_exists($data_file)) {
    $data = json_decode(file_get_contents($data_file), true);
} else {
    $data = ['channels' => [], 'votes' => []];
}

// پردازش درخواست‌ها
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $channel_url = $_POST['channel_url'];
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // بررسی وجود کانال در لیست
    $channel_info = getChannelInfo($channel_url);
    if ($channel_info) {
        $channel_name = $channel_info['name'];

        $channel_id = null;
        foreach ($data['channels'] as $id => $channel) {
            if ($channel['url'] === $channel_url) {
                $channel_id = $id;
                break;
            }
        }

        // اضافه کردن کانال جدید در صورت عدم وجود
        if ($channel_id === null) {
            $channel_id = uniqid();
            $data['channels'][$channel_id] = ['name' => $channel_name, 'url' => $channel_url, 'votes' => 0];
        }

        // بررسی و ثبت رای
        if (!isset($data['votes'][$ip_address]) || !in_array($channel_id, $data['votes'][$ip_address])) {
            $data['votes'][$ip_address][] = $channel_id;
            $data['channels'][$channel_id]['votes']++;
            echo "رای شما ثبت شد!";
        } else {
            echo "شما قبلاً رای داده‌اید.";
        }

        // ذخیره داده‌ها در فایل JSON
        file_put_contents($data_file, json_encode($data));
    } else {
        echo "کانال یافت نشد.";
    }
}

function getChannelInfo($url) {
    // دریافت اطلاعات کانال از طریق APIهای یوتیوب و توییچ (نمونه اولیه)
    if (strpos($url, 'youtube.com') !== false) {
        return ['name' => 'کانال یوتیوب نمونه'];
    } elseif (strpos($url, 'twitch.tv') !== false) {
        return ['name' => 'کانال توییچ نمونه'];
    }
    return null;
}

// نمایش لیست کانال‌ها و آرای آن‌ها
$channels = $data['channels'];
usort($channels, function ($a, $b) {
    return $b['votes'] - $a['votes'];
});

?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سیستم رای‌گیری</title>
</head>
<body>
    <h1>سیستم رای‌گیری</h1>
    <form method="POST">
        <label for="channel_url">لینک کانال:</label>
        <input type="url" id="channel_url" name="channel_url" required>
        <button type="submit">رای بده</button>
    </form>
    <h2>نتایج رای‌گیری:</h2>
    <ul>
        <?php foreach ($channels as $channel): ?>
            <li>
                <?php echo htmlspecialchars($channel['name']); ?>: <?php echo $channel['votes']; ?> رای
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
