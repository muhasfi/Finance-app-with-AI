<div class="row">

    {{-- Ringkasan bulanan --}}
    <div class="col-12">
        <div class="card border-start border-primary border-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-stars text-primary fs-5"></i>
                    <h5 class="mb-0">Ringkasan Keuangan</h5>
                    <small class="text-muted ms-auto">
                        Diperbarui {{ \Carbon\Carbon::parse($insight['generated_at'])->diffForHumans() }}
                    </small>
                </div>
                <p class="mb-0 text-secondary" style="line-height:1.7">
                    {{ $insight['summary'] }}
                </p>
            </div>
        </div>
    </div>

    {{-- Saran hemat --}}
    @if (! empty($insight['tips']))
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightbulb text-warning me-2"></i>Saran Hemat</h5>
                </div>
                <div class="card-body">
                    @foreach ($insight['tips'] as $i => $tip)
                        <div class="d-flex gap-3 {{ $i > 0 ? 'mt-3 pt-3 border-top' : '' }}">
                            <div class="flex-shrink-0">
                                <span class="badge bg-warning text-dark rounded-circle"
                                      style="width:28px;height:28px;display:flex;align-items:center;justify-content:center">
                                    {{ $i + 1 }}
                                </span>
                            </div>
                            <div>
                                @if ($tip['category'])
                                    <span class="badge bg-light text-dark mb-1">{{ $tip['category'] }}</span>
                                @endif
                                <p class="mb-1 small">{{ $tip['tip'] }}</p>
                                @if (! empty($tip['potential_saving']) && $tip['potential_saving'] !== '-')
                                    <small class="text-success fw-semibold">
                                        <i class="bi bi-piggy-bank me-1"></i>
                                        Potensi hemat: {{ $tip['potential_saving'] }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Anomali --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Deteksi Anomali</h5>
            </div>
            <div class="card-body">
                @if (empty($insight['anomalies']))
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle text-success fs-1 d-block mb-2"></i>
                        <p class="mb-0">Tidak ada pengeluaran tidak biasa terdeteksi bulan ini.</p>
                    </div>
                @else
                    @foreach ($insight['anomalies'] as $anomaly)
                        <div class="d-flex align-items-start gap-2 mb-3">
                            <span class="badge mt-1
                                @if($anomaly['severity'] === 'high') bg-danger
                                @elseif($anomaly['severity'] === 'medium') bg-warning text-dark
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($anomaly['severity']) }}
                            </span>
                            <div>
                                <p class="fw-semibold mb-0 small">{{ $anomaly['category'] }}</p>
                                <p class="text-muted mb-0 small">{{ $anomaly['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

</div>
