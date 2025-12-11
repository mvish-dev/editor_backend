<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Constants\ResponseCode;
use Exception;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        try {
            $user = $request->user();
            
            return response()->json([
                'designs_count' => $user->designs()->count(),
                'orders_count' => $user->orders()->count(),
                'token_balance' => $user->token_balance,
                'status' => ResponseCode::SUCCESS
            ], ResponseCode::SUCCESS);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
