<?php

namespace App\Http\Controllers;

use App\Models\RfidTag;
use Illuminate\Http\Request;

class RfidStreamController extends Controller
{
    public function index(Request $request)
    {
        // Example 1: Fetching individual live scan logs
        $tags = RfidTag::latest('id')->take(25)->get();

        /* 
        // Example 2: If you want to group by reader location (Summary Report style):
        $tags = RfidTag::select('reader_id', \DB::raw('count(*) as total_scans'))
            ->groupBy('reader_id')
            ->get();
        */

        return view('rfid.dashboard', compact('tags'));
    }
}
