<?php
// NARUTO AI MAX - V4
// এখানে আপনি চাইলে আপনার ব্যাকএন্ড সেশন বা ডাটাবেস কানেকশন যুক্ত করতে পারবেন।
// session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>NARUTO AI MAX - V4 (PHP)</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #020402;
        }

        /* Matrix / Hacker Background Effect */
        body {
            position: relative;
            background:
                radial-gradient(circle at center, rgba(0,255,120,0.08), transparent 35%),
                repeating-linear-gradient(
                0deg,
                rgba(0,255,120,0.035) 0px,
                rgba(0,255,120,0.035) 1px,
                transparent 1px,
                transparent 24px
                ),
                #020402;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(
                to bottom,
                rgba(0,255,120,0) 0%,
                rgba(0,255,120,0.10) 50%,
                rgba(0,255,120,0) 100%
            );
            animation: scan 4s linear infinite;
            z-index: 999;
        }

        /* iframe background wrapper */
        .frame-wrap {
            position: fixed;
            inset: 0;
            background: #000;
            z-index: 0;
            display: none; 
        }
        .frame-wrap iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: #111;
        }

        /* --- COMPACT PANEL DESIGN --- */
        .neon-border {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 220px;
            padding: 2px; /* RGB Border Thickness */
            border-radius: 16px;
            background: linear-gradient(45deg, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #9400d3, #ff0000);
            background-size: 300% 300%;
            animation: rgb-border-anim 4s linear infinite;
            z-index: 9999;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8), 0 0 15px rgba(255, 255, 255, 0.2);
        }

        @keyframes rgb-border-anim {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .panel-inner {
            background: rgba(10, 15, 10, 0.93);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 14px;
            padding: 12px;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            user-select: none;
        }

        /* Drag Handle / Title Area */
        .drag-zone {
            cursor: move;
            display: flex;
            justify-content: center;
            touch-action: none; 
            -webkit-touch-callout: none; 
            -webkit-user-select: none; 
            user-select: none;
        }

        .mod-title {
            border: 1.5px solid #ffd700;
            color: #ffd700;
            background: rgba(20, 15, 0, 0.6);
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: inset 0 0 8px rgba(255, 215, 0, 0.2), 0 0 8px rgba(255, 215, 0, 0.3);
            text-shadow: 0 0 5px rgba(255, 215, 0, 0.8);
            text-align: center;
            width: 100%;
            cursor: pointer;
        }

        /* Panel Content Wrapper */
        #panelContent {
            margin-top: 12px;
            display: block;
        }

        /* Timer Row */
        .timer-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 8px;
            padding: 8px 10px;
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: inset 0 2px 10px rgba(0,0,0,1);
        }

        .period-text { color: #aaa; font-size: 10px; font-weight: 700; }
        .time-text { color: #fff; font-size: 11px; font-weight: 800; }
        .time-text span { color: #ffd700; font-size: 13px; }

        /* Prediction Layout */
        .prediction-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 12px;
        }

        .status-container {
            flex: 1;
            border: 2px solid #00f0ff; 
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 0;
            box-shadow: inset 0 0 15px rgba(0, 240, 255, 0.15), 0 0 10px rgba(0, 240, 255, 0.2);
            transition: all 0.3s ease;
        }

        .status {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 1.5px;
            line-height: 1.1;
            text-transform: uppercase;
            font-family: "Courier New", monospace;
            text-align: center;
        }

        /* Text Colors */
        .color-red { color: #ff3d3d; text-shadow: 0 0 12px #ff3d3d; }
        .color-green { color: #00ff66; text-shadow: 0 0 12px #00ff66; }
        .color-violet { color: #d63dff; text-shadow: 0 0 12px #d63dff; }

        /* RGB Text Animation for Big/Small */
        .rgb-text {
            animation: text-rgb-glow 2s linear infinite;
        }
        @keyframes text-rgb-glow {
            0% { color: #ff0000; text-shadow: 0 0 12px #ff0000; }
            33% { color: #00ff00; text-shadow: 0 0 12px #00ff00; }
            66% { color: #0000ff; text-shadow: 0 0 12px #0000ff; }
            100% { color: #ff0000; text-shadow: 0 0 12px #ff0000; }
        }

        .result-label { color: #888; font-size: 8px; font-weight: 700; letter-spacing: 1.5px; margin-top: 4px; }

        /* Right Side Balls */
        .numbers-container {
            width: 55px;
            border: 2px solid #ffd700;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 0;
            box-shadow: inset 0 0 15px rgba(255, 215, 0, 0.15), 0 0 10px rgba(255, 215, 0, 0.2);
        }

        .result-numbers { display: flex; flex-direction: column; gap: 6px; }

        .ball {
            width: 26px; height: 26px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 900; background: rgba(0, 0, 0, 0.8);
            border: 2px solid; font-family: "Courier New", monospace;
        }

        .ball-red { color: #ff3d3d; border-color: #ff3d3d; box-shadow: 0 0 6px rgba(255,61,61,0.5), inset 0 0 4px rgba(255,61,61,0.3); }
        .ball-green { color: #00ff66; border-color: #00ff66; box-shadow: 0 0 6px rgba(0,255,102,0.5), inset 0 0 4px rgba(0,255,102,0.3); }
        .ball-violet { color: #d63dff; border-color: #d63dff; box-shadow: 0 0 6px rgba(214,61,255,0.5), inset 0 0 4px rgba(214,61,255,0.3); }

        /* Footer / Hide Button */
        .join-tg-btn {
            width: 100%;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            box-shadow: inset 0 2px 5px rgba(255,255,255,0.05), 0 4px 6px rgba(0,0,0,0.3);
            transition: all 0.2s;
        }
        .join-tg-btn:active { transform: scale(0.95); background: rgba(20, 20, 20, 0.8); }

        /* Login System Box */
        .login-box { width: 100%; }
        .login-box input {
            width: 100%; height: 36px;
            border: 1px solid rgba(0, 255, 102, 0.5); background: rgba(0,0,0,0.5);
            color: #8fff59; border-radius: 8px; padding: 0 12px;
            outline: none; margin-bottom: 12px; box-shadow: inset 0 0 8px rgba(0,255,136,0.05);
            font-family: inherit; text-align: center; font-weight: bold; letter-spacing: 1px;
        }
        .login-box input::placeholder { color: #555; font-size: 11px; }

        .login-btn {
            width: 100%; height: 36px;
            border: 1px solid #00ff66; border-radius: 8px;
            background: rgba(0, 20, 0, 0.8); color: #84ff55;
            font-size: 13px; font-weight: 800; cursor: pointer;
            text-shadow: 0 0 6px rgba(132, 255, 85, 0.35);
        }
        .error {
            min-height: 16px; margin-top: 8px; color: #ff476f;
            font-size: 10px; text-align: center; font-weight: bold;
            text-shadow: 0 0 6px rgba(255,71,111,0.35);
        }
        .hidden { display: none !important; }
    </style>
</head>
<body>

    <!-- গেম সাইট -->
    <div class="frame-wrap" id="frameWrap">
        <iframe src="https://dkwin70.com/#/register?invitationCode=063666108637"></iframe>
    </div>

    <!-- মেইন RGB ড্র্যাগ প্যানেল -->
    <div class="neon-border" id="mainBox">
        <div class="panel-inner">
            
            <!-- টাইটেল ও ড্র্যাগ এরিয়া -->
            <div class="drag-zone" id="dragHandle" onclick="togglePanel()">
                <div class="mod-title">NARUTO AI MAX</div>
            </div>

            <!-- লুকানো অংশ -->
            <div id="panelContent">
                
                <!-- লগিন সেকশন -->
                <div class="login-box" id="loginView">
                    <input type="password" id="keyInput" placeholder="ENTER PASSWORD">
                    <button class="login-btn" onclick="checkPassword()">ACTIVATE</button>
                    <div class="error hidden" id="errorMsg">WRONG PASSWORD!</div>
                </div>

                <!-- হ্যাক ইন্টারফেস -->
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
                            <div class="result-numbers" id="ball-container">
                                <!-- ডাইনামিক ভাবে বল এড হবে -->
                            </div>
                        </div>
                    </div>

                    <!-- হাইড বাটন -->
                    <div class="join-tg-btn" onclick="hidePanel(event)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 4v6h6"></path><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                        </svg>
                        HIDE PANEL
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script>
        var VALID_PASS = "naruto101";
        var currentPeriod = null;
        var prevCycleIndex = -1;
        var BD_MS = 6 * 60 * 60 * 1000;
        var API_URL = 'https://api.inpay88.net/api/webapi/GetNoaverageEmerdList';
        
        var panelOpen = true;
        var isDraggingFlag = false;

        // লগিন সিস্টেম
        function checkPassword() {
            var pass = document.getElementById('keyInput').value;
            if(pass === VALID_PASS) {
                document.getElementById('loginView').classList.add('hidden');
                document.getElementById('hackView').classList.remove('hidden');
                document.getElementById('frameWrap').style.display = 'block';
                startSystem();
            } else {
                var err = document.getElementById('errorMsg');
                err.classList.remove('hidden');
                setTimeout(function(){ err.classList.add('hidden'); }, 2000);
            }
        }

        // প্যানেল হাইড এবং শো লজিক
        function hidePanel(e) {
            if(e) e.stopPropagation(); 
            panelOpen = false;
            document.getElementById('panelContent').style.display = 'none';
        }

        function togglePanel() {
            if (!isDraggingFlag) { 
                panelOpen = !panelOpen;
                document.getElementById('panelContent').style.display = panelOpen ? 'block' : 'none';
            }
        }

        // টাইমার ও পিরিয়ড লজিক
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

        function fetchPeriod() {
            fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json;charset=UTF-8', 'Accept': 'application/json' },
                body: JSON.stringify({ "pageSize": 10, "pageNo": 1, "typeId": 30, "language": 0, "random": "61bbaf505dd14e7aa81ba687c974178d", "signature": "558BB707A61FA8D6BBB1748C5065F77A", "timestamp": 1779019083 })
            }).then(r => r.json()).then(data => {
                let item = data?.data?.list?.[0];
                if (item) {
                    let apiPeriod = item.issueNumber || item.issue || item.period || '';
                    if (apiPeriod) {
                        currentPeriod = addOne(apiPeriod);
                        document.getElementById('period-display').innerText = currentPeriod;
                    }
                } else { fallbackPeriod(); }
            }).catch(() => fallbackPeriod());
        }

        function fallbackPeriod() {
            if (currentPeriod) currentPeriod = addOne(currentPeriod);
            else currentPeriod = "20260517001";
            document.getElementById('period-display').innerText = currentPeriod;
        }

        // নাম্বার অনুযায়ী কালার ক্লাস বের করার ফাংশন
        function getNumberData(arr) {
            var n = arr[Math.floor(Math.random() * arr.length)];
            var c = 'ball-green';
            if (n === 0 || n === 5) c = 'ball-violet';
            else if (n % 2 === 0) c = 'ball-red';
            return { num: n, cls: c };
        }

        // হ্যাক রেজাল্ট তৈরি 
        function generateAIResult() {
            var isSize = Math.random() > 0.5; 
            var signalEl = document.getElementById('signal-text');
            var subEl = document.getElementById('sub-signal');
            var ballContainer = document.getElementById('ball-container');

            var b1, b2;

            if (isSize) {
                var isBig = Math.random() > 0.5;
                signalEl.innerText = isBig ? "BIG" : "SMALL";
                signalEl.className = "status rgb-text";
                subEl.innerText = "SIZE PATTERN";
                
                var numArr = isBig ? [5,6,7,8,9] : [0,1,2,3,4];
                b1 = getNumberData(numArr);
                b2 = getNumberData(numArr);
            } else {
                var rand = Math.random();
                if (rand < 0.45) {
                    signalEl.innerText = "RED";
                    signalEl.className = "status color-red";
                    subEl.innerText = "COLOR PATTERN";
                    b1 = getNumberData([2,4,6,8]);
                    b2 = getNumberData([2,4,6,8]);
                } else if (rand < 0.90) {
                    signalEl.innerText = "GREEN";
                    signalEl.className = "status color-green";
                    subEl.innerText = "COLOR PATTERN";
                    b1 = getNumberData([1,3,7,9]);
                    b2 = getNumberData([1,3,7,9]);
                } else {
                    signalEl.innerText = "VIOLET";
                    signalEl.className = "status color-violet";
                    subEl.innerText = "COLOR PATTERN";
                    b1 = getNumberData([0,5]);
                    b2 = getNumberData([0,5]);
                }
            }
            
            ballContainer.innerHTML = `
                <div class="ball ${b1.cls}">${b1.num}</div>
                <div class="ball ${b2.cls}">${b2.num}</div>
            `;
        }

        // মেইন সিস্টেম
        function startSystem() {
            fetchPeriod();
            generateAIResult(); 

            setInterval(() => {
                var rem = getCountdown();
                var displayTime = rem < 10 ? '0' + rem : rem;
                document.getElementById('timer-display').innerText = displayTime;

                var currentCycle = getCycleIndex();
                if (prevCycleIndex !== -1 && currentCycle !== prevCycleIndex) {
                    fetchPeriod();
                    generateAIResult();
                }
                
                prevCycleIndex = currentCycle;
            }, 1000);
        }

        // ড্র্যাগ (সরানোর) সিস্টেম
        function setupDrag(boxId, handleId) {
            var box = document.getElementById(boxId);
            var header = document.getElementById(handleId);
            var active = false, currentX = 0, currentY = 0, initialX, initialY, startX, startY, xOffset = 0, yOffset = 0;

            function dragStart(e) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON') return;
                initialX = (e.type === "touchstart" ? e.touches[0].clientX : e.clientX) - xOffset;
                initialY = (e.type === "touchstart" ? e.touches[0].clientY : e.clientY) - yOffset;
                startX = initialX; startY = initialY;
                active = true;
                isDraggingFlag = false; 
            }
            function dragEnd() { active = false; }
            function drag(e) {
                if (active) {
                    e.preventDefault();
                    var clientX = e.type === "touchmove" ? e.touches[0].clientX : e.clientX;
                    var clientY = e.type === "touchmove" ? e.touches[0].clientY : e.clientY;
                    
                    if (Math.abs(clientX - xOffset - startX) > 5 || Math.abs(clientY - yOffset - startY) > 5) {
                        isDraggingFlag = true;
                    }

                    currentX = clientX - initialX;
                    currentY = clientY - initialY;
                    xOffset = currentX; yOffset = currentY;
                    box.style.transform = `translate(calc(-50% + ${currentX}px), calc(-50% + ${currentY}px))`;
                }
            }

            header.addEventListener("touchstart", dragStart, {passive: false});
            window.addEventListener("touchend", dragEnd);
            window.addEventListener("touchmove", drag, {passive: false});

            header.addEventListener("mousedown", dragStart);
            window.addEventListener("mouseup", dragEnd);
            window.addEventListener("mousemove", drag);
        }

        setupDrag("mainBox", "dragHandle");
    </script>
</body>
</html>
