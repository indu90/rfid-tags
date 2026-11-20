@extends('tablar::page')

@section('title', 'Asset Mapping')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Asset RFID Mapping</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ url('asset') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-packages me-1"></i> Back to Asset List
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div id="mapAlert" class="alert d-none" role="alert"></div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assign or Update Serial / EPC Tag</h3>
            </div>
            <div class="card-body">
                <form id="mapTagForm" method="POST" action="{{ url('asset/map') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Select Asset</label>
                            <select name="asset_id" id="asset_id" class="form-select" required>
                                <option value="">Choose an asset...</option>
                                @foreach(($assets ?? collect()) as $asset)
                                <option value="{{ $asset->id }}"
                                    data-serial="{{ $asset->serial_no }}"
                                    data-name="{{ $asset->name }}"
                                    data-status="{{ $asset->status }}">
                                    {{ $asset->asset_code }} — {{ $asset->name }} ({{ $asset->serial_no }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">Serial No / RFID EPC</label>
                            <input type="text" name="serial_no" id="serial_no" class="form-control font-monospace" placeholder="Enter or scan EPC / serial number" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Asset Name</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Optional updated name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                                <option value="in_use" {{ request('status') === 'in_use' ? 'selected' : '' }}>In Use</option>
                                <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="retired" {{ request('status') === 'retired' ? 'selected' : '' }}>Retired</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" id="remarks" class="form-control" placeholder="Optional assignment or remap note">
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <button type="submit" id="mapSubmitBtn" class="btn btn-primary">
                            <i class="ti ti-link me-1"></i> Save Mapping
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const assetSelect = document.getElementById('asset_id');
        const serialInput = document.getElementById('serial_no');
        const nameInput = document.getElementById('name');
        const statusSelect = document.getElementById('status');
        const remarksInput = document.getElementById('remarks');
        const form = document.getElementById('mapTagForm');
        const alertBox = document.getElementById('mapAlert');
        const submitBtn = document.getElementById('mapSubmitBtn');

        if (!assetSelect || !form) {
            return;
        }

        function showAlert(type, message, errors = null) {
            if (!alertBox) return;
            alertBox.className = `alert alert-${type}`;
            alertBox.innerHTML = '';

            const textNode = document.createElement('div');
            textNode.textContent = message;
            alertBox.appendChild(textNode);

            if (errors && typeof errors === 'object') {
                const list = document.createElement('ul');
                list.className = 'mb-0 mt-1';
                Object.values(errors).flat().forEach(function(msg) {
                    const li = document.createElement('li');
                    li.textContent = msg;
                    list.appendChild(li);
                });
                alertBox.appendChild(list);
            }
        }

        assetSelect.addEventListener('change', function() {
            const opt = assetSelect.options[assetSelect.selectedIndex];
            if (!opt || !opt.value) return;

            if (serialInput) serialInput.value = opt.dataset.serial || '';
            if (nameInput) nameInput.value = opt.dataset.name || '';
            if (statusSelect) statusSelect.value = opt.dataset.status || '';
        });

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (submitBtn) submitBtn.disabled = true;
            if (alertBox) alertBox.className = 'alert d-none';

            const payload = {
                asset_id: assetSelect.value,
                serial_no: serialInput ? serialInput.value.trim() : '',
                name: nameInput ? nameInput.value.trim() : '',
                status: statusSelect && statusSelect.value ? statusSelect.value : null,
                remarks: remarksInput ? remarksInput.value.trim() : ''
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const rawText = await response.text();
                let data = null;

                try {
                    data = JSON.parse(rawText);
                } catch (_) {
                    showAlert('danger', `Server error (${response.status}): unexpected non-JSON response.`);
                    return;
                }

                if (response.ok && data.status === 'success') {
                    showAlert('success', data.message || 'Mapping updated successfully.');

                    const selectedOpt = assetSelect.options[assetSelect.selectedIndex];
                    if (selectedOpt) {
                        selectedOpt.dataset.serial = payload.serial_no;
                        if (payload.name) selectedOpt.dataset.name = payload.name;
                        if (payload.status) selectedOpt.dataset.status = payload.status;
                    }
                } else {
                    showAlert(
                        'danger',
                        data.message || `Request failed (${response.status}).`,
                        data.errors || null
                    );
                }
            } catch (err) {
                console.error('RFID mapping fetch error:', err);
                showAlert('danger', 'Network error while saving mapping. Check console for details.');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    });
</script>
@endpush