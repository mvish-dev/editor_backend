<?php

namespace App\Http\Controllers;

use App\Models\ModuleGroup;
use Illuminate\Http\Request;
use App\Constants\ResponseCode;
use Exception;

class ModuleController extends Controller
{
    public function index()
    {
        try {
            $groups = ModuleGroup::with(['modules' => function ($query) {
                $query->where('status', 'active');
            }])->where('status', 'active')->get();
            
            return response()->json($groups);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
