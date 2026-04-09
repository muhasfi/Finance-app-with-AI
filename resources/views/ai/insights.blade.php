@extends('layouts.app')
@section('title', 'AI Insight Keuangan')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row align-items-center">
            <div class="col-12 col-md-6">
                <h3>AI Insight Keuangan</h3>
                <p class="text-subtitle text-muted">Ringkasan dan saran berdasarkan data keuangan Anda</p>
            </div>
            <div class="col-12 col-md-6 d-flex justify-content-end gap-2 align-items-center">
                {{-- Pilih bulan & tahun --}}
                <select id="selectMonth" class="form-select form-select-sm" style="width:auto">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>
                <select id="selectYear" class="form-select form-select-sm" style="width:auto">
                    @foreach(range(now()->year, now()->year - 2) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button id="btnGenerate" class="btn btn-primary btn-sm">
                    <i class="bi bi-stars me-1"></i>
                    {{ $insight ? 'Refresh' : 'Generate Insight' }}
                </button>
            </div>
        </div>
    </div>

    {{-- Status processing --}}
    <div id="processingAlert" class="alert alert-info d-none" role="alert">
        <div class="d-flex align-items-center gap-2">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <span>AI sedang menganalisis data keuangan Anda... Halaman akan otomatis diperbarui.</span>
        </div>
    </div>

    {{-- Insight content --}}
    <div id="insightContent">
        @if ($insight)
            @include('ai.partials.insight-content', ['insight' => $insight])
        @else
            <div class="card">
                <div class="card-body text-center py-5" id="emptyState">
                    <i class="bi bi-stars fs-1 text-muted d-block mb-3"></i>
                    <h5 class="text-muted">Belum ada insight untuk bulan ini</h5>
                    <p class="text-muted small mb-4">Klik "Generate Insight" untuk menganalisis data keuangan Anda dengan AI.</p>
                    <button id="btnGenerateEmpty" class="btn btn-primary">
                        <i class="bi bi-stars me-1"></i> Generate Insight Sekarang
                    </button>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
let pollInterval = null;

function getSelectedMonth() { return document.getElementById('selectMonth').value; }
function getSelectedYear()  { return document.getElementById('selectYear').value; }

function startPolling() {
    document.getElementById('processingAlert').classList.remove('d-none');
    if (pollInterval) clearInterval(pollInterval);

    pollInterval = setInterval(async () => {
        const res  = await fetch(`/ai/insights/status?month=${getSelectedMonth()}&year=${getSelectedYear()}`);
        const data = await res.json();

        if (data.ready) {
            clearInterval(pollInterval);
            document.getElementById('processingAlert').classList.add('d-none');
            window.location.reload();
        }
    }, 3000); // cek setiap 3 detik
}

async function generateInsight() {
    const btn = document.getElementById('btnGenerate');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

    try {
        const res  = await fetch('/ai/insights/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                month: getSelectedMonth(),
                year:  getSelectedYear(),
            }),
        });
        const data = await res.json();

        if (data.status === 'queued' || data.status === 'processing') {
            startPolling();
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-stars me-1"></i> Generate Insight';
        alert('Gagal menghubungi AI. Coba lagi.');
    }
}

document.getElementById('btnGenerate').addEventListener('click', generateInsight);

const emptyBtn = document.getElementById('btnGenerateEmpty');
if (emptyBtn) emptyBtn.addEventListener('click', generateInsight);
</script>
@endpush
