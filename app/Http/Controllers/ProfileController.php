<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the user's profile information
     */
    public function index()
    {
        $user = Auth::user();
        $profileData = $this->getProfileData($user);
        
        return view('profile.index', compact('user', 'profileData'));
    }

    /**
     * Show the form for editing profile
     */
    public function edit()
    {
        $user = Auth::user();
        $profileData = $this->getProfileData($user);
        
        return view('profile.edit', compact('user', 'profileData'));
    }

    /**
     * Update the user's profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'no_hp' => 'nullable|string|max:15',
            'alamat' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ];

        // Add role-specific validation
        if ($user->role === 'guru') {
            $rules['nama_lengkap'] = 'required|string|max:255';
        } elseif ($user->role === 'siswa') {
            $rules['nama_lengkap'] = 'required|string|max:255';
        }

        $request->validate($rules);

        try {
            // Update user table
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Store new avatar
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->update(['avatar' => $avatarPath]);
            }

            // Update role-specific data
            $this->updateRoleSpecificData($user, $request);

            return redirect()->route('profile.index')->with('success', 'Profile berhasil diperbarui!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        try {
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return redirect()->route('profile.index')->with('success', 'Password berhasil diubah!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get profile data based on user role
     */
    private function getProfileData($user)
    {
        $profileData = null;

        switch ($user->role) {
            case 'guru':
                $profileData = $user->guru()->first();
                break;
            case 'siswa':
                $profileData = $user->siswa()->with('kelas')->first();
                break;
            case 'admin':
            case 'kepala_sekolah':
                // For admin and kepala_sekolah, we'll just use user data
                $profileData = null;
                break;
        }

        return $profileData;
    }

    /**
     * Update role-specific data
     */
    private function updateRoleSpecificData($user, $request)
    {
        switch ($user->role) {
            case 'guru':
                if ($user->guru) {
                    $user->guru->update([
                        'nama_lengkap' => $request->nama_lengkap,
                        'no_hp' => $request->no_hp,
                        'alamat' => $request->alamat,
                    ]);
                }
                break;

            case 'siswa':
                if ($user->siswa) {
                    $user->siswa->update([
                        'nama_lengkap' => $request->nama_lengkap,
                        'no_hp' => $request->no_hp,
                        'alamat' => $request->alamat,
                    ]);
                }
                break;
        }
    }
}