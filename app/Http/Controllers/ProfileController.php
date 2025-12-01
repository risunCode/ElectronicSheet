<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|string',
        ]);

        $user = auth()->user();
        
        // Decode base64 image
        $imageData = $request->photo;
        
        // Extract the base64 encoded part
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            $extension = $matches[1];
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $imageData = base64_decode($imageData);
            
            // Generate unique filename
            $filename = 'profile-photos/' . $user->id . '-' . Str::random(10) . '.' . $extension;
            
            // Delete old photo if exists
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            
            // Save new photo
            Storage::disk('public')->put($filename, $imageData);
            
            // Update user
            $user->update(['profile_photo' => $filename]);
            
            return back()->with('success', 'Foto profil berhasil diperbarui.');
        }
        
        return back()->with('error', 'Format gambar tidak valid.');
    }
}
