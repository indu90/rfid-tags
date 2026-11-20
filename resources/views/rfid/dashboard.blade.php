@extends('tablar::page')

@section('title', 'Asset Management System - Dashboard')

@section('content')
<!-- Page Header -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">RFID Asset & Movement Overview</div>
                <h2 class="page-title">Dashboard</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('asset.map') }}" class="btn btn-outline-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-link" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M9 15l6 -6" />
                            <path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464" />
                            <path d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463" />
                        </svg>
                        Map RFID Tag
                    </a>
                    <a href="{{ route('asset.create') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                        Register Asset
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        <!-- Top 4 KPI Cards -->
        <div class="row row-deck row-cards mb-4">
            <!-- Total Assets -->
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="subheader">Total Assets</div>
                            <div class="ms-auto">
                                <span class="avatar avatar-sm bg-green-lt text-green">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" />
                                        <path d="M12 12l8 -4.5" />
                                        <path d="M12 12l0 9" />
                                        <path d="M12 12l-8 -4.5" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="h1 mb-2">{{ $totalAssets ?? 0 }}</div>
                        <div class="d-flex gap-2 small">
                            <span class="badge bg-green-lt">Available: {{ $availableAssets ?? 0 }}</span>
                            <span class="badge bg-blue-lt">In Use: {{ $inUseAssets ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tagged / Mapped Assets -->
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="subheader">Mapped RFID Tags</div>
                            <div class="ms-auto">
                                <span class="avatar avatar-sm bg-azure-lt text-azure">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                        <path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="h1 mb-2">{{ $mappedAssets ?? 0 }}</div>
                        <div class="d-flex gap-2 small">
                            <span class="badge bg-secondary-lt">Unmapped: {{ $unmappedAssets ?? 0 }}</span>
                            <span class="badge bg-orange-lt">Total Scans: {{ $totalScans ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- IN / OUT Movement -->
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="subheader">Gate Movement (IN / OUT)</div>
                            <div class="ms-auto">
                                <span class="avatar avatar-sm bg-teal-lt text-teal">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M17 10l-14 0l4 -4" />
                                        <path d="M7 14l14 0l-4 4" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="h1 mb-2">
                            <span class="text-green">{{ $assetsIn ?? 0 }}</span>
                            <span class="text-muted fs-3">/</span>
                            <span class="text-red">{{ $assetsOut ?? 0 }}</span>
                        </div>
                        <div class="d-flex gap-2 small">
                            <span class="badge bg-green-lt">IN Scans: {{ $assetsIn ?? 0 }}</span>
                            <span class="badge bg-red-lt">OUT Scans: {{ $assetsOut ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Devices -->
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="subheader">RFID Readers</div>
                            <div class="ms-auto">
                                <span class="avatar avatar-sm bg-warning-lt text-warning">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 18l.01 0" />
                                        <path d="M9.172 15.172a4 4 0 0 1 5.656 0" />
                                        <path d="M6.343 12.343a8 8 0 0 1 11.314 0" />
                                        <path d="M3.515 9.515c4.686 -4.687 12.284 -4.687 17 0" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <div class="h1 mb-2">{{ $totalDevices ?? 0 }}</div>
                        <div class="d-flex gap-2 small">
                            <span class="badge bg-green-lt">Active: {{ $activeDevices ?? 0 }}</span>
                            <span class="badge bg-info-lt">Mapped: {{ $mappedDevices ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Middle Split Section: Movement & Asset Breakdown + Recent Tag Mappings -->
        <div class="row row-deck row-cards mb-4">
            <!-- Left: Asset & Movement Breakdown -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Asset & Movement Summary</h3>
                        <div class="card-actions">
                            <a href="{{ route('asset.index') }}" class="btn btn-sm btn-outline-secondary">View All Assets</a>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                        $safeTotal = max(1, (int) ($totalAssets ?? 0));
                        $availPct = round((($availableAssets ?? 0) / $safeTotal) * 100);
                        $inUsePct = round((($inUseAssets ?? 0) / $safeTotal) * 100);
                        $maintPct = round((($otherAssets ?? 0) / $safeTotal) * 100);
                        @endphp

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Asset Status Distribution</span>
                                <span class="fw-semibold">{{ $totalAssets ?? 0 }} Total</span>
                            </div>
                            <div class="progress progress-separated mb-2">
                                <div class="progress-bar bg-success" role="progressbar" @style(['width'=> $availPct . '%']) aria-label="Available"></div>
                                <div class="progress-bar bg-primary" role="progressbar" @style(['width'=> $inUsePct . '%']) aria-label="In Use"></div>
                                <div class="progress-bar bg-warning" role="progressbar" @style(['width'=> $maintPct . '%']) aria-label="Other"></div>
                            </div>
                            <div class="row text-center small mt-2">
                                <div class="col">
                                    <span class="legend bg-success"></span> Available ({{ $availableAssets ?? 0 }})
                                </div>
                                <div class="col">
                                    <span class="legend bg-primary"></span> In Use ({{ $inUseAssets ?? 0 }})
                                </div>
                                <div class="col">
                                    <span class="legend bg-warning"></span> Maint / Other ({{ $otherAssets ?? 0 }})
                                </div>
                            </div>
                        </div>

                        <div class="divide-y">
                            <div class="py-2 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="badge bg-green-lt me-2">IN</span>
                                    <span>Gate IN Detections</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <strong>{{ $assetsIn ?? 0 }}</strong>
                                    <a href="{{ route('asset.index') }}" class="btn btn-sm btn-ghost-success">Open</a>
                                </div>
                            </div>
                            <div class="py-2 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="badge bg-red-lt me-2">OUT</span>
                                    <span>Gate OUT Detections</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <strong>{{ $assetsOut ?? 0 }}</strong>
                                    <a href="{{ route('asset.index') }}" class="btn btn-sm btn-ghost-danger">Open</a>
                                </div>
                            </div>
                            <div class="py-2 d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="badge bg-azure-lt me-2">READERS</span>
                                    <span>Free / Unassigned Devices</span>
                                </div>
                                <strong>{{ $freeDevices ?? 0 }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Recent Tag Assignments -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Tag Mappings</h3>
                        <div class="card-actions">
                            <a href="{{ route('asset.map') }}" class="btn btn-sm btn-primary">Assign / Remap</a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter text-nowrap">
                            <thead>
                                <tr>
                                    <th>Asset</th>
                                    <th>RFID EPC / Serial</th>
                                    <th>Action</th>
                                    <th>Mapped At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentMappings ?? [] as $map)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $map->asset_name ?? 'Asset #' . $map->asset_id }}</div>
                                        <div class="text-muted small">{{ $map->asset_code ?? '-' }}</div>
                                    </td>
                                    <td><code>{{ $map->serial_no }}</code></td>
                                    <td>
                                        @php
                                        $badgeColor = match($map->action ?? 'mapped') {
                                        'reassigned' => 'bg-warning-lt text-warning',
                                        'remapped' => 'bg-info-lt text-info',
                                        default => 'bg-green-lt text-green',
                                        };
                                        @endphp
                                        <span class="badge {{ $badgeColor }} text-uppercase">{{ $map->action ?? 'mapped' }}</span>
                                    </td>
                                    <td class="text-muted small">{{ $map->created_at }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No RFID tag mappings recorded yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live / Recent RFID Scan Logs Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Recent RFID Scan Transactions</h3>
                <div class="card-actions">
                    <span class="badge bg-azure-lt">Latest 10 Scans</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Scan Time</th>
                            <th>Asset</th>
                            <th>RFID Tag (EPC)</th>
                            <th>Direction</th>
                            <th>Reader / Antenna</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody id="rfid-stream-body">
                        @forelse($transactions ?? [] as $index => $txn)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>{{ $txn->read_at ?? $txn->created_at }}</td>
                            <td>
                                @if(!empty($txn->asset_number))
                                <span class="fw-semibold">{{ $txn->asset_number }}</span>
                                @if(!empty($txn->asset_name))
                                <span class="text-muted">— {{ $txn->asset_name }}</span>
                                @endif
                                @else
                                <a href="{{ route('asset.map', ['serial_no' => $txn->epc]) }}" class="badge bg-warning-lt text-warning">
                                    Unmapped (Click to Map)
                                </a>
                                @endif
                            </td>
                            <td><code>{{ $txn->epc }}</code></td>
                            <td>
                                @if(($txn->status ?? '') === 'IN')
                                <span class="badge bg-green-lt text-green">IN</span>
                                @elseif(($txn->status ?? '') === 'OUT')
                                <span class="badge bg-red-lt text-red">OUT</span>
                                @else
                                <span class="badge bg-secondary-lt">SCAN</span>
                                @endif
                            </td>
                            <td>
                                {{ $txn->reader_name ?: ($txn->device_id ?? 'unknown') }}
                                @if(!empty($txn->antenna_no))
                                <span class="text-muted small">(Ant {{ $txn->antenna_no }})</span>
                                @endif
                            </td>
                            <td>{{ $txn->location ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No scan transaction record available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Device Status Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Reader / Device Status</h3>
            </div>
            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Device Name (Device ID)</th>
                            <th>Location Name</th>
                            <th>Location Type</th>
                            <th>Area Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices ?? [] as $index => $device)
                        <tr>
                            <td class="text-muted">{{ $index + 1 }}</td>
                            <td>{{ $device->name ?? 'Reader' }} ({{ $device->device_id ?? $device->id }})</td>
                            <td>{{ $device->location_name ?? '-' }}</td>
                            <td>{{ $device->location_type ?? '-' }}</td>
                            <td>{{ $device->area_name ?? '-' }}</td>
                            <td>
                                @php $isOnline = in_array(strtolower((string) ($device->status ?? '')), ['active', 'online', '1'], true); @endphp
                                <span class="badge {{ $isOnline ? 'bg-success-lt text-success' : 'bg-secondary-lt' }}">
                                    {{ $device->status ?? 'Unknown' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No device data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection