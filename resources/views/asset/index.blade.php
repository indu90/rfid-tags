@extends('tablar::page')

@section('title', 'Asset List')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Assets Management</div>
                <h2 class="page-title">Asset List & RFID Tag Tracking</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ route('asset.create') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
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
        <!-- KPI Summary Row -->
        <div class="row row-deck row-cards mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" />
                                        <path d="M12 12l8 -4.5" />
                                        <path d="M12 12l0 9" />
                                        <path d="M12 12l-8 -4.5" />
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">{{ $stats['total'] }} Total Assets</div>
                                <div class="text-secondary">Registered in inventory</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-azure text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                        <path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z" />
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">{{ $stats['mapped'] }} Tagged Assets</div>
                                <div class="text-secondary">Bound to RFID EPC</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-green text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M5 12l5 5l10 -10" />
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">{{ $stats['IN'] }} IN</div>
                                <div class="text-secondary">Operational status</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-red text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 9v4" />
                                        <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
                                        <path d="M12 16h.01" />
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">{{ $stats['OUT'] }} Out</div>
                                <div class="text-secondary">Needs verification</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('asset.index') }}" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search Asset Code, Name, Serial, or EPC...">
                    </div>
                    <!-- <div class="col-md-2">
                        <select name="category_id" class="form-select">
                        </select>
                    </div> -->
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach(['IN', 'OUT'] as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- <div class="col-md-2">
                        <select name="tag_state" class="form-select">
                            <option value="">All Tag States</option>
                            <option value="mapped" {{ request('tag_state') === 'mapped' ? 'selected' : '' }}>RFID Mapped</option>
                            <option value="unmapped" {{ request('tag_state') === 'unmapped' ? 'selected' : '' }}>Unmapped</option>
                        </select>
                    </div>-->
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                        <a href="{{ route('asset.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assets Table Card -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover">
                    <thead>
                        <tr>
                            <th>Asset Code</th>
                            <th>Asset Name & Serial</th>
                            <!--<th>Category</th>-->
                            <th>RFID Tag EPC</th>
                            <th>Last Reader / Signal</th>
                            <th>Last Seen</th>
                            <th>Status</th>
                            <!--<th class="w-1 text-end">Actions</th>-->
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                        @php
                        $badgeColor = match($asset->status) {
                        'Active' => 'bg-green-lt',
                        'In Maintenance' => 'bg-yellow-lt',
                        'Missing' => 'bg-red-lt',
                        default => 'bg-secondary-lt',
                        };
                        @endphp
                        <tr>
                            <td class="fw-bold text-primary">{{ $asset->asset_code }}</td>
                            <td>
                                <div>{{ $asset->name }}</div>
                                <div class="text-secondary small">S/N: {{ $asset->serial_no ?? 'N/A' }}</div>
                            </td>
                            <!-- <td>
                                <span class="badge bg-blue-lt">{{ $asset->category->category_name ?? 'Uncategorized' }}</span>
                            </td>-->
                            <td>
                                @if($asset->serial_no)
                                <code class="text-azure fw-bold">{{ $asset->serial_no }}</code>
                                @else
                                <span class="badge bg-orange-lt">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($asset->last_reader)
                                <div>Reader: <strong>{{ $asset->last_reader }}</strong> (Ant {{ $asset->last_antenna ?? '-' }})</div>
                                <div class="text-secondary small">RSSI: {{ $asset->last_rssi ?? 'N/A' }} dBm</div>
                                @else
                                <span class="text-secondary small">No scans yet</span>
                                @endif
                            </td>
                            <td>
                                @if($asset->last_seen_at)
                                <span title="{{ $asset->last_seen_at }}">
                                    {{ \Carbon\Carbon::parse($asset->last_seen_at)->diffForHumans() }}
                                </span>
                                @else
                                <span class="text-secondary">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $badgeColor }}">{{ $asset->status }}</span>
                            </td>
                            <!--<td class="text-end">
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-map-tag"
                                    data-asset-id="{{ $asset->id }}"
                                    data-asset-code="{{ $asset->asset_code }}"
                                    data-asset-name="{{ $asset->name }}"
                                    data-current-epc="{{ $asset->serial_no }}">
                                    {{ $asset->serial_no ? 'Re-map Tag' : 'Map Tag' }}
                            </button>-->
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-secondary">
                                No assets found matching the selected criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($assets->hasPages())
            <div class="card-footer d-flex align-items-center">
                <p class="m-0 text-secondary">
                    Showing <span>{{ $assets->firstItem() }}</span> to <span>{{ $assets->lastItem() }}</span> of <span>{{ $assets->total() }}</span> entries
                </p>
                <div class="ms-auto">
                    {{ $assets->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick RFID Tag Mapping Modal -->
<div class="modal modal-blur fade" id="mapTagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="mapTagForm">
                <div class="modal-header">
                    <h5 class="modal-title">Map RFID Tag to Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal_asset_id" name="asset_id">
                    <div class="mb-3">
                        <label class="form-label">Selected Asset</label>
                        <input type="text" id="modal_asset_label" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">RFID Tag EPC</label>
                        <input type="text" id="modal_rfid_epc" name="rfid_epc" class="form-control" placeholder="Scan or enter EPC hex string..." required>
                        <small class="form-hint">Place the RFID tag near the desktop reader or enter the EPC manually.</small>
                    </div>
                    <div id="mapAlertBox" class="alert d-none" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary ms-auto" id="btnSaveMapping">Save Mapping</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const mapModalEl = document.getElementById('mapTagModal');
        const mapModal = new bootstrap.Modal(mapModalEl);
        const mapTagForm = document.getElementById('mapTagForm');
        const alertBox = document.getElementById('mapAlertBox');

        // Event delegation for opening the Map Tag modal
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-map-tag');
            if (!btn) return;

            document.getElementById('modal_asset_id').value = btn.dataset.assetId;
            document.getElementById('modal_asset_label').value = `${btn.dataset.assetCode} - ${btn.dataset.assetName}`;
            document.getElementById('modal_rfid_epc').value = btn.dataset.currentEpc || '';
            alertBox.classList.add('d-none');

            mapModal.show();
            setTimeout(() => document.getElementById('modal_rfid_epc').focus(), 300);
        });

        // Submit RFID Tag Mapping via Vanilla JS Fetch API
        mapTagForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const saveBtn = document.getElementById('btnSaveMapping');
            saveBtn.disabled = true;

            const payload = {
                asset_id: document.getElementById('modal_asset_id').value,
                rfid_epc: document.getElementById('modal_rfid_epc').value.trim(),
            };

            try {
                const response = await fetch("{{ route('asset.map') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    alertBox.className = 'alert alert-success';
                    alertBox.textContent = result.message;
                    alertBox.classList.remove('d-none');
                    setTimeout(() => window.location.reload(), 700);
                } else {
                    alertBox.className = 'alert alert-danger';
                    alertBox.textContent = result.message || 'Unable to map RFID tag.';
                    alertBox.classList.remove('d-none');
                    saveBtn.disabled = false;
                }
            } catch (err) {
                alertBox.className = 'alert alert-danger';
                alertBox.textContent = 'Network error while saving RFID mapping.';
                alertBox.classList.remove('d-none');
                saveBtn.disabled = false;
            }
        });
    });
</script>
@endpush