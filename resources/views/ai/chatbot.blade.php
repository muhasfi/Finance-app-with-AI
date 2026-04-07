@extends('layouts.app')
@section('title', 'Tanya Fina — Asisten Keuangan AI')

@push('styles')
<style>
    /* ── Chat Layout ── */
    .chat-wrapper {
        display: flex;
        gap: 1.25rem;
        height: calc(100vh - 200px);
        min-height: 520px;
    }

    /* ── Chat Utama ── */
    .chat-card {
        flex: 1;
        display: flex;
        flex-direction: column;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,.07);
        box-shadow: 0 4px 24px rgba(0,0,0,.06);
    }

    /* Header */
    .chat-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        flex-shrink: 0;
    }
    .fina-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(99,102,241,.3);
    }
    .fina-info .fina-name {
        font-weight: 600;
        font-size: .925rem;
        color: #1a1a2e;
        line-height: 1.2;
    }
    .fina-info .fina-desc {
        font-size: .75rem;
        color: #6b7280;
        margin-top: 1px;
    }
    .fina-status {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: .72rem;
        color: #10b981;
        font-weight: 500;
    }
    .fina-status-dot {
        width: 7px;
        height: 7px;
        background: #10b981;
        border-radius: 50%;
        animation: pulse-dot 2s infinite;
    }
    @keyframes pulse-dot {
        0%,100% { opacity: 1; }
        50% { opacity: .4; }
    }

    /* Messages area */
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f8f9fc;
        display: flex;
        flex-direction: column;
        gap: 14px;
        scroll-behavior: smooth;
    }
    .chat-messages::-webkit-scrollbar { width: 4px; }
    .chat-messages::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

    /* Bubble */
    .msg {
        display: flex;
        gap: 8px;
        max-width: 80%;
        animation: msg-in .2s ease;
    }
    @keyframes msg-in {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .msg.user-msg { align-self: flex-end; flex-direction: row-reverse; }

    .msg-avatar-sm {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .msg-avatar-sm.ai-av  { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
    .msg-avatar-sm.usr-av { background: #e0e7ff; color: #6366f1; font-weight: 700; font-size: 11px; }

    .msg-bubble {
        padding: 10px 14px;
        border-radius: 14px;
        font-size: .855rem;
        line-height: 1.65;
        color: #1f2937;
    }
    .msg.ai-msg .msg-bubble {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-bottom-left-radius: 4px;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
    }
    .msg.user-msg .msg-bubble {
        background: linear-gradient(135deg, #6366f1, #7c3aed);
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    /* Typing indicator */
    .typing-indicator {
        display: flex;
        gap: 4px;
        align-items: center;
        padding: 12px 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        border-bottom-left-radius: 4px;
        width: fit-content;
    }
    .typing-dot {
        width: 7px;
        height: 7px;
        background: #9ca3af;
        border-radius: 50%;
        animation: bounce 1.2s infinite;
    }
    .typing-dot:nth-child(2) { animation-delay: .2s; }
    .typing-dot:nth-child(3) { animation-delay: .4s; }
    @keyframes bounce {
        0%,60%,100% { transform: translateY(0); }
        30% { transform: translateY(-6px); }
    }

    /* Chat input */
    .chat-input-area {
        padding: 14px 16px;
        background: #fff;
        border-top: 1px solid #f0f0f0;
        flex-shrink: 0;
    }
    .chat-input-row {
        display: flex;
        gap: 8px;
        align-items: flex-end;
    }
    .chat-input {
        flex: 1;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: .875rem;
        resize: none;
        min-height: 44px;
        max-height: 120px;
        outline: none;
        transition: border-color .15s;
        font-family: inherit;
        color: #1f2937;
        background: #f9fafb;
    }
    .chat-input:focus { border-color: #6366f1; background: #fff; }
    .chat-input::placeholder { color: #9ca3af; }
    .btn-send {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #6366f1, #7c3aed);
        border: none;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: opacity .15s, transform .1s;
        flex-shrink: 0;
    }
    .btn-send:hover  { opacity: .9; }
    .btn-send:active { transform: scale(.95); }
    .btn-send:disabled { opacity: .5; cursor: not-allowed; }

    /* ── Sidebar ── */
    .chat-sidebar {
        width: 260px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .sidebar-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,.07);
        box-shadow: 0 2px 10px rgba(0,0,0,.04);
        overflow: hidden;
    }
    .sidebar-card-header {
        padding: 13px 16px 11px;
        font-size: .8rem;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .sidebar-card-header i { color: #6366f1; }

    /* Quick questions */
    .quick-list { padding: 10px 12px; display: flex; flex-direction: column; gap: 6px; }
    .quick-btn {
        width: 100%;
        text-align: left;
        background: #f8f9fc;
        border: 1px solid #e9eaf0;
        border-radius: 9px;
        padding: 8px 11px;
        font-size: .775rem;
        color: #4b5563;
        cursor: pointer;
        transition: all .15s;
        line-height: 1.4;
    }
    .quick-btn:hover {
        background: #eef0ff;
        border-color: #c7d2fe;
        color: #4338ca;
    }

    /* Tip info */
    .sidebar-tip {
        padding: 12px 14px;
        font-size: .75rem;
        color: #6b7280;
        line-height: 1.6;
    }
    .sidebar-tip strong { color: #374151; }

    /* Rate limit bar */
    .rate-info {
        padding: 10px 14px;
        font-size: .72rem;
        color: #9ca3af;
        border-top: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .rate-bar {
        flex: 1;
        height: 3px;
        background: #f3f4f6;
        border-radius: 99px;
        overflow: hidden;
    }
    .rate-fill {
        height: 100%;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        border-radius: 99px;
        transition: width .3s;
    }

    /* Error toast */
    .chat-error {
        display: none;
        padding: 8px 13px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        font-size: .775rem;
        color: #dc2626;
        margin-bottom: 8px;
        align-items: center;
        gap: 7px;
    }
    .chat-error.show { display: flex; }

    @media (max-width: 768px) {
        .chat-wrapper { flex-direction: column; height: auto; }
        .chat-card    { height: 60vh; }
        .chat-sidebar { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-8">
                <h3 class="d-flex align-items-center gap-2">
                    <span style="background:linear-gradient(135deg,#6366f1,#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
                        Tanya Fina
                    </span>
                    <span class="badge rounded-pill" style="background:#eef0ff;color:#6366f1;font-size:.7rem;font-weight:600;-webkit-text-fill-color:#6366f1;">AI</span>
                </h3>
                <p class="text-subtitle text-muted">Asisten keuangan pribadi yang memahami data transaksi Anda</p>
            </div>
            <div class="col-12 col-md-4 d-flex justify-content-md-end mt-2 mt-md-0">
                <button class="btn btn-light btn-sm" id="btnReset">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Percakapan
                </button>
            </div>
        </div>
    </div>

    <div class="chat-wrapper">

        {{-- ── MAIN CHAT ── --}}
        <div class="chat-card">

            {{-- Header --}}
            <div class="chat-header">
                <div class="fina-avatar">✦</div>
                <div class="fina-info">
                    <div class="fina-name">Fina</div>
                    <div class="fina-desc">Asisten Keuangan AI · Gemini</div>
                </div>
                <div class="fina-status">
                    <div class="fina-status-dot"></div>
                    Online
                </div>
            </div>

            {{-- Messages --}}
            <div class="chat-messages" id="chatMessages">
                <div class="msg ai-msg" id="msgWelcome">
                    <div class="msg-avatar-sm ai-av">✦</div>
                    <div class="msg-bubble">
                        Halo, <strong>{{ auth()->user()->name }}</strong>! 👋<br>
                        Saya <strong>Fina</strong>, asisten keuangan AI Anda. Saya bisa bantu menjawab pertanyaan seputar keuangan Anda — seperti saldo, pengeluaran, tren, atau saran hemat.<br><br>
                        Mau tanya apa hari ini?
                    </div>
                </div>

                {{-- Typing indicator (hidden by default) --}}
                <div class="msg ai-msg" id="typingMsg" style="display:none">
                    <div class="msg-avatar-sm ai-av">✦</div>
                    <div class="typing-indicator">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                </div>
            </div>

            {{-- Input --}}
            <div class="chat-input-area">
                <div class="chat-error" id="chatError">
                    <i class="bi bi-exclamation-circle"></i>
                    <span id="chatErrorText"></span>
                </div>
                <div class="chat-input-row">
                    <textarea
                        id="chatInput"
                        class="chat-input"
                        placeholder="Tanya sesuatu tentang keuangan Anda..."
                        rows="1"
                        maxlength="500"
                    ></textarea>
                    <button class="btn-send" id="btnSend" title="Kirim">
                        <i class="bi bi-send-fill" style="font-size:.85rem"></i>
                    </button>
                </div>
                <div class="d-flex justify-content-between mt-1 px-1">
                    <span class="text-muted" style="font-size:.7rem">Enter untuk kirim · Shift+Enter baris baru</span>
                    <span class="text-muted" id="charCount" style="font-size:.7rem">0/500</span>
                </div>
            </div>
        </div>

        {{-- ── SIDEBAR ── --}}
        <div class="chat-sidebar">

            {{-- Quick Questions --}}
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <i class="bi bi-lightning-fill"></i>
                    Pertanyaan Cepat
                </div>
                <div class="quick-list">
                    @php
                    $quickQuestions = [
                        'Berapa total saldo semua rekening saya?',
                        'Apakah keuangan saya sehat bulan ini?',
                        'Kategori apa yang paling banyak pengeluarannya?',
                        'Berikan 3 saran hemat untuk saya',
                        'Bandingkan pengeluaran bulan ini vs bulan lalu',
                        'Ada transaksi yang tidak biasa?',
                    ];
                    @endphp
                    @foreach($quickQuestions as $q)
                    <button class="quick-btn" onclick="fillQuestion(this)">{{ $q }}</button>
                    @endforeach
                </div>
            </div>

            {{-- Tips --}}
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <i class="bi bi-info-circle-fill"></i>
                    Tips Bertanya
                </div>
                <div class="sidebar-tip">
                    <strong>Fina tahu data Anda</strong> — termasuk saldo rekening, riwayat transaksi, dan kategori pengeluaran.<br><br>
                    Coba tanya hal spesifik seperti jumlah, persentase, atau perbandingan untuk jawaban yang lebih akurat.
                </div>
                <div class="rate-info">
                    <span id="rateText">0/20 pesan</span>
                    <div class="rate-bar">
                        <div class="rate-fill" id="rateFill" style="width:0%"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const input    = document.getElementById('chatInput');
    const btnSend  = document.getElementById('btnSend');
    const btnReset = document.getElementById('btnReset');
    const messages = document.getElementById('chatMessages');
    const typing   = document.getElementById('typingMsg');
    const errBox   = document.getElementById('chatError');
    const errText  = document.getElementById('chatErrorText');
    const charCount= document.getElementById('charCount');
    const rateFill = document.getElementById('rateFill');
    const rateText = document.getElementById('rateText');

    let msgCount = 0;
    let sending  = false;

    // ── Auto-resize textarea ──
    input.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        charCount.textContent = this.value.length + '/500';
    });

    // ── Enter to send ──
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    btnSend.addEventListener('click', sendMessage);
    btnReset.addEventListener('click', resetChat);

    function fillQuestion(el) {
        input.value = el.textContent.trim();
        input.dispatchEvent(new Event('input'));
        input.focus();
    }
    // Expose globally for onclick
    window.fillQuestion = fillQuestion;

    function appendMsg(role, html) {
        // Remove typing indicator first (will re-add at end)
        typing.style.display = 'none';

        const isUser = role === 'user';
        const userName = @json(auth()->user()->name);
        const initials = userName.split(' ').map(w => w[0]).join('').substring(0,2).toUpperCase();

        const div = document.createElement('div');
        div.className = 'msg ' + (isUser ? 'user-msg' : 'ai-msg');
        div.innerHTML = `
            <div class="msg-avatar-sm ${isUser ? 'usr-av' : 'ai-av'}">${isUser ? initials : '✦'}</div>
            <div class="msg-bubble">${html}</div>
        `;

        // Insert before typing indicator
        messages.insertBefore(div, typing);
        scrollBottom();
    }

    function scrollBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function showError(msg) {
        errText.textContent = msg;
        errBox.classList.add('show');
        setTimeout(() => errBox.classList.remove('show'), 5000);
    }

    function updateRate(count) {
        msgCount = count;
        const pct = Math.min((count / 20) * 100, 100);
        rateFill.style.width = pct + '%';
        rateText.textContent = count + '/20 pesan';
        if (count >= 20) {
            rateFill.style.background = '#ef4444';
        }
    }

    async function sendMessage() {
        const text = input.value.trim();
        if (!text || sending) return;

        sending = true;
        btnSend.disabled = true;
        errBox.classList.remove('show');

        // Append user bubble
        appendMsg('user', escapeHtml(text));
        input.value = '';
        input.style.height = 'auto';
        charCount.textContent = '0/500';

        // Show typing
        typing.style.display = 'flex';
        scrollBottom();

        try {
            const res = await fetch(@json(route('ai.chat.message')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: text }),
            });

            const data = await res.json();

            if (!res.ok) {
                if (res.status === 429) {
                    showError(data.error || 'Terlalu banyak pesan. Tunggu sebentar.');
                } else {
                    showError(data.error || 'Terjadi kesalahan. Coba lagi.');
                }
                typing.style.display = 'none';
                return;
            }

            updateRate(msgCount + 1);
            appendMsg('ai', formatReply(data.reply));

        } catch (err) {
            typing.style.display = 'none';
            showError('Koneksi bermasalah. Periksa internet Anda.');
        } finally {
            sending = false;
            btnSend.disabled = false;
            input.focus();
        }
    }

    async function resetChat() {
        if (!confirm('Reset percakapan? Riwayat chat akan dihapus.')) return;

        try {
            const res = await fetch(@json(route('ai.chat.reset')), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();

            // Clear messages (keep welcome)
            const allMsgs = messages.querySelectorAll('.msg:not(#typingMsg)');
            allMsgs.forEach(m => m.remove());

            // Add fresh welcome
            appendMsg('ai', data.reply || 'Halo lagi! Percakapan baru dimulai. Ada yang bisa saya bantu?');
            updateRate(0);

        } catch (err) {
            showError('Gagal mereset percakapan.');
        }
    }

    // Format reply: bold **text**, newlines → <br>
    function formatReply(text) {
        return text
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');
    }

    function escapeHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
})();
</script>
@endpush