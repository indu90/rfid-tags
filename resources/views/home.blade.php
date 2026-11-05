@extends('tablar::page')

@section('title', 'Asset Management System - Dashboard')

@section('content')
<!-- Page Header -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title text-uppercase">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-dashboard me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M12 13m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                        <path d="M13.45 11.55l2.05 -2.05" />
                        <path d="M6.4 20a9 9 0 1 1 11.2 0z" />
                    </svg>
                    Dashboard
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        <!-- Summary Metric Cards -->
        <div class="row row-cards mb-4">
            <!-- Total Assets -->
            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="avatar avatar-lg bg-green-lt">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M7.8 20.344l-4.144 -4.144a1.2 1.2 0 0 1 0 -1.697l10.485 -10.485a1.2 1.2 0 0 1 1.697 0l4.144 4.144a1.2 1.2 0 0 1 0 1.697l-10.485 10.485a1.2 1.2 0 0 1 -1.697 0z" />
                                        <path d="M10.5 7.5l4 4" />
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="text-success fw-bold">Total Assets</div>
                                <div class="h1 mb-0 me-2 text-success">{{ $totalAssets ?? 0 }}</div>
                                <div class="text-muted fs-5">
                                    <span class="text-success">IN : {{ $assetsIn ?? 0 }}</span>,
                                    <span class="text-danger">OUT : {{ $assetsOut ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Devices -->
            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="avatar avatar-lg bg-warning-lt">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M3 12m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                                        <path d="M9 8m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                                        <path d="M15 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="text-warning fw-bold">Total Devices</div>
                                <div class="h1 mb-0 me-2 text-warning">{{ $totalDevices ?? 0 }}</div>
                                <div class="text-muted fs-5">
                                    <span class="text-warning">ACTIVE : {{ $activeDevices ?? 0 }}</span>,
                                    <span class="text-danger">INACTIVE : {{ $inactiveDevices ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mapped Devices -->
            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="avatar avatar-lg bg-info-lt">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M5 12l5 5l10 -10" />
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="text-info fw-bold">Mapped Devices</div>
                                <div class="h1 mb-0 me-2 text-info">{{ $mappedDevices ?? 0 }}</div>
                                <div class="text-muted fs-5">
                                    <span class="text-info">FREE DEVICES : {{ $freeDevices ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Tags -->
            <div class="col-sm-6 col-lg-4 mt-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="avatar avatar-lg bg-orange-lt">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M7.8 20.344l-4.144 -4.144a1.2 1.2 0 0 1 0 -1.697l10.485 -10.485a1.2 1.2 0 0 1 1.697 0l4.144 4.144a1.2 1.2 0 0 1 0 1.697l-10.485 10.485a1.2 1.2 0 0 1 -1.697 0z" />
                                    </svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="text-orange fw-bold">Total Tags</div>
                                <div class="h1 mb-0 text-orange">{{ $totalTags ?? 1 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1. Device Status Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Device Status</h3>
            </div>
            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap">
                    <thead>
                        <tr>
                            <th>Serial No.</th>
                            <th>Device Name (Device ID)</th>
                            <th>Location Name</th>
                            <th>Location type</th>
                            <th>Area Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices ?? [] as $index => $device)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $device->name ?? 'Reader' }} ({{ $device->device_id }})</td>
                            <td>{{ $device->location_name ?? 'Main Warehouse' }}</td>
                            <td>{{ $device->location_type ?? 'In' }}</td>
                            <td>{{ $device->area_name ?? 'Zone A' }}</td>
                            <td><span class="badge bg-success">{{ $device->status ?? 'Active' }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No device data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Asset IN/OUT Count Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Asset IN/OUT Count</h3>
            </div>
            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap">
                    <thead>
                        <tr>
                            <th>Serial No.</th>
                            <th>Location Type</th>
                            <th>Count</th>
                            <th>Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>In</td>
                            <td>{{ $assetsIn ?? 0 }}</td>
                            <td><a href="#" class="btn btn-sm btn-success">Open</a></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Out</td>
                            <td>{{ $assetsOut ?? 0 }}</td>
                            <td><a href="#" class="btn btn-sm btn-success">Open</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. IN Asset Transaction Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">IN Asset Transaction</h3>
            </div>
            <div class="table-responsive">
                <table class="table card-table table-vcenter text-nowrap">
                    <thead>
                        <tr>
                            <th>Serial No.</th>
                            <th>Transaction Date</th>
                            <th>Asset Number</th>
                            <th>Asset Tag Number</th>
                            <th>Device ID</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody id="rfid-stream-body">
                        @forelse($transactions ?? [] as $index => $txn)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $txn->created_at }}</td>
                            <td>{{ $txn->asset_number }}</td>
                            <td><code>{{ $txn->epc }}</code></td>
                            <td>{{ $txn->device_id }}</td>
                            <td>{{ $txn->location }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No transaction record available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection