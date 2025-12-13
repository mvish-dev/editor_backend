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

    public function balance(Request $request)
    {
        try {
            return response()->json([
                'balance' => $request->user()->token_balance
            ]);
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

    public function orders(Request $request)
    {
        try {
            return $request->user()->orders()->with('package')->latest()->paginate(10);
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

    public function spend(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|integer|min:1',
                'description' => 'nullable|string'
            ]);

            $user = $request->user();
            $amount = $request->amount;

            if ($user->token_balance < $amount) {
                return response()->json([
                    'status' => ResponseCode::ERROR,
                    'message' => 'Insufficient tokens'
                ], 400);
            }

            $user->decrement('token_balance', $amount);

            $user->transactions()->create([
                'amount' => $amount, // store as positive, type indicates direction usually, but let's check purchase.
                                     // Purchase stored positive. If I store positive here, I need to rely on type.
                                     // Let's stick to positive amount and 'usage'/'spend' type.
                'type' => 'usage',   // or 'spend'
                'description' => $request->description ?? "Used tokens"
            ]);

            return response()->json([
                'status' => ResponseCode::SUCCESS,
                'message' => 'Tokens deducted successfully',
                'new_balance' => $user->token_balance
            ], ResponseCode::SUCCESS);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
