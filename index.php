<?php
error_reporting(0);
@ini_set('display_errors', 0);
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// 🔒 মাস্টার পাসওয়ার্ড
$MASTER_PASSWORD = "naruto099";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cipher_key']) && $_POST['cipher_key'] === $MASTER_PASSWORD) {
        $_SESSION['is_hacker_verified'] = true;
    } else {
        $error_msg = "INVALID KEY!";
    }
}
$isLoggedIn = isset($_SESSION['is_hacker_verified']) && $_SESSION['is_hacker_verified'] === true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>NARUTO AI PRO</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800;900&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; user-select: none; }
        body { overflow: hidden; height: 100vh; width: 100vw; background: #000; font-family: 'Rajdhani', sans-serif; }
        iframe#bg-site { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; z-index: 1; }

        /* 🟣 VIP PRO PANEL DESIGN */
        #hack-overlay {
            position: absolute; top: 10%; right: 5%; width: 220px; z-index: 9999;
            background: #100b1a; border-radius: 12px; padding: 10px;
            border: 1px solid rgba(138, 43, 226, 0.6);
            box-shadow: 0 0 20px rgba(138, 43, 226, 0.4), inset 0 0 10px rgba(0, 0, 0, 0.8);
            color: #fff; transform: scale(0.95); transform-origin: top right;
        }

        /* 🖐️ SMART DRAG PAD */
        .drag-header { width: 60%; height: 80px; position: absolute; top: 0; left: 0; cursor: move; z-index: 20; }
        .drag-header::after { content: ''; width: 25px; height: 4px; background: rgba(0, 242, 255, 0.4); border-radius: 10px; position: absolute; top: 10px; left: 15px; }

        /* HEADER */
        .top-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 6px; margin-bottom: 8px; position: relative; z-index: 10;}
        .top-title { font-family: 'Orbitron'; font-size: 10px; font-weight: 900; color: #fff; display: flex; align-items: center; gap: 5px; text-transform: uppercase;}
        .top-title img { width: 18px; height: 18px; border-radius: 50%; }
        
        .btn-group { display: flex; gap: 5px; position: relative; z-index: 30; }
        .icon-btn { background: rgba(255,255,255,0.1); border: none; border-radius: 50%; width: 22px; height: 22px; color: #fff; font-size: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s;}
        .icon-btn:active { transform: scale(0.9); }
        .icon-btn.active { background: rgba(0,255,136,0.2); color: #00ff88; box-shadow: 0 0 8px #00ff88; }
        .icon-btn.off { background: rgba(255,0,51,0.2); color: #ff0033; box-shadow: 0 0 8px #ff0033; }

        .section-box { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; padding: 8px; margin-bottom: 8px; }

        /* TIMERS & CIRCLES */
        .sync-row { display: flex; justify-content: space-between; font-size: 8px; font-family: 'Orbitron'; font-weight: bold; margin-bottom: 5px;}
        .period-text { color: #e0e0e0; }
        .period-text span { color: #f7b924; }
        .timer-text { color: #ff00ff; text-shadow: 0 0 5px #ff00ff; }
        .timer-text span { color: #00ff88; text-shadow: 0 0 5px #00ff88;}

        .progress-container { width: 100%; height: 4px; background: rgba(0,0,0,0.5); border-radius: 2px; margin-bottom: 10px; overflow: hidden; border: 1px solid rgba(0, 242, 255, 0.2); }
        .progress-bar { height: 100%; width: 100%; background: linear-gradient(90deg, #00f2ff, #4169e1, #8a2be2, #00f2ff); background-size: 200% 100%; animation: pBarGlow 2s linear infinite; transition: width 1s linear; }
        @keyframes pBarGlow { 0% { background-position: 0% 0; } 100% { background-position: 100% 0; } }

        .display-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; padding: 0 5px;}
        .circles { display: flex; gap: 8px; }
        .circle { width: 32px; height: 32px; border-radius: 50%; border: 2px solid #00ff88; color: #00ff88; display: flex; align-items: center; justify-content: center; font-family: 'Orbitron'; font-size: 14px; font-weight: 900; box-shadow: 0 0 10px rgba(0,255,136,0.5), inset 0 0 5px rgba(0,255,136,0.5); }
        
        .prediction-box { display: flex; flex-direction: column; align-items: flex-end; }
        .pred-size { font-family: 'Orbitron'; font-size: 24px; font-weight: 900; color: #fff; text-shadow: 0 0 10px #fff; line-height: 1;}

        .text-red { color: #ff0044; text-shadow: 0 0 15px #ff0044; }
        .text-green { color: #00ff88; text-shadow: 0 0 15px #00ff88; }
        .text-skip { color: #f7b924 !important; text-shadow: 0 0 15px #f7b924 !important; font-size: 18px !important;}
        .text-panic { color: #ff00ff !important; text-shadow: 0 0 15px #ff00ff !important; font-size: 16px !important; animation: none !important;}
        @keyframes rgbGlow { 0%{color:#ff0000;text-shadow:0 0 15px #ff0000} 33%{color:#0000ff;text-shadow:0 0 15px #0000ff} 66%{color:#00ff00;text-shadow:0 0 15px #00ff00} 100%{color:#ff0000;text-shadow:0 0 15px #ff0000} }
        .text-rainbow { animation: rgbGlow 1.5s linear infinite; }

        /* HISTORY & ACCURACY */
        .mid-row { display: flex; justify-content: space-between; }
        .history-box { flex: 1; }
        .history-title { font-size: 7px; color: #aaa; margin-bottom: 4px; display: flex; align-items: center; gap: 4px;}
        .hist-list { font-size: 7px; font-family: 'Orbitron'; color: #ccc; display: flex; flex-direction: column; gap: 2px; height: 55px; overflow-y: auto; padding-right: 2px;}
        .hist-list::-webkit-scrollbar { width: 2px; }
        .hist-list::-webkit-scrollbar-thumb { background: #8a2be2; border-radius: 2px; }
        .hist-item span.w { color: #00ff88; }
        .hist-item span.l { color: #ff0044; }

        .acc-box { text-align: right; }
        .acc-val { font-family: 'Orbitron'; font-size: 18px; font-weight: 900; color: #fff; line-height: 1.1;}
        .acc-high { color: #00ff88 !important; text-shadow: 0 0 10px rgba(0,255,136,0.5) !important; }
        .acc-mid { color: #f7b924 !important; text-shadow: 0 0 10px rgba(247,185,36,0.5) !important; }
        .acc-low { color: #ff0044 !important; text-shadow: 0 0 10px rgba(255,0,68,0.5) !important; }
        .acc-dash { width: 100%; height: 2px; border-bottom: 2px dashed #8a2be2; margin-top: 2px;}

        /* STATS & BALANCE */
        .stats-box { font-size: 8px; font-family: 'Orbitron'; font-weight: bold; margin-bottom: 8px; display: flex; flex-direction: column; gap: 2px;}
        .stat-line { display: flex; justify-content: space-between; }
        .stat-lbl { color: #aaa; }
        .stat-val { color: #fff; }

        .balance-section { display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.4); padding: 5px; border-radius: 6px; border: 1px solid #333;}
        .bal-lbl { font-size: 7px; color: #00f2ff; font-family: 'Orbitron'; font-weight: bold;}
        .bal-input { width: 55px; background: #000; border: 1px solid #8a2be2; color: #fff; text-align: center; border-radius: 4px; font-family: 'Orbitron'; font-size: 10px; padding: 2px; outline: none;}
        .step-meter { font-size: 9px; font-family: 'Orbitron'; font-weight: bold; color: #f7b924; }

        /* LOGIN FORM */
        .cipher-input { width: 100%; padding: 10px; background: rgba(0,0,0,0.5); border: 1px solid #8a2be2; color: #fff; text-align: center; font-size: 10px; margin-bottom: 10px; outline: none; border-radius: 6px; font-family: 'Orbitron'; }
        .btn-start { width: 100%; padding: 10px; border: none; border-radius: 6px; font-family: 'Orbitron'; font-weight: 900; font-size: 11px; cursor: pointer; color: #fff; background: #8a2be2; box-shadow: 0 0 10px rgba(138, 43, 226, 0.5); transition: 0.2s;}
        .btn-start:active { transform: scale(0.95); }
        .error { color: #ff0044; font-size: 8px; margin-bottom: 6px; font-weight: bold; text-align: center;}

        /* Toast Message */
        .toast-msg { position: fixed; top: 20px; left: 50%; transform: translateX(-50%) translateY(-50px); background: rgba(10, 10, 15, 0.95); border: 1px solid #00f2ff; color: #fff; padding: 12px 15px; border-radius: 8px; font-family: 'Rajdhani', sans-serif; font-size: 14px; font-weight: 700; box-shadow: 0 0 20px rgba(0, 242, 255, 0.4); z-index: 100000; opacity: 0; transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55); pointer-events: none; text-align: center; width: 85%; max-width: 350px; }
        .toast-msg.show { transform: translateX(-50%) translateY(0); opacity: 1; }
        .toast-msg span { color: #00f2ff; font-family: 'Orbitron'; font-size: 11px; display: block; margin-bottom: 4px;}
        .toast-red { border-color: #ff0044; box-shadow: 0 0 20px rgba(255,0,68,0.4); }
        .toast-red span { color: #ff0044; }
    </style>
</head>
<body>
    <iframe id="bg-site" src="https://deshclub2.com/#/home/AllLotteryGames/WinGo?typeId=30"></iframe>

    <div id="naruto-toast" class="toast-msg">
        <span>🤖 NARUTO AI SAYS:</span>
        <div id="toast-msg-text">Message</div>
    </div>

    <div id="hack-overlay">
        <div class="drag-header" id="dragHeader"></div>

        <?php if (!$isLoggedIn): ?>
            <div class="top-header">
                <div class="top-title"><img src="naruto.jpg" onerror="this.src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='"> NARUTO AI PRO</div>
            </div>
            <form method="POST" action="">
                <input type="password" name="cipher_key" class="cipher-input" placeholder="ACCESS KEY" required autocomplete="off">
                <?php if($error_msg): ?> <div class="error"><?php echo $error_msg; ?></div> <?php endif; ?>
                <button type="submit" class="btn-start">INITIALIZE</button>
            </form>
        <?php else: ?>
            
            <div id="dynamic-container">
                <div class="top-header">
                    <div class="top-title"><img src="naruto.jpg" onerror="this.src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='"> NARUTO AI PRO</div>
                    <div class="btn-group">
                        <button id="voiceBtn" class="icon-btn active" onclick="toggleVoice()">🔊</button>
                    </div>
                </div>

                <div class="section-box">
                    <div class="sync-row">
                        <div class="period-text">⚡ PERIOD: <span id="period-display">----</span></div>
                        <div class="timer-text">NEXT IN: <span id="countdown">30</span></div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-bar" id="sync-progress"></div>
                    </div>

                    <div class="display-row">
                        <div class="circles">
                            <div class="circle" id="circle1">?</div>
                            <div class="circle" id="circle2">?</div>
                        </div>
                        <div class="prediction-box">
                            <div id="prediction-text" class="pred-size tap-start-btn" onclick="initSystemManually()">TAP TO SCAN</div>
                        </div>
                    </div>
                </div>

                <div class="section-box">
                    <div class="mid-row" style="background: transparent; padding: 0; margin: 0; border: none;">
                        <div class="history-box">
                            <div class="history-title">📜 HISTORY</div>
                            <div class="hist-list" id="history-list"></div>
                        </div>
                        <div class="acc-box">
                            <div class="history-title" style="justify-content: flex-end;">🎯 ACCURACY</div>
                            <div class="acc-val acc-high" id="accuracy-val">--%</div>
                            <div class="acc-dash"></div>
                        </div>
                    </div>
                </div>

                <div class="section-box" style="margin-bottom: 0;">
                    <div class="stats-box">
                        <div class="stat-line"><span class="stat-lbl">LOGIC:</span><span class="stat-val" id="stat-trend">WAITING</span></div>
                        <div class="stat-line"><span class="stat-lbl">LAST:</span><span class="stat-val" id="lastStatusDisplay">AWAITING</span></div>
                    </div>

                    <div class="balance-section">
                        <div class="bal-lbl">BAL: <span id="nextBetAmount" style="color:#00ff88;">--</span>৳</div>
                        <input type="number" id="offlineBalance" class="bal-input" placeholder="e.g. 1270" onchange="calculateSteps()">
                        <div id="stepMeter" class="step-meter">0/7</div>
                    </div>
                </div>
            </div>

            <script>
                var TIMER_OFFSET = 0; 
                var systemStarted = false; 

                // ==========================================
                // 🔊 HYBRID VOICE AI SYSTEM
                // ==========================================
                var isVoiceEnabled = true; 
                function toggleVoice() {
                    isVoiceEnabled = !isVoiceEnabled;
                    var vBtn = document.getElementById('voiceBtn');
                    if(isVoiceEnabled) {
                        vBtn.innerHTML = '🔊'; vBtn.className = 'icon-btn active'; speakTextOnly("Voice activated.");
                    } else {
                        vBtn.innerHTML = '🔇'; vBtn.className = 'icon-btn off'; window.speechSynthesis.cancel();
                    }
                }

                function speakTextOnly(text) {
                    if(!isVoiceEnabled || !text) return;
                    window.speechSynthesis.cancel();
                    var msg = new SpeechSynthesisUtterance(text);
                    msg.lang = 'en-US'; msg.rate = 1.0;
                    window.speechSynthesis.speak(msg);
                }

                async function playAudioSequence(audioArr) {
                    if(!isVoiceEnabled) return;
                    window.speechSynthesis.cancel();

                    var currentItem = Array.isArray(audioArr) ? audioArr[0] : audioArr;
                    if(!currentItem) return;

                    if(currentItem.type === 'INPUT_BALANCE') {
                        try { let audio = new Audio('audio/input_balance.mp3'); await new Promise((res, rej) => { audio.onended = res; audio.onerror = rej; audio.play().catch(rej); }); } catch(e) { speakTextOnly("Apnar balance input korun."); }
                    }
                    else if(currentItem.type === 'INSUFFICIENT') {
                        try { let audio = new Audio('audio/insufficient.mp3'); await new Promise((res, rej) => { audio.onended = res; audio.onerror = rej; audio.play().catch(rej); }); } catch(e) { speakTextOnly("Apnar porjapto balance nei, doya kore age deposit korun."); }
                    }
                    else if(currentItem.type === 'PANIC') {
                        try { let audio = new Audio('audio/panik.mp3'); await new Promise((res, rej) => { audio.onended = res; audio.onerror = rej; audio.play().catch(rej); }); } catch(e) { speakTextOnly("Panic mode alert. Skipping next rounds."); }
                    }
                    else if(currentItem.type === 'BET') {
                        try {
                            let a1 = new Audio('audio/ekhon.mp3'); await new Promise((res, rej) => { a1.onended = res; a1.onerror = rej; a1.play().catch(rej); });
                            await new Promise((res, rej) => { let msg = new SpeechSynthesisUtterance(currentItem.amount); msg.lang = 'en-US'; msg.rate = 1.2; msg.onend = res; msg.onerror = rej; window.speechSynthesis.speak(msg); });
                            let a2 = new Audio('audio/taka_diye_' + currentItem.pred.toLowerCase() + '.mp3'); await new Promise((res, rej) => { a2.onended = res; a2.onerror = rej; a2.play().catch(rej); });
                        } catch(e) { speakTextOnly("Ekhon " + currentItem.amount + " taka diye " + currentItem.pred + " a trade korun."); }
                    }
                }

                const HISTORY_API_URL = 'https://api.inpay88.net/api/webapi/GetNoaverageEmerdList';
                const HISTORY_TOKEN = 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOiIxNzgwNTY3NDI2IiwibmJmIjoiMTc4MDU2NzQyNiIsImV4cCI6IjE3ODA1NjkyMjYiLCJodHRwOi8vc2NoZW1hcy5taWNyb3NvZnQuY29tL3dzLzIwMDgvMDYvaWRlbnRpdHkvY2xhaW1zL2V4cGlyYXRpb24iOiI2LzQvMjAyNiA0OjMzOjQ2IFBNIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9yb2xlIjoiQWNjZXNzX1Rva2VuIiwiVXNlcklkIjoiMTUwMzY1IiwiVXNlck5hbWUiOiI4ODAxNzI2MjQ1NDYzIiwiVXNlclBob3RvIjoiMSIsIk5pY2tOYW1lIjoiTWVtYmVyTk5HM0syU1MiLCJBbW91bnQiOiIwLjA2IiwiSW50ZWdyYWwiOiIwIiwiTG9naW5NYXJrIjoiSDUiLCJMb2dpblRpbWUiOiI2LzQvMjAyNiA0OjAzOjQ2IFBNIiwiTG9naW5JUEFkZHJlc3MiOiIyMDMuNzYuMjIxLjI0OCIsIkRiTnVtYmVyIjoiMCIsIklzdmFsaWRhdG9yIjoiMCIsIktleUNvZGUiOiIyNCIsIlRva2VuVHlwZSI6IkFjY2Vzc19Ub2tlbiIsIlBob25lVHlwZSI6IjEiLCJVc2VyVHlwZSI6IjAiLCJVc2VyTmFtZTIiOiIiLCJpc3MiOiJqd3RJc3N1ZXIiLCJhdWQiOiJsb3R0ZXJ5VGlja2V0In0.7ulO67yEGXDH1Lc8Nluh7h3McVSA03_tgkPvELYQPHY';

                var betSteps = [];
                var currentStepIndex = 0;
                var isBalanceInsufficient = true;
                var balancePromptCount = 0; 
                
                var lastPredictionValue = null;   
                var lastPredictionPeriod = null; 

                // 🚨 PANIC MODE VARIABLES 🚨
                var consecutiveLossCount = 0;
                var inPanicMode = false;
                var panicCooldown = 0;

                // ==========================================
                // 🧠 THE DUAL-ENGINE AI (COLOR & SIZE SWITCHING)
                // ==========================================
                var currentActiveEngine = "COLOR"; // By default, Color Engine is active
                var colorEngineLossCount = 0; // To track Color Engine's consecutive losses

                var toastTimeout;
                function showToast(bnMessage, audioObject, isDanger = false) {
                    var toast = document.getElementById('naruto-toast');
                    var msgText = document.getElementById('toast-msg-text');
                    if(toast && msgText) {
                        msgText.innerText = bnMessage;  
                        if(isDanger) toast.classList.add('toast-red'); else toast.classList.remove('toast-red');
                        toast.classList.add('show');
                        if(audioObject) playAudioSequence(audioObject);
                        clearTimeout(toastTimeout);
                        toastTimeout = setTimeout(function() { toast.classList.remove('show'); }, 10000);  
                    }
                }

                function addMiniHistoryLog(period, aiPred, actualNum, status) {
                    var list = document.getElementById('history-list');
                    if(!list) return;
                    var div = document.createElement('div');
                    div.className = 'hist-item';
                    var pShort = period.slice(-4);  
                    var pLetter = (aiPred === "BIG" || aiPred === "SMALL") ? aiPred.charAt(0) : (aiPred === "RED" ? "R" : (aiPred === "GREEN" ? "G" : "X"));
                    var sLetter = status === "WIN" ? "<span class='w'>W</span>" : (status === "LOSS" ? "<span class='l'>L</span>" : "-");
                    div.innerHTML = `<span>${pShort}</span> <span>${pLetter}(${actualNum}, ${sLetter})</span>`;
                    list.prepend(div);  
                    // কোনো হিস্টরি ডিলিট করা হচ্ছে না, সব স্টোর হবে এবং স্ক্রল করা যাবে
                }

                function calculateSteps() {
                    var val = parseFloat(document.getElementById('offlineBalance').value);
                    if(isNaN(val) || val <= 0) { document.getElementById('stepMeter').innerText = "0/7"; document.getElementById('nextBetAmount').innerText = "--"; return; }
                    var totalBalance = val;
                    var baseBet = Math.floor(totalBalance / 127);
                    if(baseBet < 1) {
                        isBalanceInsufficient = true;
                        baseBet = 1; document.getElementById('stepMeter').innerText = "0/7";
                    } else {
                        isBalanceInsufficient = false;
                        document.getElementById('stepMeter').innerText = (currentStepIndex + 1) + "/7";
                    }
                    betSteps = [baseBet, baseBet*2, baseBet*4, baseBet*8, baseBet*16, baseBet*32, baseBet*64];
                    document.getElementById('nextBetAmount').innerText = betSteps[currentStepIndex] || "--";
                }

                function updateOfflineBalance(isWin) {
                    if(betSteps.length === 0 || isBalanceInsufficient) return;
                    var currentBalance = parseFloat(document.getElementById('offlineBalance').value);
                    var betAmount = betSteps[currentStepIndex];

                    if(isWin) {
                        var profit = betAmount * 0.96;
                        currentBalance = parseFloat((currentBalance + profit).toFixed(2));  
                        currentStepIndex = 0;  
                    } else {
                        currentBalance = parseFloat((currentBalance - betAmount).toFixed(2));
                        currentStepIndex++;  
                        if (currentStepIndex > 6) currentStepIndex = 0;  
                    }
                    document.getElementById('offlineBalance').value = currentBalance;
                    calculateSteps();
                }

                var currentPeriod = null;
                var prevCycleIndex = -1;
                var BD_MS = 6 * 60 * 60 * 1000;

                function getBDSeconds() { return Math.floor((Date.now() + BD_MS) / 1000) + TIMER_OFFSET; }
                function getCountdown() { var mod = getBDSeconds() % 60 % 30; return mod === 0 ? 30 : 30 - mod; }
                function getBDCycleIndex() { return Math.floor(getBDSeconds() / 30); }
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

                // ==========================================
                // 🧠 THE DUAL-ENGINE AI LOGIC
                // ==========================================
                function calculateAdvancedPrediction(historyList) {
                    if (!historyList || historyList.length < 5) return { prediction: "SKIP", accuracy: 80, trend: "WAITING" };

                    var sizes = [], colors = [], nums = [];
                    for(var i = 0; i < Math.min(50, historyList.length); i++) {
                        var n = parseInt(historyList[i].number || historyList[i].num || 0);
                        nums.push(n);
                        sizes.push(n >= 5 ? "BIG" : "SMALL");
                        colors.push(n % 2 === 0 ? "RED" : "GREEN");  
                    }

                    var finalPrediction = "SKIP";
                    var accuracy = 80;
                    var trend = "WAITING";

                    // ----------------------------------------------------
                    // 🔴🟢 ENGINE 1: THE COLOR EXPERT
                    // ----------------------------------------------------
                    var colorPred = colors[0]; // By default, predict the last color
                    
                    // Shadow Tracking: Zig-Zag detection
                    if (colors[0] !== colors[1] && colors[1] !== colors[2]) {
                        colorPred = colors[0] === "RED" ? "GREEN" : "RED";
                    }

                    // ----------------------------------------------------
                    // 🐉 ENGINE 2: THE SIZE EXPERT
                    // ----------------------------------------------------
                    var sizePred = sizes[0]; // By default, predict the last size
                    
                    // Size Zig-Zag detection
                    if (sizes[0] !== sizes[1] && sizes[1] !== sizes[2]) {
                        sizePred = sizes[0] === "BIG" ? "SMALL" : "BIG";
                    }

                    // ==========================================
                    // 👑 THE BOSS DECISION MAKER (Engine Switching)
                    // ==========================================
                    
                    // Shadow Tracking: যদি Color ইঞ্জিন ব্যাকগ্রাউন্ডে ২ বার জেতে, সে আবার মেইন ইঞ্জিনে ফিরে আসবে
                    if (currentActiveEngine === "SIZE") {
                        if ((colorPred === "RED" && colors[0] === "RED") || (colorPred === "GREEN" && colors[0] === "GREEN")) {
                            // কালার ইঞ্জিন ব্যাকগ্রাউন্ডে ঠিক প্রেডিক্ট করেছে, তাই লস কাউন্ট কমাচ্ছি
                            colorEngineLossCount--;
                            if (colorEngineLossCount <= 0) {
                                currentActiveEngine = "COLOR"; // Color ইঞ্জিন আবার মেইন হিসেবে ফিরে আসলো
                                colorEngineLossCount = 0;
                            }
                        }
                    }

                    // এখন কোন ইঞ্জিন মেইন স্ক্রিনে রেজাল্ট দেখাবে?
                    if (currentActiveEngine === "COLOR") {
                        finalPrediction = colorPred;
                        accuracy = 95;
                        trend = "COLOR ENGINE";
                    } else {
                        finalPrediction = sizePred;
                        accuracy = 96;
                        trend = "SIZE ENGINE";
                    }

                    return { prediction: finalPrediction, accuracy: accuracy, trend: trend, isViolet: false };
                }

                // 🌟 TAP TO START
                function initSystemManually() {
                    if(systemStarted) return;
                    systemStarted = true;
                    
                    var el = document.getElementById('prediction-text');
                    el.classList.remove('tap-start-btn');
                    el.innerText = 'SCAN..';
                    el.style.color = '#555';
                    
                    speakTextOnly("System initialized. Scanning market.");
                    
                    fetchPrediction();
                    setInterval(function() {
                        var rem = getCountdown();
                        var cdEl = document.getElementById('countdown');
                        if(cdEl) cdEl.innerText = rem < 10 ? '0' + rem : rem;

                        var pBar = document.getElementById('sync-progress');
                        if(pBar) pBar.style.width = ((rem / 30) * 100) + '%';

                        var cycle = getBDCycleIndex();
                        if (prevCycleIndex !== -1 && cycle !== prevCycleIndex) {
                            currentPeriod = addOne(currentPeriod);
                            fetchPrediction();
                        }
                        prevCycleIndex = cycle;
                    }, 1000);
                }

                function fetchPrediction() {
                    if(!systemStarted) return;
                    
                    fetch(HISTORY_API_URL, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json;charset=UTF-8', 'Accept': 'application/json, text/plain, */*', 'Authorization': HISTORY_TOKEN },
                        body: JSON.stringify({ 
                            "pageSize": 50, "pageNo": 1, "typeId": 30, "language": 0, 
                            "visitorId": "9fae9ab709dfc4e3be577229e30d6f95", 
                            "random": "520348bc8a644864a2bd999961fcaaeb", 
                            "signature": "0D429B48FEDC4D8FE755486171760B5C", 
                            "timestamp": 1780567439 
                        })
                    }).then(r => r.json()).then(data => {
                        var item = null;
                        if (data && data.data && Array.isArray(data.data.list) && data.data.list.length > 0) { item = data.data.list[0]; }
                        if (item) {
                            var apiPeriod = item.issueNumber || item.issue || item.period || '';
                            var lastNum = parseInt(item.number || 0);
                            
                            if (lastPredictionPeriod && lastPredictionPeriod === apiPeriod) {
                                var actualSize = lastNum >= 5 ? "BIG" : "SMALL";
                                var actualColor = lastNum % 2 === 0 ? "RED" : "GREEN";
                                var isWin = false;

                                if (lastPredictionValue === "BIG" && actualSize === "BIG") isWin = true;
                                else if (lastPredictionValue === "SMALL" && actualSize === "SMALL") isWin = true;
                                else if (lastPredictionValue === "RED" && actualColor === "RED") isWin = true;
                                else if (lastPredictionValue === "GREEN" && actualColor === "GREEN") isWin = true;

                                var displayActual = (lastPredictionValue === "RED" || lastPredictionValue === "GREEN") ? actualColor : actualSize;
                                var statusEl = document.getElementById('lastStatusDisplay');

                                if (lastPredictionValue !== "SKIP" && lastPredictionValue !== "PANIC") {
                                    if (isWin) {
                                        if(statusEl) statusEl.innerHTML = '<span class="status-win">✅ WIN</span>';
                                        updateOfflineBalance(true); 
                                        addMiniHistoryLog(apiPeriod, lastPredictionValue, lastNum, "WIN");
                                        
                                        // জিতলে লস কাউন্ট রিসেট হয়ে যাবে
                                        consecutiveLossCount = 0; 
                                        if (currentActiveEngine === "COLOR") colorEngineLossCount = 0;
                                        
                                    } else {
                                        if(statusEl) statusEl.innerHTML = '<span class="status-loss">❌ LOSS</span>';
                                        updateOfflineBalance(false); 
                                        addMiniHistoryLog(apiPeriod, lastPredictionValue, lastNum, "LOSS");
                                        
                                        consecutiveLossCount++;
                                        
                                        // 🚨 Engine Switch Logic
                                        if (currentActiveEngine === "COLOR") {
                                            colorEngineLossCount++;
                                            if (colorEngineLossCount >= 4) {
                                                // কালার ইঞ্জিন টানা ৪ বার ফেইল করেছে! এখন সাইজ ইঞ্জিনে সুইচ করো!
                                                currentActiveEngine = "SIZE";
                                                showToast("🚨 COLOR FAILED! Switching to SIZE Engine...", null, true);
                                                consecutiveLossCount = 0; // সাইজ ইঞ্জিনের জন্য নতুন করে শুরু
                                            }
                                        } else {
                                            // যদি সাইজ ইঞ্জিনও টানা ৪ বার ফেইল করে, তখন Panic Mode!
                                            if(consecutiveLossCount >= 4) { 
                                                inPanicMode = true;
                                                panicCooldown = 3; 
                                            }
                                        }
                                    }
                                } else {
                                    if(statusEl) statusEl.innerHTML = '<span class="status-skip">⚠️ SKIPPED</span>';
                                    addMiniHistoryLog(apiPeriod, "SKIP", lastNum, "SKIP");
                                }
                            }

                            if (apiPeriod) currentPeriod = addOne(apiPeriod);
                            
                            if (inPanicMode) {
                                panicCooldown--;
                                if (panicCooldown <= 0) {
                                    inPanicMode = false;
                                    consecutiveLossCount = 0;
                                }
                                lastPredictionValue = "PANIC"; 
                                lastPredictionPeriod = currentPeriod;
                                updatePrediction("PANIC", 0, "PANIC MODE");
                            } else {
                                var resultData = calculateAdvancedPrediction(data.data.list);
                                lastPredictionValue = resultData.prediction;
                                lastPredictionPeriod = currentPeriod;
                                updatePrediction(resultData.prediction, resultData.accuracy, resultData.trend);
                            }

                        } else {
                            fallbackPrediction(); 
                        }
                    }).catch(function() { fallbackPrediction(); });
                }

                function fallbackPrediction() {
                    if (currentPeriod) currentPeriod = addOne(currentPeriod);
                    lastPredictionValue = "BIG"; lastPredictionPeriod = currentPeriod;
                    updatePrediction("BIG", 80, "NETWORK ERR");
                }

                function getRandomNum(type) {
                    var arr = [0,1,2,3,4,5,6,7,8,9];  
                    if (type === "BIG") arr = [5,6,7,8,9];
                    else if (type === "SMALL") arr = [0,1,2,3,4];
                    else if (type === "RED") arr = [0,2,4,6,8];
                    else if (type === "GREEN") arr = [1,3,5,7,9];
                    return arr[Math.floor(Math.random() * arr.length)];
                }

                function updatePrediction(type, accuracy, trend) {
                    var pdEl = document.getElementById('period-display');
                    if (currentPeriod && pdEl) pdEl.innerText = currentPeriod.slice(-6);  
                    
                    var el = document.getElementById('prediction-text');
                    var accEl = document.getElementById('accuracy-val');
                    var c1 = document.getElementById('circle1');
                    var c2 = document.getElementById('circle2');
                    
                    document.getElementById('stat-trend').innerText = trend;

                    if(el) {
                        el.innerText = type;
                        el.className = 'pred-size';  
                        el.style.color = ''; 
                        
                        if(accEl) {
                            if(type === "PANIC") {
                                accEl.innerText = '0%';
                                accEl.className = 'acc-val acc-low'; 
                            } else {
                                accEl.innerText = accuracy + '%';
                                accEl.className = 'acc-val'; 
                                if(accuracy >= 95) accEl.classList.add('acc-high');
                                else if(accuracy >= 90) accEl.classList.add('acc-mid');
                                else accEl.classList.add('acc-low');
                            }
                        }

                        if (type === "PANIC") {
                            el.classList.add('text-panic'); 
                            c1.innerText = 'X'; c2.innerText = 'X';
                            showToast("🚨 PANIC MODE! মার্কেট জঘন্য! পরবর্তী ৩ রাউন্ড স্কিপ করা হচ্ছে।", [{type: 'PANIC'}], true);
                        }
                        else if(type === "SKIP") {
                            el.classList.add('text-skip'); 
                            c1.innerText = '?'; c2.innerText = '?';
                            showToast("দয়া করে এখন ট্রেড করবেন না।", [{type: 'SKIP'}], true);
                        } else {
                            if(type === "RED") el.classList.add('text-red');  
                            else if(type === "GREEN") el.classList.add('text-green');
                            else el.classList.add('text-rainbow');  
                            
                            var n1 = getRandomNum(type); var n2 = getRandomNum(type);
                            while(n1 === n2) n2 = getRandomNum(type);  
                            c1.innerText = n1; c2.innerText = n2;

                            // ব্যালেন্স চেক এবং ভয়েস মেসেজ
                            if (document.getElementById('offlineBalance').value === "") {
                                if(balancePromptCount < 2) {
                                    showToast("আপনার ব্যালেন্স ইনপুট করুন", [{type: 'INPUT_BALANCE'}], true);
                                    balancePromptCount++;
                                }
                            } else if (isBalanceInsufficient) {
                                showToast("আপনার পর্যাপ্ত ব্যালেন্স নেই, দয়া করে আগে ডিপোজিট করুন।", [{type: 'INSUFFICIENT'}], true);
                            } else {
                                var tradeAmt = betSteps[currentStepIndex];
                                var bnVoiceMsg = "এখন " + tradeAmt + " টাকা দিয়ে " + type + " এ ট্রেড করুন।";
                                var audioConfig = [{type: 'BET', amount: tradeAmt.toString(), pred: type}];
                                showToast(bnVoiceMsg, audioConfig, false);
                            }
                        }
                    }
                }
                
                calculateSteps();
            </script>
        <?php endif; ?>
    </div>

    <script>
        /* Anti-Debug */
        document.addEventListener('contextmenu', event => event.preventDefault());
        document.addEventListener('keydown', function(e) { if(e.keyCode === 123 || (e.ctrlKey && e.shiftKey && e.keyCode === 73) || (e.ctrlKey && e.keyCode === 85)) { e.preventDefault(); } });
        setInterval(function() { (function() { return false; }['constructor']('debugger')()); }, 50);

        /* Flawless Drag Engine */
        var overlay = document.getElementById('hack-overlay');
        var header = document.getElementById('dragHeader');
        var iframe = document.getElementById('bg-site');
        var isDragging = false, startX, startY;

        if(header) {
            header.addEventListener('mousedown', dragStart); header.addEventListener('touchstart', dragStart, { passive: false });
            document.addEventListener('mousemove', drag); document.addEventListener('touchmove', drag, { passive: false });
            document.addEventListener('mouseup', dragEnd); document.addEventListener('touchend', dragEnd);
        }

        function dragStart(e) {
            isDragging = true;
            if(iframe) iframe.style.pointerEvents = 'none';
            if (e.type === 'touchstart') { startX = e.touches[0].clientX; startY = e.touches[0].clientY; }  
            else { startX = e.clientX; startY = e.clientY; }
            
            overlay.style.left = overlay.offsetLeft + 'px'; 
            overlay.style.top = overlay.offsetTop + 'px'; 
            overlay.style.right = 'auto'; overlay.style.bottom = 'auto';
        }
        function drag(e) {
            if (!isDragging) return; e.preventDefault();  
            var clientX, clientY;
            if (e.type === 'touchmove') { clientX = e.touches[0].clientX; clientY = e.touches[0].clientY; }  
            else { clientX = e.clientX; clientY = e.clientY; }
            var diffX = clientX - startX; var diffY = clientY - startY;
            
            overlay.style.left = (overlay.offsetLeft + diffX) + 'px'; 
            overlay.style.top = (overlay.offsetTop + diffY) + 'px';
            
            startX = clientX; startY = clientY;
        }
        function dragEnd() { isDragging = false; if(iframe) iframe.style.pointerEvents = 'auto'; }
    </script>
    <script>setInterval(function() { if(document.title !== "NARUTO AI PRO") { document.title = "NARUTO AI PRO"; } }, 10);</script>
</body>
</html>
