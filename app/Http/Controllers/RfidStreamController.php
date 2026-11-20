<?php

namespace App\Http\Controllers;

use App\Models\RfidTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class RfidStreamController extends Controller
{
    public function index(): View
    {
        $hasAssets = Schema::hasTable('assets');
        $hasScans = Schema::hasTable('rfid_scan_logs');
        $hasAssignments = Schema::hasTable('rfid_assignment_logs');
        $hasDevices = Schema::hasTable('devices');

        $totalAssets = $hasAssets ? DB::table('assets')->count() : 0;
        $availableAssets = $hasAssets ? DB::table('assets')->where('status', 'available')->count() : 0;
        $inUseAssets = $hasAssets ? DB::table('assets')->where('status', 'in_use')->count() : 0;
        $otherAssets = max(0, $totalAssets - $availableAssets - $inUseAssets);

        $mappedAssets = $hasAssets
            ? DB::table('assets')->whereNotNull('serial_no')->where('serial_no', '!=', '')->count()
            : 0;
        $unmappedAssets = max(0, $totalAssets - $mappedAssets);

        $totalScans = $hasScans ? DB::table('rfid_scan_logs')->count() : 0;
        $assetsIn = $hasScans ? DB::table('rfid_scan_logs')->where('status', 'IN')->count() : 0;
        $assetsOut = $hasScans ? DB::table('rfid_scan_logs')->where('status', 'OUT')->count() : 0;

        $totalDevices = $hasDevices ? DB::table('devices')->count() : 0;
        $activeDevices = ($hasDevices && Schema::hasColumn('devices', 'status'))
            ? DB::table('devices')->whereIn(DB::raw('LOWER(status)'), ['active', 'online', '1'])->count()
            : 0;
        $inactiveDevices = max(0, $totalDevices - $activeDevices);

        $mappedDevices = ($hasDevices && Schema::hasColumn('devices', 'location_id'))
            ? DB::table('devices')->whereNotNull('location_id')->count()
            : 0;
        $freeDevices = max(0, $totalDevices - $mappedDevices);

        $devices = $hasDevices
            ? DB::table('devices')
            ->leftJoin('locations', 'devices.location_id', '=', 'locations.id')
            ->leftJoin('areas', 'locations.area_id', '=', 'areas.id')
            ->select([
                'devices.id',
                'devices.id as device_id',
                'devices.device_name as name',
                'devices.status',
                'locations.location_name',
                'locations.location_type',
                'areas.name',
            ])
            ->orderByDesc('devices.id')
            ->limit(10)
            ->get()
            : collect();

        $recentMappings = $hasAssignments
            ? DB::table('rfid_assignment_logs')
            ->leftJoin('assets', 'rfid_assignment_logs.asset_id', '=', 'assets.id')
            ->select([
                'rfid_assignment_logs.*',
                'assets.asset_code',
                'assets.name as asset_name',
            ])
            ->orderByDesc('rfid_assignment_logs.id')
            ->limit(6)
            ->get()
            : collect();

        $transactions = $hasScans
            ? DB::table('rfid_scan_logs')
            ->leftJoin('assets', 'rfid_scan_logs.asset_id', '=', 'assets.id')
            ->leftJoin('locations', 'rfid_scan_logs.location_id', '=', 'locations.id')
            ->select([
                'rfid_scan_logs.id',
                'rfid_scan_logs.epc',
                'rfid_scan_logs.status',
                'rfid_scan_logs.reader_id as device_id',
                'rfid_scan_logs.reader_name',
                'rfid_scan_logs.antenna_no',
                'rfid_scan_logs.read_at',
                'rfid_scan_logs.created_at',
                'assets.asset_code as asset_number',
                'assets.name as asset_name',
                'locations.location_name as location',
            ])
            ->orderByDesc('rfid_scan_logs.id')
            ->limit(10)
            ->get()
            : collect();

        return view('rfid.dashboard', compact(
            'totalAssets',
            'availableAssets',
            'inUseAssets',
            'otherAssets',
            'mappedAssets',
            'unmappedAssets',
            'totalScans',
            'assetsIn',
            'assetsOut',
            'totalDevices',
            'activeDevices',
            'inactiveDevices',
            'mappedDevices',
            'freeDevices',
            'devices',
            'recentMappings',
            'transactions'
        ));
    }
}
