@extends('tablar::page')

@section('title', 'Asset Categories & Locations')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Asset Categories & Locations</h2>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ url('asset') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-packages me-1"></i> Asset List
                </a>
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

        <div class="row row-cards">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Add Location / Group</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ url('asset/categories') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label required">Area</label>
                                <select name="area_id" class="form-select @error('area_id') is-invalid @enderror" required>
                                    <option value="">Select Area...</option>
                                    @foreach(($areas ?? collect()) as $area)
                                    <option value="{{ $area->id }}" {{ (string) old('area_id') === (string) $area->id ? 'selected' : '' }}>
                                        {{ $area->name ?? $area->area_name ?? ('Area #' . $area->id) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('area_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Location Name</label>
                                <input type="text" name="location_name" value="{{ old('location_name') }}" class="form-control @error('location_name') is-invalid @enderror" placeholder="e.g., Gate 1" required>
                                @error('location_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark" for="location_type">
                                    Location Type <span class="text-danger">*</span>
                                </label>
                                <select name="location_type" id="location_type" class="form-select @error('location_type') is-invalid @enderror">
                                    <option value="">Select Location Type</option>
                                    @foreach($locationTypes as $type)
                                    <option value="{{ $type }}" {{ old('location_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('location_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-plus me-1"></i> Save
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Configured Locations</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($locations ?? collect()) as $location)
                                <tr>
                                    <td>{{ $location->id }}</td>
                                    <td>{{ $location->location_name ?? ('Location #' . $location->id) }}</td>
                                    <td>{{ $location->location_type ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ url('asset?location_id=' . $location->id) }}" class="btn btn-sm btn-outline-primary">
                                            View Assets
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-secondary py-4">No locations found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row row-cards mt-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Add Asset Category</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ url('asset/categories/category') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label required">Category Name</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="e.g., Laptops, Monitors, Tools" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" value="{{ old('description') }}"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Optional notes">
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-plus me-1"></i> Save Category
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Configured Categories</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($categories ?? collect()) as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->description ?? '-' }}</td>
                            <td>
                                <a href="{{ url('asset?category=' . urlencode($category->name)) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    View Assets
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">No categories added yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection