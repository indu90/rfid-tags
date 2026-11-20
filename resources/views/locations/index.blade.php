@extends('tablar::page')

@section('title', 'RFID TAGES | View All Location')

@section('content')
<div class="page-body mt-3">
    <div class="container-xl">

        <!-- Welcome / Flash Banner -->
        <div class="alert alert-success border-0 mb-3 py-2 px-3" style="background-color: #d4edda; color: #155724; font-size: 14px;">
            {{ session('success') ?? 'Welcome Admin User To Site Manager' }}
        </div>

        <div class="card shadow-sm border-0">
            <!-- Card Header -->
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-grid-dots me-2 text-dark" width="24" height="24" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" fill="none">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <circle cx="5" cy="5" r="1" fill="currentColor"/>
                        <circle cx="12" cy="5" r="1" fill="currentColor"/>
                        <circle cx="19" cy="5" r="1" fill="currentColor"/>
                        <circle cx="5" cy="12" r="1" fill="currentColor"/>
                        <circle cx="12" cy="12" r="1" fill="currentColor"/>
                        <circle cx="19" cy="12" r="1" fill="currentColor"/>
                        <circle cx="5" cy="19" r="1" fill="currentColor"/>
                        <circle cx="12" cy="19" r="1" fill="currentColor"/>
                        <circle cx="19" cy="19" r="1" fill="currentColor"/>
                    </svg>
                    <h3 class="card-title fw-bold text-dark mb-0 fs-3">VIEW ALL LOCATION</h3>
                </div>
                <div>
                    <a href="{{ route('locations.create') }}" class="btn btn-warning text-white fw-bold px-4">
                        + Add Location
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Toolbar: Excel, PDF on Left | Search on Right -->
                <div class="row align-items-center mb-3 g-2">
                    <div class="col-sm-6 d-flex align-items-center">
                        <button type="button" id="btnExportExcel" class="btn btn-outline-secondary btn-sm px-3 me-2 bg-light text-dark border">
                            Excel
                        </button>
                        <button type="button" id="btnExportPdf" class="btn btn-outline-secondary btn-sm px-3 bg-light text-dark border">
                            PDF
                        </button>
                    </div>
                    <div class="col-sm-6 d-flex justify-content-sm-end align-items-center">
                        <label for="tableSearchInput" class="form-label mb-0 me-2 text-muted fw-normal">Search:</label>
                        <input type="search" id="tableSearchInput" class="form-control form-control-sm w-auto" placeholder="" style="min-width: 180px;">
                    </div>
                </div>

                <!-- Locations Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-vcenter text-nowrap align-middle" id="locationsTable">
                        <thead>
                            <tr class="table-light text-dark border-bottom">
                                <th class="text-nowrap" style="cursor: pointer;" onclick="sortTable(0)">
                                    Serial No. <span class="sort-icon float-end text-muted small"></span>
                                </th>
                                <th class="text-nowrap" style="cursor: pointer;" onclick="sortTable(1)">
                                    Area Name <span class="sort-icon float-end text-muted small"></span>
                                </th>
                                <th class="text-nowrap" style="cursor: pointer;" onclick="sortTable(2)">
                                    Location Name <span class="sort-icon float-end text-muted small"></span>
                                </th>
                                <th class="text-nowrap" style="cursor: pointer;" onclick="sortTable(3)">
                                    Location Type <span class="sort-icon float-end text-muted small"></span>
                                </th>
                                <th class="text-nowrap" style="cursor: pointer;" onclick="sortTable(4)">
                                    Status <span class="sort-icon float-end text-muted small"></span>
                                </th>
                                <th class="text-nowrap" style="cursor: pointer;" onclick="sortTable(5)">
                                    Created Date <span class="sort-icon float-end text-muted small"></span>
                                </th>
                                <th class="text-nowrap text-center">
                                    Action <span class="sort-icon float-end text-muted small"></span>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="locationsTableBody">
                            @forelse($locations as $index => $location)
                            <tr>
                                <td>{{ $locations->firstItem() ? ($locations->firstItem() + $index) : ($index + 1) }}</td>
                                <td>{{ $location->area->name ?? 'N/A' }}</td>
                                <td>{{ $location->location_name }}</td>
                                <td>{{ $location->location_type }}</td>
                                <td>{{ $location->status ?? 'Active' }}</td>
                                <td>{{ $location->created_at ? $location->created_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-success px-3 me-1" data-bs-toggle="modal" data-bs-target="#viewModal{{ $location->id }}">
                                        View
                                    </button>
                                    <a href="{{ route('locations.edit', $location->id) }}" class="btn btn-sm btn-info px-3 text-white">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr id="noDataRow">
                                <td colspan="7" class="text-center text-muted py-4">
                                    No data available in table
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer: Showing X to Y entries and Pagination -->
                <div class="row align-items-center mt-3 g-2">
                    <div class="col-sm-6 text-muted">
                        Showing {{ $locations->firstItem() ?? 0 }} to {{ $locations->lastItem() ?? 0 }} of {{ $locations->total() }} entries
                    </div>
                    <div class="col-sm-6 d-flex justify-content-sm-end">
                        {{ $locations->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- Modals for View --}}
@foreach($locations as $location)
<div class="modal fade" id="viewModal{{ $location->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Location Details - {{ $location->location_name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-borderless">
                    <tr>
                        <th class="text-muted w-50">Location ID:</th>
                        <td>{{ $location->id }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Associated Area:</th>
                        <td class="fw-bold">{{ $location->area->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Location Name:</th>
                        <td class="fw-bold">{{ $location->location_name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Location Type:</th>
                        <td><span class="badge bg-blue-lt">{{ $location->location_type }}</span></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Status:</th>
                        <td>
                            <span class="badge {{ $location->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $location->status ?? 'Active' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Created Date:</th>
                        <td>{{ $location->created_at ? $location->created_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Last Updated:</th>
                        <td>{{ $location->updated_at ? $location->updated_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="{{ route('locations.edit', $location->id) }}" class="btn btn-info text-white">Edit Location</a>
            </div>
        </div>
    </div>
</div>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('tableSearchInput');
    const tableBody = document.getElementById('locationsTableBody');
    const rows = tableBody ? tableBody.querySelectorAll('tr') : [];

    // Real-time table search filtering
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const query = this.value.toLowerCase().trim();
            rows.forEach(row => {
                if (row.id === 'noDataRow') return;
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // Export to Excel / CSV
    const btnExcel = document.getElementById('btnExportExcel');
    if (btnExcel) {
        btnExcel.addEventListener('click', function () {
            let csv = [];
            const table = document.getElementById('locationsTable');
            const tableRows = table.querySelectorAll('tr');

            for (let i = 0; i < tableRows.length; i++) {
                if (tableRows[i].style.display === 'none' || tableRows[i].id === 'noDataRow') continue;
                let row = [], cols = tableRows[i].querySelectorAll('td, th');
                // Export first 6 columns (excluding Action column)
                for (let j = 0; j < Math.min(cols.length, 6); j++) {
                    let cleanText = cols[j].innerText.replace(/â–²â–¼/g, '').trim().replace(/"/g, '""');
                    row.push('"' + cleanText + '"');
                }
                csv.push(row.join(','));
            }

            const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
            const downloadLink = document.createElement('a');
            downloadLink.download = 'All_Locations.csv';
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        });
    }

    // Export / Print PDF
    const btnPdf = document.getElementById('btnExportPdf');
    if (btnPdf) {
        btnPdf.addEventListener('click', function () {
            window.print();
        });
    }
});

// Column sorting helper
let sortDirection = {};
function sortTable(columnIndex) {
    const table = document.getElementById('locationsTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.id !== 'noDataRow');

    const isAscending = !sortDirection[columnIndex];
    sortDirection[columnIndex] = isAscending;

    rows.sort((a, b) => {
        const aText = a.children[columnIndex] ? a.children[columnIndex].innerText.trim() : '';
        const bText = b.children[columnIndex] ? b.children[columnIndex].innerText.trim() : '';

        const aNum = parseFloat(aText);
        const bNum = parseFloat(bText);

        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? aNum - bNum : bNum - aNum;
        }
        return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
    });

    rows.forEach(row => tbody.appendChild(row));
}
</script>
@endsection
