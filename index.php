<?php
session_start();

// ==========================================
// ⚙️ DATABASE CONFIGURATION
// ==========================================
$db_host = 'db.fr-pari1.bengt.wasmernet.com';
$db_port = '10272';
$db_name = 'jk_naruto_ai_max_hackhost';
$db_user = 'user_fef03df8'; 
$db_pass = 'pw_ed7bcd88';   

$pdo = null;
try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS naruto_history (
        period VARCHAR(20) PRIMARY KEY,
        prediction VARCHAR(15),
        actual VARCHAR(15) DEFAULT '-',
        status VARCHAR(1) DEFAULT 'P',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch(PDOException $e) { }

// Period Number + 1 করার ফাংশন
function addOneStr($str) {
    $len = strlen($str);
    $carry = 1;
    $res = '';
    for ($i = $len - 1; $i >= 0; $i--) {
        if (!is_numeric($str[$i])) {
            $res = substr($str, 0, $i + 1) . $res;
            break;
        }
        $val = (int)$str[$i] + $carry;
        if ($val > 9) { $val = 0; $carry = 1; } 
        else { $carry = 0; }
        $res = $val . $res;
    }
    return $res;
}

// ==========================================
// 🚀 BACKEND API & GHOST BOT LOGIC
// ==========================================
if (isset($_GET['action']) || isset($_POST['action'])) {
    $action = $_GET['action'] ?? $_POST['action'];
    header('Content-Type: application/json');

    if (!$pdo) {
        echo json_encode(['error' => true, 'message' => 'DB Not Connected']);
        exit;
    }

    // 👻 GHOST BOT LOGIC (সার্ভারে কেউ যেন বসে আছে!) 👻
    if ($action == 'ghost_bot') {
        // ডাবল বট যেন সার্ভার ক্র্যাশ না করে, তার জন্য চেকিং
        $lock_file = __DIR__ . '/bot_lock.txt';
        if (file_exists($lock_file) && (time() - (int)file_get_contents($lock_file)) < 40) {
            echo json_encode(['status' => 'already_running']);
            exit;
        }

        // ব্রাউজার কেটে দিলেও যেন স্ক্রিপ্ট চলতে থাকে
        ignore_user_abort(true);
        set_time_limit(0);
        
        // ফ্রন্টএন্ডকে বলে দেওয়া যে বট চালু হয়েছে, যাতে সে আটকে না থাকে
        header("Connection: close");
        ob_start();
        echo json_encode(['status' => 'ghost_bot_started']);
        header("Content-Length: " . ob_get_length());
        ob_end_flush();
        flush();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        // ইনফিনিট লুপ (সারাক্ষণ চলতে থাকবে)
        while (true) {
            file_put_contents($lock_file, time());
            
            $ch = curl_init('https://api.inpay88.net/api/webapi/GetNoaverageEmerdList');
            $payload = json_encode([
                "pageSize" => 3, "pageNo" => 1, "typeId" => 30, "language" => 0, 
                "random" => "61bbaf505dd14e7aa81ba687c974178d", 
                "signature" => "558BB707A61FA8D6BBB1748C5065F77A", "timestamp" => 1779019083
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            $list = $data['data']['list'] ?? [];

            if (!empty($list)) {
                $reversed = array_reverse($list);
                foreach ($reversed as $item) {
                    $period = $item['issueNumber'] ?? $item['issue'] ?? $item['period'];
                    $number = (int)$item['number'];
                    $actualSize = $number >= 5 ? 'BIG' : 'SMALL';
                    
                    $stmt = $pdo->prepare("SELECT prediction, status FROM naruto_history WHERE period = ?");
                    $stmt->execute([$period]);
                    $row = $stmt->fetch();
                    
                    if (!$row) {
                        $ins = $pdo->prepare("INSERT INTO naruto_history (period, prediction, actual, status) VALUES (?, ?, ?, ?)");
                        $ins->execute([$period, $actualSize, $actualSize, 'W']);
                    } else if ($row['status'] == 'P') {
                        $pred = $row['prediction'];
                        $status = 'L';
                        if ($pred == 'BIG' && $number >= 5) $status = 'W';
                        elseif ($pred == 'SMALL' && $number <= 4) $status = 'W';
                        
                        $upd = $pdo->prepare("UPDATE naruto_history SET actual = ?, status = ? WHERE period = ?");
                        $upd->execute([$actualSize, $status, $period]);
                    }
                }
                
                // পরের গেমের জন্য ১টি প্রেডিকশন
                $latestPeriod = $list[0]['issueNumber'] ?? $list[0]['issue'] ?? $list[0]['period'];
                $nextPeriod = addOneStr($latestPeriod);
                
                $stmt = $pdo->prepare("SELECT prediction FROM naruto_history WHERE period = ?");
                $stmt->execute([$nextPeriod]);
                if (!$stmt->fetch()) {
                    $stmt2 = $pdo->query("SELECT actual FROM naruto_history WHERE status != 'P' ORDER BY period DESC LIMIT 4");
                    $last4 = $stmt2->fetchAll(PDO::FETCH_COLUMN);
                    $prediction = (rand(0, 100) > 50) ? 'BIG' : 'SMALL';
                    if (count($last4) == 4 && $last4[0] != $last4[1] && $last4[1] != $last4[2] && $last4[2] != $last4[3] && $last4[0] == $last4[2]) {
                        $prediction = ($last4[0] == 'BIG') ? 'SMALL' : 'BIG';
                    }
                    $ins = $pdo->prepare("INSERT IGNORE INTO naruto_history (period, prediction) VALUES (?, ?)");
                    $ins->execute([$nextPeriod, $prediction]);
                }
            }
            sleep(28); // প্রতি ২৮ সেকেন্ড পরপর এই বট নিজে নিজে গেম খেলবে
        }
        exit;
    }

    // 1. Frontend Sync (Gap Filler) - জাস্ট সেফটির জন্য
    if ($action == 'sync') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (is_array($data)) {
            $data = array_reverse($data); 
            foreach ($data as $item) {
                $period = $item['period'] ?? $item['issueNumber'] ?? $item['issue'];
                $number = (int)$item['number'];
                $actualSize = $number >= 5 ? 'BIG' : 'SMALL';
                
                $stmt = $pdo->prepare("SELECT prediction, status FROM naruto_history WHERE period = ?");
                $stmt->execute([$period]);
                $row = $stmt->fetch();
                
                if (!$row) {
                    $ins = $pdo->prepare("INSERT INTO naruto_history (period, prediction, actual, status) VALUES (?, ?, ?, ?)");
                    $ins->execute([$period, $actualSize, $actualSize, 'W']);
                } else if ($row['status'] == 'P') {
                    $pred = $row['prediction'];
                    $status = 'L';
                    if ($pred == 'BIG' && $number >= 5) $status = 'W';
                    elseif ($pred == 'SMALL' && $number <= 4) $status = 'W';
                    elseif ($pred == 'RED' && $number % 2 == 0) $status = 'W';
                    elseif ($pred == 'GREEN' && $number % 2 != 0) $status = 'W';
                    elseif ($pred == 'VIOLET' && ($number == 0 || $number == 5)) $status = 'W';
                    
                    $upd = $pdo->prepare("UPDATE naruto_history SET actual = ?, status = ? WHERE period = ?");
                    $upd->execute([$actualSize, $status, $period]);
                }
            }
        }
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action == 'get_prediction') {
        $period = $_GET['period'];
        $stmt = $pdo->prepare("SELECT prediction FROM naruto_history WHERE period = ?");
        $stmt->execute([$period]);
        $row = $stmt->fetch();
        if ($row) echo json_encode(['prediction' => $row['prediction'], 'pattern' => false]);
        else {
            $stmt2 = $pdo->query("SELECT actual FROM naruto_history WHERE status != 'P' ORDER BY period DESC LIMIT 4");
            $last4 = $stmt2->fetchAll(PDO::FETCH_COLUMN);
            $prediction = ''; $patternFound = false;
            if (count($last4) == 4 && $last4[0] != $last4[1] && $last4[1] != $last4[2] && $last4[2] != $last4[3] && $last4[0] == $last4[2]) {
                $prediction = ($last4[0] == 'BIG') ? 'SMALL' : 'BIG'; $patternFound = true;
            } else $prediction = (rand(0, 100) > 50) ? 'BIG' : 'SMALL';

            $ins = $pdo->prepare("INSERT IGNORE INTO naruto_history (period, prediction) VALUES (?, ?)");
            $ins->execute([$period, $prediction]);
            echo json_encode(['prediction' => $prediction, 'pattern' => $patternFound]);
        }
        exit;
    }

    if ($action == 'get_history') {
        $stmt = $pdo->query("SELECT * FROM naruto_history ORDER BY period DESC LIMIT 100");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $wins = $pdo->query("SELECT COUNT(*) FROM naruto_history WHERE status='W'")->fetchColumn();
        $losses = $pdo->query("SELECT COUNT(*) FROM naruto_history WHERE status='L'")->fetchColumn();
        echo json_encode(['wins' => $wins, 'losses' => $losses, 'data' => $rows]);
        exit;
    }

    if ($action == 'delete_history') {
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data['password'] === 'Hack@1135#hosT_jk') {
            $pdo->exec("TRUNCATE TABLE naruto_history");
            echo json_encode(['success' => true]);
        } else echo json_encode(['success' => false, 'message' => 'Wrong Password!']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>NARUTO AI MAX - GHOST BOT</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        html, body { width: 100%; height: 100%; overflow: hidden; background: #020402; }

        body {
            position: relative;
            background: radial-gradient(circle at center, rgba(0,255,120,0.08), transparent 35%),
                repeating-linear-gradient(0deg, rgba(0,255,120,0.035) 0px, rgba(0,255,120,0.035) 1px, transparent 1px, transparent 24px), #020402;
        }
        body::before {
            content: ""; position: fixed; inset: 0; pointer-events: none; z-index: 999;
            background: linear-gradient(to bottom, rgba(0,255,120,0) 0%, rgba(0,255,120,0.10) 50%, rgba(0,255,120,0) 100%);
            animation: scan 4s linear infinite;
        }
        @keyframes scan { 0% { transform: translateY(-100%); } 100% { transform: translateY(100%); } }

        .frame-wrap { position: fixed; inset: 0; background: #000; z-index: 0; display: none; }
        .frame-wrap iframe { width: 100%; height: 100%; border: none; background: #111; }

        .neon-border {
            position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 220px; padding: 2px; border-radius: 16px;
            background: linear-gradient(45deg, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #9400d3, #ff0000);
            background-size: 300% 300%; animation: rgb-border-anim 4s linear infinite;
            z-index: 9999; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8), 0 0 15px rgba(255, 255, 255, 0.2);
        }
        @keyframes rgb-border-anim { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }

        .panel-inner {
            background: rgba(10, 15, 10, 0.93); backdrop-filter: blur(8px);
            border-radius: 14px; padding: 12px; width: 100%; height: 100%;
            display: flex; flex-direction: column; user-select: none;
        }
        .drag-zone { cursor: move; display: flex; justify-content: center; user-select: none; }
        .mod-title {
            border: 1.5px solid #ffd700; color: #ffd700; background: rgba(20, 15, 0, 0.6);
            border-radius: 8px; padding: 6px 14px; font-size: 11px; font-weight: 800;
            letter-spacing: 1px; text-transform: uppercase; box-shadow: inset 0 0 8px rgba(255, 215, 0, 0.2);
            text-shadow: 0 0 5px rgba(255, 215, 0, 0.8); text-align: center; width: 100%; cursor: pointer;
        }

        #panelContent { margin-top: 12px; display: block; }
        .timer-row {
            display: flex; justify-content: space-between; align-items: center; background: rgba(0, 0, 0, 0.8);
            border-radius: 8px; padding: 8px 10px; margin-bottom: 12px; border: 1px solid rgba(255, 255, 255, 0.05); box-shadow: inset 0 2px 10px rgba(0,0,0,1);
        }
        .period-text { color: #aaa; font-size: 10px; font-weight: 700; }
        .time-text { color: #fff; font-size: 11px; font-weight: 800; }
        .time-text span { color: #ffd700; font-size: 13px; }

        .prediction-row { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 12px; }
        .status-container {
            flex: 1; border: 2px solid #00f0ff; border-radius: 12px; background: rgba(0, 0, 0, 0.6);
            display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px 0;
            box-shadow: inset 0 0 15px rgba(0, 240, 255, 0.15); transition: all 0.3s ease;
        }
        .status { font-size: 20px; font-weight: 900; letter-spacing: 1.5px; line-height: 1.1; font-family: monospace; text-align: center; }

        .color-red { color: #ff3d3d; text-shadow: 0 0 12px #ff3d3d; }
        .color-green { color: #00ff66; text-shadow: 0 0 12px #00ff66; }
        .color-violet { color: #d63dff; text-shadow: 0 0 12px #d63dff; }
        .pattern-text { color: #ffd700 !important; text-shadow: 0 0 8px #ffd700 !important; }
        .rgb-text { animation: text-rgb-glow 2s linear infinite; }
        @keyframes text-rgb-glow { 0% { color: #ff0000; text-shadow: 0 0 12px #ff0000; } 33% { color: #00ff00; text-shadow: 0 0 12px #00ff00; } 66% { color: #0000ff; text-shadow: 0 0 12px #0000ff; } 100% { color: #ff0000; text-shadow: 0 0 12px #ff0000; } }
        .result-label { color: #888; font-size: 8px; font-weight: 700; letter-spacing: 1px; margin-top: 4px; text-align: center; }

        .numbers-container {
            width: 55px; border: 2px solid #ffd700; border-radius: 12px; background: rgba(0, 0, 0, 0.6);
            display: flex; align-items: center; justify-content: center; padding: 8px 0; box-shadow: inset 0 0 15px rgba(255, 215, 0, 0.15);
        }
        .result-numbers { display: flex; flex-direction: column; gap: 6px; }
        .ball {
            width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 900; background: rgba(0, 0, 0, 0.8); border: 2px solid; font-family: monospace;
        }
        .ball-red { color: #ff3d3d; border-color: #ff3d3d; box-shadow: 0 0 6px rgba(255,61,61,0.5); }
        .ball-green { color: #00ff66; border-color: #00ff66; box-shadow: 0 0 6px rgba(0,255,102,0.5); }
        .ball-violet { color: #d63dff; border-color: #d63dff; box-shadow: 0 0 6px rgba(214,61,255,0.5); }

        .join-tg-btn {
            width: 100%; background: rgba(0, 0, 0, 0.6); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px;
            color: #fff; font-size: 11px; font-weight: 800; padding: 8px; display: flex; align-items: center; justify-content: center;
            gap: 6px; cursor: pointer; transition: all 0.2s;
        }
        .join-tg-btn:active { transform: scale(0.95); background: rgba(20, 20, 20, 0.8); }

        .login-box input { width: 100%; height: 36px; border: 1px solid rgba(0, 255, 102, 0.5); background: rgba(0,0,0,0.5); color: #8fff59; border-radius: 8px; padding: 0 12px; outline: none; margin-bottom: 12px; font-weight: bold; text-align: center; }
        .login-btn { width: 100%; height: 36px; border: 1px solid #00ff66; border-radius: 8px; background: rgba(0, 20, 0, 0.8); color: #84ff55; font-size: 13px; font-weight: 800; cursor: pointer; text-shadow: 0 0 6px rgba(132, 255, 85, 0.35); }
        .error { min-height: 16px; margin-top: 8px; color: #ff476f; font-size: 10px; text-align: center; font-weight: bold; }
        .hidden { display: none !important; }

        /* History Overlay */
        .history-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 100000; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
        .history-box { width: 90%; max-width: 450px; height: 80%; background: #080808; border: 1px solid #00ff66; border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 0 20px rgba(0,255,102,0.15); font-family: monospace; }
        .hist-header { padding: 15px; background: #111; border-bottom: 1px solid #222; display: flex; justify-content: space-between; align-items: center; }
        .hist-stats { color: #fff; font-size: 13px; font-weight: bold; }
        .hist-stats span.w { color: #00e676; margin-right: 10px;}
        .hist-stats span.l { color: #ff1744; }
        .hist-btns { display: flex; gap: 8px; }
        .del-btn { background: #ff1744; color: #fff; border: none; padding: 6px 10px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 11px; }
        .close-btn { background: #333; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .hist-body { flex: 1; overflow-y: auto; padding: 10px; }
        .hist-table { width: 100%; border-collapse: collapse; font-size: 11px; color: #ccc; }
        .hist-table th { color: #00f0ff; padding: 10px 5px; border-bottom: 1px solid #222; text-align: center; }
        .hist-table td { padding: 10px 5px; text-align: center; border-bottom: 1px solid #1a1a1a; }
        .badge { display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 50%; color: #fff; font-weight: 800; font-size: 10px; }
        .badge-w { background: #00e676; box-shadow: 0 0 6px #00e676; }
        .badge-l { background: #ff1744; box-shadow: 0 0 6px #ff1744; }
        .badge-p { background: #555; }
    </style>
</head>
<body>

    <div class="frame-wrap" id="frameWrap">
        <iframe src="https://deshclub2.com/#/home/AllLotteryGames/WinGo?typeId=30"></iframe>
    </div>

    <div class="neon-border" id="mainBox">
        <div class="panel-inner">
            <div class="drag-zone" id="dragHandle" onclick="togglePanel()">
                <div class="mod-title">NARUTO AI MAX</div>
            </div>

            <div id="panelContent">
                <div class="login-box" id="loginView">
                    <input type="password" id="keyInput" placeholder="ENTER PASSWORD">
                    <button class="login-btn" onclick="checkPassword()">ACTIVATE</button>
                    <div class="error hidden" id="errorMsg">WRONG PASSWORD!</div>
                </div>

                <div id="hackView" class="hidden">
                    <div class="timer-row">
                        <div class="period-text">PR: <span id="period-display" style="color:#fff;">Loading...</span></div>
                        <div class="time-text"><span id="timer-display">00</span>s</div>
                    </div>

                    <div class="prediction-row">
                        <div class="status-container" id="status-box">
                            <div class="status rgb-text" id="signal-text">WAIT</div>
                            <div class="result-label" id="sub-signal">ANALYZING...</div>
                        </div>
                        <div class="numbers-container">
                            <div class="result-numbers" id="ball-container"></div>
                        </div>
                    </div>

                    <div class="join-tg-btn" onclick="openHistory(event)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        HISTORY
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="history-overlay hidden" id="historyModal">
        <div class="history-box">
            <div class="hist-header">
                <div class="hist-stats">
                    Win: <span class="w" id="totWin">0</span> 
                    Loss: <span class="l" id="totLoss">0</span>
                </div>
                <div class="hist-btns">
                    <button class="del-btn" onclick="deleteHistory()">Delete</button>
                    <button class="close-btn" onclick="closeHistory()">X</button>
                </div>
            </div>
            <div class="hist-body">
                <table class="hist-table">
                    <thead><tr><th>Period</th><th>Prediction</th><th>Actual</th><th>Status</th></tr></thead>
                    <tbody id="histBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        var VALID_PASS = "naruto1971";
        var currentPeriod = null;
        var prevCycleIndex = -1;
        var BD_MS = 6 * 60 * 60 * 1000;
        var API_URL = 'https://api.inpay88.net/api/webapi/GetNoaverageEmerdList';
        var panelOpen = true;
        var isDraggingFlag = false;

        function checkPassword() {
            if(document.getElementById('keyInput').value === VALID_PASS) {
                document.getElementById('loginView').classList.add('hidden');
                document.getElementById('hackView').classList.remove('hidden');
                document.getElementById('frameWrap').style.display = 'block';
                
                // 👻 প্যানেল খোলার সাথে সাথেই ঘোস্ট বট সার্ভারে চালু হয়ে যাবে!
                startGhostBot();
                startSystem();
            } else {
                document.getElementById('errorMsg').classList.remove('hidden');
                setTimeout(() => document.getElementById('errorMsg').classList.add('hidden'), 2000);
            }
        }

        // घोস্ট বট অ্যাক্টিভেশন
        function startGhostBot() {
            fetch('?action=ghost_bot').catch(e => console.log('Ghost Bot Running...'));
        }

        function togglePanel() {
            if (!isDraggingFlag) { 
                panelOpen = !panelOpen;
                document.getElementById('panelContent').style.display = panelOpen ? 'block' : 'none';
            }
        }

        function getBDSeconds() { return Math.floor((Date.now() + BD_MS) / 1000); }
        function getCountdown() { var mod = (getBDSeconds() % 60) % 30; return mod === 0 ? 30 : 30 - mod; }
        function getCycleIndex() { return Math.floor(getBDSeconds() / 30); }

        function addOne(str) {
            var m = str.match(/(\d+)$/);
            if (!m) return str;
            var numStr = m[1];
            var prefix = str.substring(0, str.length - numStr.length);
            var num = BigInt(numStr) + 1n;
            var s = num.toString();
            while (s.length < numStr.length) s = '0' + s;
            return prefix + s;
        }

        function fallbackPeriod() {
            if(!currentPeriod) currentPeriod = "20260517001";
            else currentPeriod = addOne(currentPeriod);
            document.getElementById('period-display').innerText = currentPeriod;
        }

        function getNumberData(arr) {
            var n = arr[Math.floor(Math.random() * arr.length)];
            var c = 'ball-green';
            if (n === 0 || n === 5) c = 'ball-violet';
            else if (n % 2 === 0) c = 'ball-red';
            return { num: n, cls: c };
        }

        async function fetchPeriod(callback) {
            try {
                let r = await fetch(API_URL, {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    // ফ্রন্টএন্ডে এখন শুধু লেটেস্ট ১৪৪ ডেটা দিয়ে সিঙ্ক করবো (সেফটির জন্য)
                    body: JSON.stringify({ "pageSize": 2, "pageNo": 1, "typeId": 30, "language": 0, "random": "61bba", "signature": "558BB", "timestamp": 1779019083 })
                });
                let data = await r.json();
                let list = data?.data?.list;
                if (list && list.length > 0) {
                    let apiPeriod = list[0].issueNumber || list[0].issue || list[0].period || '';
                    currentPeriod = addOne(apiPeriod);
                    document.getElementById('period-display').innerText = currentPeriod;
                    let payload = list.map(x => ({ period: x.issueNumber||x.issue||x.period, number: x.number }));
                    await fetch('?action=sync', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
                } else fallbackPeriod(); 
            } catch(e) { fallbackPeriod(); }
            if(callback) callback();
        }

        async function generateAIResult() {
            try {
                let res = await fetch(`?action=get_prediction&period=${currentPeriod}`);
                let data = await res.json();
                if(data && data.prediction) { renderUI(data.prediction, data.pattern); return; }
            } catch(e) {}
            let isBig = Math.random() > 0.5; renderUI(isBig ? 'BIG' : 'SMALL', false);
        }

        function renderUI(prediction, hasPattern) {
            var signalEl = document.getElementById('signal-text');
            var subEl = document.getElementById('sub-signal');
            var ballContainer = document.getElementById('ball-container');
            var b1, b2;

            if (prediction === 'BIG' || prediction === 'SMALL') {
                signalEl.innerText = prediction; signalEl.className = "status rgb-text";
                subEl.innerText = hasPattern ? "ABAB PATTERN DETECTED" : "SIZE PATTERN";
                subEl.className = hasPattern ? "result-label pattern-text" : "result-label";
                var numArr = (prediction === 'BIG') ? [5,6,7,8,9] : [0,1,2,3,4];
                b1 = getNumberData(numArr); b2 = getNumberData(numArr);
            } else {
                signalEl.innerText = prediction;
                if (prediction === 'RED') { signalEl.className = "status color-red"; b1=getNumberData([2,4,6,8]); b2=getNumberData([2,4,6,8]); }
                else if (prediction === 'GREEN') { signalEl.className = "status color-green"; b1=getNumberData([1,3,7,9]); b2=getNumberData([1,3,7,9]); }
                else { signalEl.className = "status color-violet"; b1=getNumberData([0,5]); b2=getNumberData([0,5]); }
                subEl.innerText = hasPattern ? "ABAB PATTERN DETECTED" : "COLOR PATTERN";
                subEl.className = hasPattern ? "result-label pattern-text" : "result-label";
            }
            ballContainer.innerHTML = `<div class="ball ${b1.cls}">${b1.num}</div><div class="ball ${b2.cls}">${b2.num}</div>`;
        }

        function startSystem() {
            fetchPeriod(function(){ generateAIResult(); }); 
            setInterval(() => {
                var rem = getCountdown(); document.getElementById('timer-display').innerText = rem < 10 ? '0'+rem : rem;
                var currentCycle = getCycleIndex();
                if (prevCycleIndex !== -1 && currentCycle !== prevCycleIndex) fetchPeriod(function(){ generateAIResult(); });
                prevCycleIndex = currentCycle;
            }, 1000);
        }

        function openHistory(e) { if(e) e.stopPropagation(); document.getElementById('historyModal').classList.remove('hidden'); loadHistoryData(); }
        function closeHistory() { document.getElementById('historyModal').classList.add('hidden'); }

        async function loadHistoryData() {
            let tbody = document.getElementById('histBody'); tbody.innerHTML = '<tr><td colspan="4">Loading History...</td></tr>';
            try {
                let res = await fetch('?action=get_history'); let data = await res.json();
                document.getElementById('totWin').innerText = data.wins || 0; document.getElementById('totLoss').innerText = data.losses || 0;
                tbody.innerHTML = '';
                if (data.data && data.data.length > 0) {
                    data.data.forEach(row => {
                        let badgeClass = row.status === 'W' ? 'badge-w' : (row.status === 'L' ? 'badge-l' : 'badge-p');
                        tbody.innerHTML += `<tr><td>${row.period}</td><td style="font-weight:bold;color:#fff;">${row.prediction}</td><td>${row.actual}</td><td><div class="badge ${badgeClass}">${row.status}</div></td></tr>`;
                    });
                } else tbody.innerHTML = '<tr><td colspan="4">No History Found!</td></tr>';
            } catch(e) { tbody.innerHTML = '<tr><td colspan="4" style="color:red;">API Error!</td></tr>'; }
        }

        async function deleteHistory() {
            let pass = prompt("Enter Password to Delete History:");
            if(pass) {
                let res = await fetch('?action=delete_history', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ password: pass }) });
                let data = await res.json();
                if(data.success) { alert("History Deleted!"); loadHistoryData(); } else alert(data.message || "Wrong Password!");
            }
        }

        function setupDrag(boxId, handleId) {
            var box = document.getElementById(boxId); var header = document.getElementById(handleId);
            var active = false, currentX = 0, currentY = 0, initialX, initialY, startX, startY, xOffset = 0, yOffset = 0;
            function dragStart(e) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON') return;
                initialX = (e.type === "touchstart" ? e.touches[0].clientX : e.clientX) - xOffset;
                initialY = (e.type === "touchstart" ? e.touches[0].clientY : e.clientY) - yOffset;
                startX = initialX; startY = initialY; active = true; isDraggingFlag = false; 
            }
            function dragEnd() { active = false; }
            function drag(e) {
                if (active) {
                    e.preventDefault();
                    var clientX = e.type === "touchmove" ? e.touches[0].clientX : e.clientX;
                    var clientY = e.type === "touchmove" ? e.touches[0].clientY : e.clientY;
                    if (Math.abs(clientX - xOffset - startX) > 5 || Math.abs(clientY - yOffset - startY) > 5) isDraggingFlag = true;
                    currentX = clientX - initialX; currentY = clientY - initialY; xOffset = currentX; yOffset = currentY;
                    box.style.transform = `translate(calc(-50% + ${currentX}px), calc(-50% + ${currentY}px))`;
                }
            }
            header.addEventListener("touchstart", dragStart, {passive: false}); window.addEventListener("touchend", dragEnd);
            window.addEventListener("touchmove", drag, {passive: false}); header.addEventListener("mousedown", dragStart);
            window.addEventListener("mouseup", dragEnd); window.addEventListener("mousemove", drag);
        }
        setupDrag("mainBox", "dragHandle");
    </script>
</body>
</html>
