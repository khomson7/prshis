<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Drug Monitor</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0a0e1a;
            --bg-card: #111827;
            --bg-card-hover: #1a2236;
            --border-card: #1e2a42;
            --text-primary: #e8ecf4;
            --text-secondary: #8896b0;
            --text-muted: #4a5873;
            --accent-blue: #3b82f6;
            --accent-green: #10b981;
            --accent-yellow: #f59e0b;
            --accent-red: #ef4444;
            --accent-red-glow: rgba(239, 68, 68, 0.25);
            --accent-green-bg: rgba(16, 185, 129, 0.1);
            --accent-yellow-bg: rgba(245, 158, 11, 0.1);
            --accent-red-bg: rgba(239, 68, 68, 0.1);
            --accent-blue-bg: rgba(59, 130, 246, 0.08);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Prompt', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ===== HEADER ===== */
        .monitor-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            border-bottom: 1px solid var(--border-card);
            padding: 16px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(12px);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--accent-blue), #6366f1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .header-title {
            font-size: 20px;
            font-weight: 600;
            letter-spacing: -0.3px;
        }

        .header-subtitle {
            font-size: 20px;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-clock {
            font-family: 'JetBrains Mono', monospace;
            font-size: 28px;
            font-weight: 500;
            color: var(--accent-blue);
            letter-spacing: 1px;
        }

        .header-date {
            font-size: 13px;
            color: var(--text-secondary);
            text-align: right;
        }

        .refresh-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: var(--text-muted);
            background: rgba(255,255,255,0.04);
            padding: 6px 12px;
            border-radius: 20px;
        }

        .refresh-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--accent-green);
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.7); }
        }

        /* ===== CONTROLS ===== */
        .controls-bar {
            padding: 14px 28px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid var(--border-card);
        }

        .control-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .control-label {
            font-size: 12px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .control-input {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            color: var(--text-primary);
            padding: 7px 14px;
            border-radius: 8px;
            font-family: 'Prompt', sans-serif;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
        }

        .control-input:focus {
            border-color: var(--accent-blue);
        }

        .btn-load {
            background: var(--accent-blue);
            color: #fff;
            border: none;
            padding: 7px 20px;
            border-radius: 8px;
            font-family: 'Prompt', sans-serif;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-load:hover { background: #2563eb; transform: translateY(-1px); }

        .control-separator {
            width: 1px;
            height: 28px;
            background: var(--border-card);
            margin: 0 8px;
        }

        .filter-tabs {
            display: flex;
            gap: 4px;
        }

        .filter-tab {
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid transparent;
            background: transparent;
            color: var(--text-secondary);
            font-family: 'Prompt', sans-serif;
            transition: all 0.2s;
        }

        .filter-tab:hover { background: rgba(255,255,255,0.05); }
        .filter-tab.active { background: var(--accent-blue-bg); color: var(--accent-blue); border-color: rgba(59,130,246,0.3); }
        .filter-tab.tab-overdue.active { background: var(--accent-red-bg); color: var(--accent-red); border-color: rgba(239,68,68,0.3); }

        /* ===== SUMMARY STRIP ===== */
        .summary-strip {
            display: flex;
            gap: 16px;
            padding: 14px 28px;
            border-bottom: 1px solid var(--border-card);
        }

        .summary-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            border-radius: 10px;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            min-width: 140px;
        }

        .summary-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .summary-icon.beds { background: var(--accent-blue-bg); }
        .summary-icon.done { background: var(--accent-green-bg); }
        .summary-icon.pending { background: var(--accent-yellow-bg); }
        .summary-icon.overdue { background: var(--accent-red-bg); }

        .summary-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: 22px;
            font-weight: 600;
        }

        .summary-number.c-green { color: var(--accent-green); }
        .summary-number.c-yellow { color: var(--accent-yellow); }
        .summary-number.c-red { color: var(--accent-red); }
        .summary-number.c-blue { color: var(--accent-blue); }

        .summary-label {
            font-size: 11px;
            color: var(--text-secondary);
        }

        /* ===== BED GRID ===== */
        .bed-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 16px;
            padding: 20px 28px 40px;
        }

        .bed-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 14px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .bed-card:hover {
            border-color: #2a3a5c;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }

        .bed-card.has-overdue {
            border-color: rgba(239, 68, 68, 0.4);
            box-shadow: 0 0 20px var(--accent-red-glow);
        }

        .bed-card.has-overdue .bed-header {
            background: linear-gradient(135deg, rgba(239,68,68,0.12), rgba(239,68,68,0.04));
        }

        /* Bed Header */
        .bed-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid var(--border-card);
        }

        .bed-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bed-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: 18px;
            font-weight: 700;
            color: var(--accent-blue);
            background: var(--accent-blue-bg);
            padding: 4px 12px;
            border-radius: 8px;
            min-width: 50px;
            text-align: center;
        }

        .patient-info .patient-name {
            font-size: 13px;
            font-weight: 500;
        }

        .patient-info .patient-hn {
            font-size: 11px;
            color: var(--text-muted);
            font-family: 'JetBrains Mono', monospace;
        }

        .bed-stats {
            display: flex;
            gap: 8px;
        }

        .stat-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
        }

        .stat-badge.done { background: var(--accent-green-bg); color: var(--accent-green); }
        .stat-badge.pending { background: var(--accent-yellow-bg); color: var(--accent-yellow); }
        .stat-badge.overdue { background: var(--accent-red-bg); color: var(--accent-red); }

        /* Drug Items */
        .drug-list {
            padding: 8px 12px 12px;
            max-height: 320px;
            overflow-y: auto;
        }

        .drug-list::-webkit-scrollbar { width: 4px; }
        .drug-list::-webkit-scrollbar-track { background: transparent; }
        .drug-list::-webkit-scrollbar-thumb { background: var(--border-card); border-radius: 4px; }

        .drug-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: background 0.15s;
        }

        .drug-item:hover { background: rgba(255,255,255,0.03); }

        .drug-item.status-done { opacity: 0.55; }

        .drug-item.status-overdue {
            background: var(--accent-red-bg);
            animation: overdue-blink 2.5s ease-in-out infinite;
        }

        @keyframes overdue-blink {
            0%, 100% { background: var(--accent-red-bg); }
            50% { background: rgba(239, 68, 68, 0.2); }
        }

        .drug-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .drug-status-dot.done { background: var(--accent-green); }
        .drug-status-dot.pending { background: var(--accent-yellow); }
        .drug-status-dot.overdue { background: var(--accent-red); animation: pulse-dot 1.5s ease-in-out infinite; }

        .drug-time {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 500;
            min-width: 44px;
            color: var(--text-primary);
        }

        .drug-name-wrap {
            flex: 1;
            min-width: 0;
        }

        .drug-name {
            font-size: 12px;
            font-weight: 400;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .drug-detail {
            font-size: 10px;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .drug-action-info {
            text-align: right;
            flex-shrink: 0;
        }

        .drug-action-time {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: var(--accent-green);
        }

        .drug-action-person {
            font-size: 10px;
            color: var(--text-muted);
            max-width: 100px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .no-items {
            text-align: center;
            padding: 24px;
            color: var(--text-muted);
            font-size: 13px;
        }

        /* ===== Progress Bar ===== */
        .progress-bar-wrap {
            padding: 0 16px 10px;
        }

        .progress-bar {
            height: 4px;
            background: rgba(255,255,255,0.06);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, var(--accent-green), #34d399);
            transition: width 0.6s ease;
        }

        /* ===== LOADING & EMPTY ===== */
        .loading-screen {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 80px 0;
            gap: 16px;
        }

        .spinner {
            width: 36px;
            height: 36px;
            border: 3px solid var(--border-card);
            border-top-color: var(--accent-blue);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .loading-text { font-size: 13px; color: var(--text-muted); }

        /* ===== HIDDEN ===== */
        .hidden { display: none !important; }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="monitor-header">
        <div class="header-left">
            <div class="header-icon">💊</div>
            <div>
                <div class="header-title">Nurse Drug Administration Monitor</div>
                <div class="header-subtitle" id="wardLabel">หอผู้ป่วย: -</div>
            </div>
        </div>
        <div class="header-right">
            <div class="refresh-indicator">
                <div class="refresh-dot"></div>
                <span>Auto refresh <span id="refreshInterval">3</span> นาที</span>
            </div>
            <div>
                <div class="header-clock" id="clock">--:--:--</div>
                <div class="header-date" id="dateLabel">-</div>
            </div>
        </div>
    </div>

    <!-- CONTROLS -->
    <div class="controls-bar">
        <div class="control-group">
            <span class="control-label">Ward:</span>
            <input type="text" class="control-input" id="inputWard" placeholder="รหัส Ward" style="width:100px;">
        </div>
        <div class="control-group">
            <span class="control-label">วันที่:</span>
            <input type="date" class="control-input" id="inputDate">
        </div>
        <div class="control-group">
            <span class="control-label">Refresh (นาที):</span>
            <select class="control-input" id="inputRefresh">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3" selected>3</option>
                <option value="5">5</option>
            </select>
        </div>
        <button class="btn-load" onclick="loadData()">โหลดข้อมูล</button>

        <div class="control-separator"></div>

        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all" onclick="setFilter('all', this)">ทั้งหมด</button>
            <button class="filter-tab tab-overdue" data-filter="overdue" onclick="setFilter('overdue', this)">เลยเวลา</button>
            <button class="filter-tab" data-filter="pending" onclick="setFilter('pending', this)">รอดำเนินการ</button>
            <button class="filter-tab" data-filter="done" onclick="setFilter('done', this)">เสร็จแล้ว</button>
        </div>
    </div>

    <!-- SUMMARY -->
    <div class="summary-strip" id="summaryStrip">
        <div class="summary-card">
            <div class="summary-icon beds">🛏️</div>
            <div>
                <div class="summary-number c-blue" id="sumBeds">0</div>
                <div class="summary-label">เตียง</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon done">✅</div>
            <div>
                <div class="summary-number c-green" id="sumDone">0</div>
                <div class="summary-label">ให้ยาแล้ว</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon pending">⏳</div>
            <div>
                <div class="summary-number c-yellow" id="sumPending">0</div>
                <div class="summary-label">รอดำเนินการ</div>
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-icon overdue">🚨</div>
            <div>
                <div class="summary-number c-red" id="sumOverdue">0</div>
                <div class="summary-label">เลยเวลา</div>
            </div>
        </div>
    </div>

    <!-- BED GRID -->
    <div class="bed-grid" id="bedGrid">
        <div class="loading-screen" id="loadingScreen">
            <div class="spinner"></div>
            <div class="loading-text">กรุณาระบุ Ward แล้วกดโหลดข้อมูล</div>
        </div>
    </div>

<script>
    // ===== CONFIG =====
    const API_URL = './nurse-monitor-api.php';
    let refreshTimer = null;
    let currentFilter = 'all';
    let cachedData = null;

    // ===== INIT =====
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('inputDate').value = new Date().toISOString().split('T')[0];
        updateClock();
        setInterval(updateClock, 1000);

        // URL params
        const params = new URLSearchParams(window.location.search);
        if (params.get('ward')) {
            document.getElementById('inputWard').value = params.get('ward');
            loadData();
        }
    });

    // ===== CLOCK =====
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').textContent =
            now.toLocaleTimeString('th-TH', { hour12: false });

        const thaiMonths = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.',
                            'ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        const d = now.getDate();
        const m = thaiMonths[now.getMonth()];
        const y = now.getFullYear() + 543;
        document.getElementById('dateLabel').textContent = `${d} ${m} ${y}`;
    }

    // ===== LOAD DATA =====
    async function loadData() {
        const ward = document.getElementById('inputWard').value.trim();
        const date = document.getElementById('inputDate').value;
        const refreshMin = parseInt(document.getElementById('inputRefresh').value);

        if (!ward) { alert('กรุณาระบุรหัส Ward'); return; }

        document.getElementById('refreshInterval').textContent = refreshMin;

        // Show loading
        document.getElementById('bedGrid').innerHTML =
            '<div class="loading-screen"><div class="spinner"></div><div class="loading-text">กำลังโหลดข้อมูล...</div></div>';

        try {
            const res = await fetch(`${API_URL}?ward=${encodeURIComponent(ward)}&date=${date}`);
            const data = await res.json();

            if (data.error) {
                document.getElementById('bedGrid').innerHTML =
                    `<div class="loading-screen"><div class="loading-text" style="color:var(--accent-red);">❌ ${data.error}</div></div>`;
                return;
            }

            cachedData = data;
            document.getElementById('wardLabel').textContent =
                `หอผู้ป่วย: ${data.ward_name} (${data.ward}) — วันที่: ${data.date}`;

            renderData(data);

        } catch (err) {
            document.getElementById('bedGrid').innerHTML =
                `<div class="loading-screen"><div class="loading-text" style="color:var(--accent-red);">❌ เชื่อมต่อ API ไม่ได้</div></div>`;
        }

        // Setup auto refresh
        clearInterval(refreshTimer);
        refreshTimer = setInterval(loadData, refreshMin * 60 * 1000);
    }

    // ===== RENDER =====
    function renderData(data) {
        const beds = data.beds || [];

        // Summary
        let totalDone = 0, totalPending = 0, totalOverdue = 0;
        beds.forEach(b => {
            totalDone += b.summary.done;
            totalPending += b.summary.pending;
            totalOverdue += b.summary.overdue;
        });
        document.getElementById('sumBeds').textContent = beds.length;
        document.getElementById('sumDone').textContent = totalDone;
        document.getElementById('sumPending').textContent = totalPending;
        document.getElementById('sumOverdue').textContent = totalOverdue;

        // Filter beds
        let filteredBeds = beds;
        if (currentFilter === 'overdue') {
            filteredBeds = beds.filter(b => b.summary.overdue > 0);
        } else if (currentFilter === 'pending') {
            filteredBeds = beds.filter(b => b.summary.pending > 0 || b.summary.overdue > 0);
        } else if (currentFilter === 'done') {
            filteredBeds = beds.filter(b => b.summary.done > 0);
        }

        if (filteredBeds.length === 0) {
            document.getElementById('bedGrid').innerHTML =
                '<div class="loading-screen"><div class="loading-text">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</div></div>';
            return;
        }

        // Sort: overdue first
        filteredBeds.sort((a, b) => b.summary.overdue - a.summary.overdue);

        let html = '';
        filteredBeds.forEach(bed => {
            const hasOverdue = bed.summary.overdue > 0;
            const total = bed.summary.total || 1;
            const donePercent = Math.round((bed.summary.done / total) * 100);

            // Filter items
            let items = bed.items;
            if (currentFilter === 'overdue') items = items.filter(i => i.status === 'overdue');
            else if (currentFilter === 'pending') items = items.filter(i => i.status !== 'done');
            else if (currentFilter === 'done') items = items.filter(i => i.status === 'done');

            html += `
            <div class="bed-card ${hasOverdue ? 'has-overdue' : ''}">
                <div class="bed-header">
                    <div class="bed-info">
                        <div class="bed-number">${escHtml(bed.bedname)}</div>
                        <div class="patient-info">
                            <div class="patient-name">${escHtml(bed.patient_name)}</div>
                            <div class="patient-hn">HN: ${escHtml(bed.hn)} &nbsp; AN: ${escHtml(bed.an)}</div>
                        </div>
                    </div>
                    <div class="bed-stats">
                        ${bed.summary.done > 0 ? `<span class="stat-badge done">✓ ${bed.summary.done}</span>` : ''}
                        ${bed.summary.pending > 0 ? `<span class="stat-badge pending">⏳ ${bed.summary.pending}</span>` : ''}
                        ${bed.summary.overdue > 0 ? `<span class="stat-badge overdue">! ${bed.summary.overdue}</span>` : ''}
                    </div>
                </div>

                <div class="progress-bar-wrap">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:${donePercent}%"></div>
                    </div>
                </div>

                <div class="drug-list">
                    ${items.length === 0 ? '<div class="no-items">ไม่มีรายการ</div>' :
                      items.map(item => renderDrugItem(item)).join('')}
                </div>
            </div>`;
        });

        document.getElementById('bedGrid').innerHTML = html;
    }

    function renderDrugItem(item) {
        return `
        <div class="drug-item status-${item.status}">
            <div class="drug-status-dot ${item.status}"></div>
            <div class="drug-time">${escHtml(item.plan_time)}</div>
            <div class="drug-name-wrap">
                <div class="drug-name">${escHtml(item.drug_name)}</div>
                ${item.drug_detail ? `<div class="drug-detail">${escHtml(item.drug_detail)}</div>` : ''}
            </div>
            <div class="drug-action-info">
                ${item.status === 'done' ?
                    `<div class="drug-action-time">✓ ${escHtml(item.action_time)}</div>
                     <div class="drug-action-person">${escHtml(item.action_person)}</div>` :
                  item.status === 'overdue' ?
                    `<div style="font-size:11px;color:var(--accent-red);font-weight:600;">เลยเวลา</div>` :
                    `<div style="font-size:11px;color:var(--accent-yellow);">รอ</div>`
                }
            </div>
        </div>`;
    }

    // ===== FILTER =====
    function setFilter(filter, el) {
        currentFilter = filter;
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
        if (cachedData) renderData(cachedData);
    }

    // ===== UTILS =====
    function escHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
</script>

</body>
</html>
