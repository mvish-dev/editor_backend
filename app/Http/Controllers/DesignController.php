<?php

namespace App\Http\Controllers;

use App\Models\Design;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Constants\ResponseCode;
use Exception;

class DesignController extends Controller
{
    public function index(Request $request)
    {
        try {
            return $request->user()->designs()->latest()->get();
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'canvas_data' => 'required|array',
            ]);

            $design = $request->user()->designs()->create([
                'name' => $request->name,
                'canvas_data' => $request->canvas_data,
                'status' => 'draft',
            ]);

            return response()->json($design, ResponseCode::CREATED);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(Design $design)
    {
        try {
            if ($design->user_id !== Auth::id()) {
                abort(ResponseCode::FORBIDDEN, ResponseCode::MSG_FORBIDDEN);
            }
            return $design;
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, Design $design)
    {
        try {
            if ($design->user_id !== Auth::id()) {
                abort(ResponseCode::FORBIDDEN, ResponseCode::MSG_FORBIDDEN);
            }

            $request->validate([
                'name' => 'string',
                'canvas_data' => 'array',
            ]);

            $design->update($request->only('name', 'canvas_data'));

            return response()->json($design, ResponseCode::SUCCESS);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy(Design $design)
    {
        try {
            if ($design->user_id !== Auth::id()) {
                abort(ResponseCode::FORBIDDEN, ResponseCode::MSG_FORBIDDEN);
            }

            $design->delete();
            return response()->json(['message' => 'Design deleted'], ResponseCode::SUCCESS);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function generateImage(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->token_balance < 1) {
                return response()->json([
                    'status' => ResponseCode::BAD_REQUEST, // Or Payment Required if strictly appropriate
                    'message' => 'Insufficient tokens'
                ], ResponseCode::BAD_REQUEST);
            }

            // Mock Image Generation (In real app, call AI service)
            $imageUrl = 'https://via.placeholder.com/600x400.png?text=Generated+Design';

            // Deduct Token
            $user->decrement('token_balance', 1);
            
            // Record Transaction
            $user->transactions()->create([
                'amount' => -1,
                'type' => 'usage',
                'description' => 'Image Generation'
            ]);

            return response()->json(['image_url' => $imageUrl, 'remaining_tokens' => $user->token_balance], ResponseCode::SUCCESS);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
