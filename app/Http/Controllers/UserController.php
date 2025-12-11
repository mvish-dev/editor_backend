<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Constants\ResponseCode;
use Exception;

class UserController extends Controller
{
    /**
     * Update user profile details (name, email, phone, etc.)
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username,' . $user->id,
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
                'profile_picture' => 'nullable|image|max:5120', // 5MB max
            ]);

            // Handle Profile Picture Upload
            if ($request->hasFile('profile_picture')) {
                // Delete old image if exists
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                $path = $request->file('profile_picture')->store('profile_pictures', 'public');
                $user->profile_picture = $path; // Store relative path
            }

            // If user explicitly sent 'null' or empty string for profile_picture (removal)
            // Note: FormData sends string 'null' sometimes, need to handle carefully or use a separate flag.
            // For now, let's assume separate endpoint for removal or handle via a flag if needed.
            // But here, we just update fields.

            $user->name = $validatedData['name'];
            $user->username = $validatedData['username'];
            $user->email = $validatedData['email'];
            $user->phone = $validatedData['phone'];
            
            $user->save();

            return response()->json([
                'status' => ResponseCode::SUCCESS,
                'message' => 'Profile updated successfully.',
                'user' => $user
            ], ResponseCode::SUCCESS);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Remove profile picture
     */
    public function removeProfilePicture(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
                $user->profile_picture = null;
                $user->save();
            }

            return response()->json([
                'status' => ResponseCode::SUCCESS,
                'message' => 'Profile picture removed.',
                'user' => $user
            ], ResponseCode::SUCCESS);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed', // expects new_password_confirmation
            ]);

            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages(['current_password' => 'Current password is incorrect.']);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'status' => ResponseCode::SUCCESS,
                'message' => 'Password updated successfully.'
            ], ResponseCode::SUCCESS);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Delete account
     */
    public function deleteAccount(Request $request)
    {
        try {
            $user = $request->user();
            
            // Optional: require password to delete
            // For now, just delete.
            
            // Revoke tokens
            $user->tokens()->delete();
            
            // Delete user
            $user->delete();

            return response()->json([
                'status' => ResponseCode::SUCCESS,
                'message' => 'Account deleted successfully.'
            ], ResponseCode::SUCCESS);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
