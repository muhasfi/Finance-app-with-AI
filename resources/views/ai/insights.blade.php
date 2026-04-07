@extends('layouts.app')
@section('title', 'AI Insight — Analisis Keuangan')

@push('styles')
<style>
    /* ── Insight Grid ── */
    .insight-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1.25rem;
    }

    /* ── Shared card base ── */
    .ai-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,.07);
        box-shadow: 0 2px 12px rgba(0,0,0,.05);
        overflow: hidden;
    }
    .ai-card-header {
        padding: 16px 20px 14px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .ai-card-title {
        font-size: .9rem;
        font-weight: 600;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ai-badge {
        font-size: .65rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 99px;
        background: #eef0ff;
        color: #6366f1;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .ai-card-body { padding: 20px; }

    /* ── Month selector ── */
    .month-selector {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f8f9fc;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 6px 12px;
        font-size: .82rem;
        color: #374151;
    }
    .month-selector select {
        border: none;
        background: transparent;
        font-size: .82rem;
        color: #374151;
        cursor: pointer;
        outline: none;
    }

    /* ── Summary card ── */
    .summary-state {
        min-height: 140px;
        display: flex;
        flex-direction: column;
    }

    /* Empty state */
    .empty-state {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 28px 20px;
        text-align: center;
        color: #9ca3af;
        gap: 8px;
    }
    .empty-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: linear-gradient(135deg, #eef0ff, #f5f3ff);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-bottom: 2px;
    }
    .empty-state h6 { color: #374151; font-size: .875rem; margin: 0; }
    .empty-state p  { font-size: .8rem; margin: 0; line-height: 1.5; }

    /* Loading state */
    .loading-state {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 30px 20px;
        gap: 14px;
    }
    .ai-spinner-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        animation: glow-pulse 1.5s ease-in-out infinite alternate;
    }
    @keyframes glow-pulse {
        from { box-shadow: 0 0 8px rgba(99,102,241,.3); transform: scale(.95); }
        to   { box-shadow: 0 0 20px rgba(99,102,241,.5); transform: scale(1.05); }
    }
    .loading-state p { font-size: .8rem; color: #6b7280; margin: 0; text-align: center; }

    /* Summary text */
    .summary-text {
        font-size: .875rem;
        color: #374151;
        line-height: 1.75;
    }
    .summary-footer {
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid #f3f4f6;
        font-size: .72rem;
        color: #9ca3af;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* ── Tips ── */
    .tips-list { display: flex; flex-direction: column; gap: 12px; }
    .tip-item {
        display: flex;
        gap: 12px;
        padding: 13px 15px;
        background: #f8f9fc;
        border: 1px solid #e9eaf0;
        border-radius: 12px;
        transition: border-color .15s;
        animation: fade-up .3s ease;
    }
    .tip-item:hover { border-color: #c7d2fe; }
    @keyframes fade-up {
        from { opacity:0; transform:translateY(6px); }
        to   { opacity:1; transform:translateY(0); }
    }
    .tip-number {
        width: 26px; height: 26px;
        border-radius: 8px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        font-size: .75rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; margin-top: 1px;
    }
    .tip-cat  { font-size: .7rem; font-weight: 600; color: #6366f1; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; }
    .tip-text { font-size: .83rem; color: #374151; line-height: 1.55; }
    .tip-saving {
        margin-top: 5px; font-size: .72rem; color: #10b981; font-weight: 600;
        display: flex; align-items: center; gap: 4px;
    }

    /* ── Anomalies ── */
    .anomaly-list { display: flex; flex-direction: column; gap: 10px; }
    .anomaly-item {
        display: flex; gap: 10px; padding: 12px 14px;
        border-radius: 11px; border: 1px solid; font-size: .825rem;
    }
    .anomaly-item.sev-high   { background:#fff5f5; border-color:#fecaca; }
    .anomaly-item.sev-medium { background:#fffbeb; border-color:#fde68a; }
    .anomaly-item.sev-low    { background:#f0fdf4; border-color:#bbf7d0; }

    .sev-icon {
        width:24px; height:24px; border-radius:7px;
        display:flex; align-items:center; justify-content:center;
        font-size:12px; flex-shrink:0; margin-top:1px;
    }
    .sev-high .sev-icon   { background:#fef2f2; color:#dc2626; }
    .sev-medium .sev-icon { background:#fffbeb; color:#d97706; }
    .sev-low .sev-icon    { background:#f0fdf4; color:#16a34a; }

    .anomaly-cat { font-weight:600; font-size:.78rem; margin-bottom:2px; }
    .sev-high .anomaly-cat   { color:#dc2626; }
    .sev-medium .anomaly-cat { color:#d97706; }
    .sev-low .anomaly-cat    { color:#16a34a; }
    .anomaly-desc { color:#4b5563; font-size:.8rem; line-height:1.5; }

    .no-anomaly { text-align:center; padding:20px; color:#6b7280; font-size:.82rem; }
    .no-anomaly i { font-size:1.8rem; color:#10b981; display:block; margin-bottom:8px; }

    /* ── How it works sidebar ── */
    .sidebar-right { display:flex; flex-direction:column; gap:1.25rem; }

    .flow-step { display:flex; gap:10px; align-items:flex-start; padding:8px 0; }
    .flow-step + .flow-step { border-top:1px dashed #f3f4f6; }
    .flow-dot {
        width:28px; height:28px; border-radius:8px;
        display:flex; align-items:center; justify-content:center;
        font-size:13px; flex-shrink:0;
    }
    .fd-blue   { background:#dbeafe; }
    .fd-amber  { background:#fef3c7; }
    .fd-purple { background:#ede9fe; }
    .fd-green  { background:#dcfce7; }
    .flow-title { font-size:.8rem; font-weight:600; color:#1f2937; }
    .flow-desc  { font-size:.72rem; color:#6b7280; margin-top:1px; }

    /* Skeleton */
    .skeleton {
        background: linear-gradient(90deg,#f3f4f6 25%,#e9eaf0 50%,#f3f4f6 75%);
        background-size: 200% 100%;
        animation: sk 1.5s infinite;
        border-radius: 6px;
    }
    @keyframes sk { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

    @media (max-width: 992px) {
        .insight-grid { grid-template-columns: 1fr; }
        .sidebar-right { display:grid; grid-template-columns:1fr 1fr; }
    }
    @media (max-width: 576px) { .sidebar-right { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="page-heading">

    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-7">
                <h3 class="d-flex align-items-center gap-2">
                    <span style="background:linear-gradient(135deg,#6366f1,#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">AI Insight</span>
                    <span class="badge rounded-pill" style="background:#eef0ff;color:#6366f1;font-size:.7rem;font-weight:600;-webkit-text-fill-color:#6366f1;">Gemini</span>
                </h3>
                <p class="text-subtitle text-muted">Analisis keuangan otomatis berbasis AI untuk bulan ini</p>
            </div>
            <div class="col-12 col-md-5 d-flex justify-content-md-end align-items-center gap-2 mt-2 mt-md-0">
                <div class="month-selector">
                    <i class="bi bi-calendar3 text-muted" style="font-size:.8rem"></i>
                    <select id="selMonth">
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                    <select id="selYear">
                        @foreach(range(now()->year, now()->year - 2) as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary btn-sm px-3" id="btnGenerate">
                    <i class="bi bi-stars me-1"></i>
                    <span id="btnGenerateText">{{ $insight ? 'Refresh Insight' : 'Generate Insight' }}</span>
                </button>
            </div>
        </div>
    </div>

    <div class="insight-grid">

        {{-- ── LEFT COLUMN ── --}}
        <div style="display:flex;flex-direction:column;gap:1.25rem">

            {{-- 1. Ringkasan --}}
            <div class="ai-card">
                <div class="ai-card-header">
                    <div class="ai-card-title">
                        <i class="bi bi-file-text text-primary" style="font-size:1rem"></i>
                        Ringkasan Keuangan
                        <span class="ai-badge">AI</span>
                    </div>
                    <span class="text-muted" style="font-size:.75rem">
                        {{ \Carbon\Carbon::createFromDate($year, $month)->translatedFormat('F Y') }}
                    </span>
                </div>
                <div class="ai-card-body summary-state" id="summarySection">
                    @if($insight && isset($insight['summary']))
                        <div class="summary-text">{{ $insight['summary'] }}</div>
                        <div class="summary-footer">
                            <i class="bi bi-clock"></i>
                            Diperbarui {{ isset($insight['generated_at'])
                                ? \Carbon\Carbon::parse($insight['generated_at'])->diffForHumans()
                                : 'baru saja' }}
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">✦</div>
                            <h6>Belum ada ringkasan</h6>
                            <p>Klik <strong>Generate Insight</strong> untuk membuat<br>analisis keuangan bulan ini dengan AI.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. Saran Hemat --}}
            <div class="ai-card">
                <div class="ai-card-header">
                    <div class="ai-card-title">
                        <i class="bi bi-piggy-bank text-success" style="font-size:1rem"></i>
                        Saran Hemat
                        <span class="ai-badge">AI</span>
                    </div>
                </div>
                <div class="ai-card-body" id="tipsSection">
                    @if($insight && isset($insight['tips']) && count($insight['tips']) > 0)
                        <div class="tips-list">
                            @foreach($insight['tips'] as $i => $tip)
                                <div class="tip-item">
                                    <div class="tip-number">{{ $i + 1 }}</div>
                                    <div>
                                        @if(!empty($tip['category']))<div class="tip-cat">{{ $tip['category'] }}</div>@endif
                                        <div class="tip-text">{{ $tip['tip'] }}</div>
                                        @if(!empty($tip['potential_saving']))
                                            <div class="tip-saving">
                                                <i class="bi bi-arrow-down-circle-fill"></i>
                                                Potensi hemat: {{ $tip['potential_saving'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state" style="padding:24px 16px">
                            <div class="empty-icon" style="font-size:20px">💡</div>
                            <h6>Saran belum tersedia</h6>
                            <p>Generate insight untuk mendapat saran hemat yang personal.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. Anomali --}}
            <div class="ai-card">
                <div class="ai-card-header">
                    <div class="ai-card-title">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size:1rem"></i>
                        Pengeluaran Tidak Biasa
                        <span class="ai-badge">AI</span>
                    </div>
                </div>
                <div class="ai-card-body" id="anomalySection">
                    @if($insight && isset($insight['anomalies']))
                        @if(count($insight['anomalies']) > 0)
                            <div class="anomaly-list">
                                @php $icons = ['high'=>'🔴','medium'=>'🟡','low'=>'🟢']; @endphp
                                @foreach($insight['anomalies'] as $a)
                                    <div class="anomaly-item sev-{{ $a['severity'] ?? 'low' }}">
                                        <div class="sev-icon">{{ $icons[$a['severity'] ?? 'low'] ?? '🔵' }}</div>
                                        <div>
                                            <div class="anomaly-cat">{{ $a['category'] ?? 'Tidak dikategorikan' }}</div>
                                            <div class="anomaly-desc">{{ $a['description'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="no-anomaly">
                                <i class="bi bi-check-circle-fill"></i>
                                Tidak ada pengeluaran tidak biasa terdeteksi. Bagus!
                            </div>
                        @endif
                    @else
                        <div class="empty-state" style="padding:24px 16px">
                            <div class="empty-icon" style="font-size:20px">🔍</div>
                            <h6>Belum dianalisis</h6>
                            <p>AI akan mendeteksi pengeluaran tidak biasa dibanding bulan sebelumnya.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── RIGHT COLUMN ── --}}
        <div class="sidebar-right">

            <div class="ai-card">
                <div class="ai-card-header">
                    <div class="ai-card-title">
                        <i class="bi bi-cpu text-primary" style="font-size:.95rem"></i>
                        Cara Kerja
                    </div>
                </div>
                <div class="ai-card-body" style="padding:14px 18px">
                    <div class="flow-step">
                        <div class="flow-dot fd-blue">📊</div>
                        <div>
                            <div class="flow-title">Kumpulkan Data</div>
                            <div class="flow-desc">Transaksi & kategori bulan ini diambil</div>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="flow-dot fd-amber">⚡</div>
                        <div>
                            <div class="flow-title">Queue Async</div>
                            <div class="flow-desc">Job dikirim ke background worker</div>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="flow-dot fd-purple">✦</div>
                        <div>
                            <div class="flow-title">Gemini AI</div>
                            <div class="flow-desc">Analisis mendalam dengan AI</div>
                        </div>
                    </div>
                    <div class="flow-step">
                        <div class="flow-dot fd-green">✓</div>
                        <div>
                            <div class="flow-title">Tersimpan di Cache</div>
                            <div class="flow-desc">Hasil disimpan, halaman update otomatis</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ai-card">
                <div class="ai-card-header">
                    <div class="ai-card-title">
                        <i class="bi bi-info-circle text-primary" style="font-size:.95rem"></i>
                        Info
                    </div>
                </div>
                <div class="ai-card-body" style="padding:14px 16px;font-size:.8rem;color:#4b5563;line-height:1.7">
                    <p class="mb-2">
                        <i class="bi bi-clock text-muted me-1"></i>
                        Insight di-<em>cache</em> beberapa jam. Gunakan <em>Refresh</em> untuk memperbarui.
                    </p>
                    <p class="mb-2">
                        <i class="bi bi-shield-check text-muted me-1"></i>
                        Data Anda hanya digunakan untuk analisis dan tidak disimpan AI.
                    </p>
                    <p class="mb-0">
                        <i class="bi bi-stars text-muted me-1"></i>
                        Saran bersifat indikatif — keputusan tetap di tangan Anda.
                    </p>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const btnGenerate     = document.getElementById('btnGenerate');
    const btnGenerateText = document.getElementById('btnGenerateText');
    const summarySection  = document.getElementById('summarySection');
    const tipsSection     = document.getElementById('tipsSection');
    const anomalySection  = document.getElementById('anomalySection');
    const selMonth        = document.getElementById('selMonth');
    const selYear         = document.getElementById('selYear');

    let polling  = null;
    let isLoading = false;

    // Month/year change → reload
    [selMonth, selYear].forEach(el => {
        el.addEventListener('change', () => {
            const url = new URL(window.location.href);
            url.searchParams.set('month', selMonth.value);
            url.searchParams.set('year',  selYear.value);
            window.location.href = url.toString();
        });
    });

    btnGenerate.addEventListener('click', generate);

    function generate() {
        if (isLoading) return;
        setLoading(true);

        fetch(@json(route('ai.insights.generate')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ month: selMonth.value, year: selYear.value }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'queued' || data.status === 'processing') {
                startPolling();
            } else {
                setLoading(false);
            }
        })
        .catch(() => { setLoading(false); alert('Gagal memulai proses. Coba lagi.'); });
    }

    function startPolling() {
        if (polling) clearInterval(polling);
        const stopAt = Date.now() + 120000;

        polling = setInterval(() => {
            if (Date.now() > stopAt) {
                clearInterval(polling); polling = null;
                setLoading(false);
                alert('Proses memakan waktu lebih lama. Coba refresh halaman.');
                return;
            }
            fetch(@json(route('ai.insights.status')) + '?month=' + selMonth.value + '&year=' + selYear.value, {
                headers: { 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                if (data.ready && data.insight) {
                    clearInterval(polling); polling = null;
                    renderInsight(data.insight);
                    setLoading(false);
                }
            });
        }, 2500);
    }

    function setLoading(state) {
        isLoading = state;
        btnGenerate.disabled = state;
        btnGenerateText.textContent = state ? 'Memproses...' : (document.getElementById('summarySection').querySelector('.summary-text') ? 'Refresh Insight' : 'Generate Insight');

        if (state) {
            summarySection.innerHTML = `
                <div class="loading-state">
                    <div class="ai-spinner-box">✦</div>
                    <p>AI sedang menganalisis keuangan Anda…</p>
                    <div class="progress" style="width:160px;height:3px">
                        <div class="progress-bar" style="width:100%;background:linear-gradient(90deg,#6366f1,#8b5cf6);animation:none"></div>
                    </div>
                </div>`;
            tipsSection.innerHTML    = skeletonTips();
            anomalySection.innerHTML = skeletonAnomaly();
        }
    }

    function skeletonTips() {
        return [1,2,3].map(() => `
            <div class="tip-item">
                <div class="skeleton" style="width:26px;height:26px;border-radius:8px;flex-shrink:0"></div>
                <div style="flex:1">
                    <div class="skeleton" style="width:55%;height:10px;margin-bottom:8px"></div>
                    <div class="skeleton" style="width:100%;height:10px;margin-bottom:5px"></div>
                    <div class="skeleton" style="width:75%;height:10px"></div>
                </div>
            </div>`).join('');
    }

    function skeletonAnomaly() {
        return [1,2].map(() => `
            <div style="display:flex;gap:10px;margin-bottom:10px">
                <div class="skeleton" style="width:24px;height:24px;border-radius:7px;flex-shrink:0"></div>
                <div style="flex:1">
                    <div class="skeleton" style="width:40%;height:10px;margin-bottom:6px"></div>
                    <div class="skeleton" style="width:90%;height:10px"></div>
                </div>
            </div>`).join('');
    }

    function renderInsight(insight) {
        // Summary
        if (insight.summary) {
            summarySection.innerHTML = `
                <div class="summary-text">${esc(insight.summary).replace(/\n/g,'<br>')}</div>
                <div class="summary-footer">
                    <i class="bi bi-clock"></i> Baru saja diperbarui
                </div>`;
        }

        // Tips
        if (insight.tips && insight.tips.length > 0) {
            tipsSection.innerHTML = '<div class="tips-list">' + insight.tips.map((t, i) => `
                <div class="tip-item">
                    <div class="tip-number">${i + 1}</div>
                    <div>
                        ${t.category ? `<div class="tip-cat">${esc(t.category)}</div>` : ''}
                        <div class="tip-text">${esc(t.tip)}</div>
                        ${t.potential_saving ? `<div class="tip-saving"><i class="bi bi-arrow-down-circle-fill"></i> Potensi hemat: ${esc(t.potential_saving)}</div>` : ''}
                    </div>
                </div>`).join('') + '</div>';
        } else {
            tipsSection.innerHTML = `<div class="no-anomaly"><i class="bi bi-info-circle" style="color:#6b7280"></i><br>Belum ada saran tersedia.</div>`;
        }

        // Anomalies
        const icons = { high:'🔴', medium:'🟡', low:'🟢' };
        if (insight.anomalies && insight.anomalies.length > 0) {
            anomalySection.innerHTML = '<div class="anomaly-list">' + insight.anomalies.map(a => `
                <div class="anomaly-item sev-${a.severity||'low'}">
                    <div class="sev-icon">${icons[a.severity]||'🔵'}</div>
                    <div>
                        <div class="anomaly-cat">${esc(a.category||'Tidak dikategorikan')}</div>
                        <div class="anomaly-desc">${esc(a.description)}</div>
                    </div>
                </div>`).join('') + '</div>';
        } else {
            anomalySection.innerHTML = `<div class="no-anomaly"><i class="bi bi-check-circle-fill"></i><br>Tidak ada pengeluaran tidak biasa terdeteksi. Bagus!</div>`;
        }
    }

    function esc(s) {
        return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>
@endpush