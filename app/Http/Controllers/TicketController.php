<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Constants\ResponseCode;
use Exception;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            return $request->user()->tickets()->latest()->paginate(10);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => ResponseCode::VALIDATION_ERROR,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], ResponseCode::VALIDATION_ERROR);
            }

            $ticket = $request->user()->tickets()->create([
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'open'
            ]);

            return response()->json([
                'status' => ResponseCode::SUCCESS,
                'message' => 'Ticket created successfully',
                'data' => $ticket
            ], ResponseCode::SUCCESS);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
