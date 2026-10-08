<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use Illuminate\Http\Request;

class RiderController extends Controller
{
    /**
     * Display a listing of pending riders.
     */
    public function index()
    {
        $pendingRiders = Rider::where('is_approved', false)
            ->where('status', '!=', 'rejected') // Assuming we might mark them as rejected
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.riders.pending', compact('pendingRiders'));
    }

    /**
     * Approve the specified rider.
     */
    public function approve($id)
    {
        $rider = Rider::findOrFail($id);
        $rider->is_approved = true;
        // Optionally set status to 'offline' or 'active' if needed, but 'offline' is default
        $rider->save();

        return redirect()->back()->with('success', 'Rider approved successfully.');
    }

    /**
     * Reject the specified rider.
     */
    public function reject($id)
    {
        $rider = Rider::findOrFail($id);
        // We can either delete them or mark as rejected. 
        // For now, let's delete them to allow re-registration or maybe just delete.
        // User request didn't specify, but "Reject" usually implies denied. 
        // Let's delete for simplicity as they can re-register with correct docs.

        // Alternatively, add a 'rejected' status column if we want to keep record.
        // Given existing columns, let's just delete the record for now or maybe duplicate checking will fail if we keep it.
        // Let's delete it.
        $rider->delete();

        return redirect()->back()->with('success', 'Rider request rejected and removed.');
    }
}
