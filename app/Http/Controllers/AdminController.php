<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Design;
use Illuminate\Http\Request;
use App\Constants\ResponseCode;
use Exception;

class AdminController extends Controller
{
    public function users()
    {
        try {
            return User::with('roles')->paginate(10);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function adjustTokens(Request $request, User $user)
    {
        try {
            $request->validate([
                'amount' => 'required|integer',
                'description' => 'required|string'
            ]);

            $amount = $request->amount; // can be negative

            if ($amount > 0) {
                $user->increment('token_balance', $amount);
            } else {
                $user->decrement('token_balance', abs($amount));
            }

            $user->transactions()->create([
                'amount' => $amount,
                'type' => 'admin_adjustment',
                'description' => $request->description . ' (Admin)'
            ]);

            return response()->json([
                'status' => ResponseCode::SUCCESS,
                'message' => 'Tokens adjusted', 
                'new_balance' => $user->token_balance
            ], ResponseCode::SUCCESS);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function pendingTemplates()
    {
        try {
            return Design::where('status', 'pending')
                         ->where('is_template', false) // intended to be template
                         ->with('user')
                         ->get();
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function approveTemplate(Design $design)
    {
        try {
            $design->update([
                'status' => 'approved',
                'is_template' => true
            ]);
            
            return response()->json([
                'status' => ResponseCode::SUCCESS,
                'message' => 'Template approved'
            ], ResponseCode::SUCCESS);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
