<?php

namespace App\Http\Controllers;

use App\Models\ReferralCode;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    public function index()
    {
        $referralCodes = ReferralCode::with(['creator', 'assignedRole', 'usages'])
            ->latest()
            ->paginate(20);
        $roles = Role::all();

        return view('referrals.index', compact('referralCodes', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'assigned_role_id' => 'required|exists:roles,id',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
            'note' => 'nullable|string|max:255',
        ]);

        $code = strtoupper(Str::random(8));
        
        ReferralCode::create([
            'code' => $code,
            'created_by' => auth()->id(),
            'assigned_role_id' => $validated['assigned_role_id'],
            'max_uses' => $validated['max_uses'],
            'expires_at' => $validated['expires_at'],
            'note' => $validated['note'],
            'is_active' => true,
        ]);

        return back()->with('success', "Referral code {$code} berhasil dibuat.");
    }

    public function destroy(ReferralCode $referral)
    {
        $referral->delete();
        return back()->with('success', 'Referral code berhasil dihapus.');
    }
}
