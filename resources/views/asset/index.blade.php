@extends('tablar::page')

@section('title', 'Asset List')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Asset List</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="{{ url('asset/map') }}" class="btn btn-outline-primary">
                        <i class="ti ti-link me-1"></i> Asset Mapping
                    </a>
                    <a href="{{ url('asset/create') }}" class="btn btn-primary">
                        <i class="ti ti-square-plus me-1"></i> Register Asset
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="row row-cards mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="text-secondary">Total Assets</div>
                        <div class="h2 mb-0">{{ $stats['total'] ?? ($assets->total() ?? count($assets)) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="text-secondary">Available</div>
                        <div class="h2 mb-0 text-success">{{ $stats['available_count'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="text-secondary">In Use</div>
                        <div class="h2 mb-0 text-primary">{{ $stats['in_use_count'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="text-secondary">Maintenance / Other</div>
                        <div class="h2 mb-0 text-warning">{{ $stats['other_count'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ url('asset') }}" class="row g-2 w-100 align-items-center">
                    <div class="col-md-3">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search serial_no, asset_code, name...">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="category" value="{{ request('category') }}" class="form-control" placeholder="Category...">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="in_use" {{ request('status') === 'in_use' ? 'selected' : '' }}>In Use</option>
                            <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="retired" {{ request('status') === 'retired' ? 'selected' : '' }}>Retired</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="location_id" class="form-select">
                            <option value="">All Locations</option>
                            @foreach(($locations ?? collect()) as $location)
                            <option value="{{ $location->id }}" {{ (string) request('location_id') === (string) $location->id ? 'selected' : '' }}>
                                {{ $location->location_name ?? ('Location #' . $location->id) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                        <a href="{{ url('asset') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Serial No</th>
                            <th>Asset Code</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Last Seen At</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th class="text-end">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                        <tr>
                            <td>{{ $asset->id }}</td>
                            <td class="font-monospace">{{ $asset->serial_no }}</td>
                            <td>{{ $asset->asset_code }}</td>
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->location_name ?? '-' }}</td>
                            <td>{{ $asset->category ?? '-' }}</td>
                            <td>
                                @switch($asset->status)
                                @case('available')
                                <span class="badge bg-success-lt text-success">available</span>
                                @break
                                @case('in_use')
                                <span class="badge bg-primary-lt text-primary">in_use</span>
                                @break
                                @case('maintenance')
                                <span class="badge bg-warning-lt text-warning">maintenance</span>
                                @break
                                @default
                                <span class="badge bg-secondary-lt text-secondary">{{ $asset->status }}</span>
                                @endswitch
                            </td>
                            <td>{{ $asset->last_seen_at ?? '-' }}</td>
                            <td>{{ $asset->created_at ?? '-' }}</td>
                            <td>{{ $asset->updated_at ?? '-' }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('asset.map', ['asset_id' => $asset->id, 'serial_no' => $asset->serial_no]) }}"
                                    class="btn btn-sm {{ $asset->serial_no ? 'btn-outline-primary' : 'btn-primary' }}">
                                    {{ $asset->serial_no ? 'Remap Tag' : 'Assign Tag' }}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-secondary py-4">No assets found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($assets, 'links'))
            <div class="card-footer d-flex align-items-center">
                {{ $assets->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection