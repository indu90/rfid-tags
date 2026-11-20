@extends('tablar::page')

@section('title', 'Assign or Update Serial / EPC Tag')

@section('content')
<div class="container-xl">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="page-title m-0">Assign or Update Serial / EPC Tag</h2>
        <a href="{{ route('asset.index') }}" class="btn btn-outline-secondary">Back to Asset List</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="mapTagForm" method="POST" action="{{ url('asset/map') }}">
                @csrf

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-azure-lt text-azure" id="pollStatusBadge">Auto-poll: ON (3s)</span>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" id="autoPollToggle" checked>
                        <label class="form-check-label small" for="autoPollToggle">Auto-poll scans</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label required" for="serial_no">Scanned RFID Tag / EPC</label>
                    <div class="input-group">
                        <input type="text" name="serial_no" id="serial_no" class="form-control"
                            placeholder="Scan RFID tag, fetch latest scan, or enter serial no..."
                            autocomplete="off" autofocus required>
                        <button type="button" class="btn btn-outline-primary" id="fetchScanBtn">
                            Fetch Scan
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="clearScanBtn">
                            Clear
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label required" for="asset_id">Asset</label>
                    <select name="asset_id" id="asset_id" class="form-select" required>
                        <option value="">Select Asset...</option>
                        @foreach(($assets ?? collect()) as $item)
                        <option value="{{ $item->id }}"
                            data-category="{{ $item->category ?? '' }}"
                            data-status="{{ $item->status ?? 'available' }}">
                            {{ $item->asset_code }} — {{ $item->name }}
                            @if($item->serial_no) (Current Tag: {{ $item->serial_no }}) @endif
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="category">Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">Keep Current / None</option>
                        @foreach(($categories ?? collect()) as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="location_id">Location</label>
                    <select name="location_id" id="location_id" class="form-select">
                        <option value="">Keep Current Location</option>
                        @foreach(($locations ?? collect()) as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="available">Available</option>
                        <option value="in_use">In Use</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>

                <div id="mapFeedback" class="alert d-none" role="alert"></div>

                <button type="submit" class="btn btn-primary w-100" id="mapSubmitBtn">
                    Map Tag to Asset
                </button>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('mapTagForm');
        const serialInput = document.getElementById('serial_no');
        const assetSelect = document.getElementById('asset_id');
        const categorySelect = document.getElementById('category');
        const statusSelect = document.getElementById('status');
        const locationSelect = document.getElementById('location_id');
        const feedback = document.getElementById('mapFeedback');
        const clearBtn = document.getElementById('clearScanBtn');
        const fetchScanBtn = document.getElementById('fetchScanBtn');
        const autoPollToggle = document.getElementById('autoPollToggle');
        const pollStatusBadge = document.getElementById('pollStatusBadge');

        const urlParams = new URLSearchParams(window.location.search);
        const preselectedAssetId = urlParams.get('asset_id');
        const preselectedSerial = urlParams.get('serial_no');
        let hasUrlSelection = Boolean(preselectedSerial || preselectedAssetId);

        let lastSeenScanId = null;
        let pollTimer = null;
        let isSyncing = false;
        const POLL_INTERVAL_MS = 3000;

        function showAlert(type, text) {
            if (!feedback) return;
            feedback.className = `alert alert-${type}`;
            feedback.textContent = text;
            feedback.classList.remove('d-none');
        }

        function syncAssetSelection(assetId) {
            if (!assetSelect) return;

            if (!assetId) {
                assetSelect.value = '';
                return;
            }

            const matchedOption = [...assetSelect.options].find(
                (opt) => String(opt.value) === String(assetId)
            );

            if (matchedOption) {
                assetSelect.value = matchedOption.value;
                assetSelect.dispatchEvent(new Event('change'));
            } else {
                assetSelect.value = '';
            }
        }

        function syncLocationSelection(locationId) {
            if (!locationId || !locationSelect) return;

            const matchedLoc = [...locationSelect.options].find(
                (opt) => String(opt.value) === String(locationId)
            );

            if (matchedLoc) {
                locationSelect.value = matchedLoc.value;
            }
        }

        function applyScanToForm(data, {
            focusAsset = false,
            isNew = true
        } = {}) {
            if (!data || !data.serial_no) return;

            serialInput.value = data.serial_no;
            syncAssetSelection(data.asset_id);
            syncLocationSelection(data.location_id);

            const readerInfo = data.reader_name ? ` via ${data.reader_name}` : '';
            const prefix = isNew ? 'New scan detected' : 'Latest scan loaded';
            showAlert('info', `${prefix}: ${data.serial_no}${readerInfo}`);

            if (focusAsset && assetSelect) {
                assetSelect.focus();
            }
        }

        if (preselectedSerial && serialInput) {
            serialInput.value = preselectedSerial.trim();
        }

        if (preselectedAssetId && assetSelect) {
            const matchedOpt = [...assetSelect.options].find(
                (opt) => String(opt.value) === String(preselectedAssetId)
            );
            if (matchedOpt) {
                assetSelect.value = matchedOpt.value;
                assetSelect.dispatchEvent(new Event('change'));
            }
        }

        if (preselectedSerial) {
            showAlert('info', `Selected tag from link: ${preselectedSerial.trim()}`);
        }

        serialInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (assetSelect) assetSelect.focus();
            }
        });

        if (assetSelect) {
            assetSelect.addEventListener('change', () => {
                const opt = assetSelect.selectedOptions[0];
                if (!opt || !opt.value) return;
                if (categorySelect && opt.dataset.category) {
                    categorySelect.value = opt.dataset.category;
                }
                if (statusSelect && opt.dataset.status) {
                    statusSelect.value = opt.dataset.status;
                }
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                hasUrlSelection = false;
                serialInput.value = '';
                if (assetSelect) assetSelect.value = '';
                serialInput.focus();
            });
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (feedback) feedback.classList.add('d-none');

            const formData = new FormData(form);
            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    },
                    body: formData,
                });

                const data = await res.json();
                if (!res.ok) {
                    const firstErr = data.errors ?
                        Object.values(data.errors).flat()[0] :
                        (data.message || 'Failed to map RFID tag.');
                    showAlert('danger', firstErr);
                    return;
                }

                hasUrlSelection = false;
                showAlert('success', data.message || 'Tag mapped successfully.');
                serialInput.value = '';
                if (assetSelect) assetSelect.value = '';
                serialInput.focus();
            } catch (err) {
                showAlert('danger', 'Network error while mapping RFID tag.');
            }
        });

        async function syncFromLog() {
            if (isSyncing) return;
            isSyncing = true;
            try {
                await fetch("{{ url('asset/map/sync-log') }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    },
                });
            } catch (err) {
                // Ignore transient network errors during background sync
            } finally {
                isSyncing = false;
            }
        }

        async function fetchLatestScan({
            manual = false,
            syncFirst = false
        } = {}) {
            if (manual) {
                if (feedback) feedback.classList.add('d-none');
                if (fetchScanBtn) fetchScanBtn.disabled = true;
            }

            if (syncFirst) {
                await syncFromLog();
            }

            try {
                const res = await fetch("{{ url('asset/map/latest-scan') }}", {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!res.ok) {
                    if (manual) {
                        const errData = await res.json().catch(() => ({}));
                        showAlert('warning', errData.message || 'No recent RFID scan found.');
                    }
                    return;
                }

                const data = await res.json();
                if (!data.serial_no) return;

                const isFirstPoll = lastSeenScanId === null;
                const isNewScan = Number(data.scan_id) !== Number(lastSeenScanId);

                if (manual) {
                    hasUrlSelection = false;
                    lastSeenScanId = data.scan_id;
                    applyScanToForm(data, {
                        focusAsset: true,
                        isNew: isNewScan
                    });
                    return;
                }

                if (isFirstPoll) {
                    lastSeenScanId = data.scan_id;
                    if (!hasUrlSelection && serialInput.value.trim() === '') {
                        applyScanToForm(data, {
                            focusAsset: false,
                            isNew: false
                        });
                    }
                    return;
                }

                if (!isNewScan) {
                    return;
                }

                if (hasUrlSelection && serialInput.value.trim() !== '') {
                    return;
                }

                const userIsTyping = document.activeElement === serialInput && serialInput.value.trim() !== '';
                if (userIsTyping) {
                    return;
                }

                lastSeenScanId = data.scan_id;
                applyScanToForm(data, {
                    focusAsset: false,
                    isNew: true
                });
            } catch (err) {
                if (manual) {
                    showAlert('danger', 'Failed to reach scan endpoint.');
                }
            } finally {
                if (manual && fetchScanBtn) {
                    fetchScanBtn.disabled = false;
                }
            }
        }

        function stopPolling() {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
        }

        function startPolling() {
            stopPolling();

            if (!autoPollToggle || !autoPollToggle.checked) {
                if (pollStatusBadge) {
                    pollStatusBadge.className = 'badge bg-secondary-lt text-secondary';
                    pollStatusBadge.textContent = 'Auto-poll: OFF';
                }
                return;
            }

            if (pollStatusBadge) {
                pollStatusBadge.className = 'badge bg-azure-lt text-azure';
                pollStatusBadge.textContent = `Auto-poll: ON (${POLL_INTERVAL_MS / 1000}s)`;
            }

            fetchLatestScan({
                manual: false,
                syncFirst: true
            });
            pollTimer = setInterval(() => {
                if (!document.hidden) {
                    fetchLatestScan({
                        manual: false,
                        syncFirst: true
                    });
                }
            }, POLL_INTERVAL_MS);
        }

        if (fetchScanBtn) {
            fetchScanBtn.addEventListener('click', () => fetchLatestScan({
                manual: true,
                syncFirst: true
            }));
        }

        if (autoPollToggle) {
            autoPollToggle.addEventListener('change', startPolling);
        }

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && autoPollToggle && autoPollToggle.checked) {
                fetchLatestScan({
                    manual: false,
                    syncFirst: true
                });
            }
        });

        startPolling();
    });
</script>
@endsection