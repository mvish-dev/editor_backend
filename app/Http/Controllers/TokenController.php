<?php

namespace App\Http\Controllers;

use App\Models\TokenPackage;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Constants\ResponseCode;
use Exception;

class TokenController extends Controller
{
    public function packages()
    {
        try {
            return TokenPackage::all();
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function purchase(Request $request)
    {
        try {
            $request->validate([
                'package_id' => 'required|exists:token_packages,id',
            ]);

            $package = TokenPackage::find($request->package_id);
            $user = $request->user();

            // Create Order
            $order = Order::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'tokens_amount' => $package->tokens,
                'amount' => $package->price,
                'status' => 'pending', 
            ]);

            // SIMULATE SUCCESSFUL PAYMENT for now
            $order->update(['status' => 'completed', 'payment_id' => 'pay_mock_' . rand(1000,9999)]);
            
            $user->increment('token_balance', $package->tokens);
            
            $user->transactions()->create([
                'amount' => $package->tokens,
                'type' => 'purchase',
                'description' => "Purchased " . $package->name
            ]);

            return response()->json([
                'status' => ResponseCode::SUCCESS,
                'message' => 'Purchase successful',
                'new_balance' => $user->token_balance
            ], ResponseCode::SUCCESS);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function history(Request $request)
    {
        try {
            return $request->user()->transactions()->latest()->get();
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
