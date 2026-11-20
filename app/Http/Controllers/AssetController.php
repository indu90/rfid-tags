<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        // Subquery to fetch the latest scan per EPC from rfid_tags
        $latestScans = DB::table('rfid_tags as l1')
            ->select('l1.epc', 'l1.reader_id', 'l1.antenna_no', 'l1.rssi', 'l1.read_at')
            ->whereRaw('l1.id = (SELECT MAX(l2.id) FROM rfid_tags l2 WHERE l2.epc = l1.epc)');

        $query = Asset::query()
            ->leftJoinSub($latestScans, 'latest_scan', function ($join) {
                $join->on('assets.serial_no', '=', 'latest_scan.epc');
            })
            ->select(
                'assets.*',
                'latest_scan.reader_id as last_reader',
                'latest_scan.antenna_no as last_antenna',
                'latest_scan.rssi as last_rssi',
                'latest_scan.read_at as last_seen_at'
            );

        // Search filter (Code, Name, or Serial / EPC)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('assets.asset_code', 'like', "%{$search}%")
                    ->orWhere('assets.name', 'like', "%{$search}%")
                    ->orWhere('assets.serial_no', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('assets.status', $request->status);
        }

        $assets = $query->orderByDesc('assets.id')->paginate(15)->withQueryString();

        // Summary counts for top KPI cards
        $stats = [
            'total'  => Asset::count(),
            'mapped' => Asset::whereNotNull('serial_no')->where('serial_no', '!=', '')->count(),
            'IN'     => Asset::where('status', 'IN')->count(),
            'OUT'    => Asset::where('status', 'OUT')->count(),
        ];

        return view('asset.index', compact('assets', 'stats'));
    }
}
