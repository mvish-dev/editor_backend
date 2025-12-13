<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Design;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TokenPackage;
use Illuminate\Http\Request;
use App\Constants\ResponseCode;
use Exception;

class AdminController extends Controller
{
    public function overview()
    {
        try {
            return response()->json([
                'stats' => [
                    'users_count' => User::count(),
                    'orders_count' => Order::count(),
                    'tickets_count' => Ticket::where('status', 'open')->count(),
                    'revenue' => Order::sum('amount')
                ],
                'recent_orders' => Order::with('user:id,name,email')->latest()->take(5)->get(),
                'recent_tickets' => Ticket::with('user:id,name,email')->latest()->take(5)->get(),
                'status' => ResponseCode::SUCCESS
            ], ResponseCode::SUCCESS);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function users()
    {
        try {
            return User::with('roles')->latest()->paginate(10);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function storeUser(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'username' => 'required|string|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
                'role' => 'required|string',
                'status' => 'required|string' // Active/Inactive
            ]);

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone, // Optional
                'password' => bcrypt($request->password),
                'token_balance' => $request->token_balance ?? 0,
                'is_active' => $request->status === 'Active'
            ]);

            $user->assignRole($request->role); // Spatie

            return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function updateUser(Request $request, User $user)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'username' => 'required|string|unique:users,username,' . $user->id,
                'email' => 'required|email|unique:users,email,' . $user->id,
                'role' => 'required|string',
                'status' => 'required|string'
            ]);

            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'token_balance' => $request->token_balance,
                'is_active' => $request->status === 'Active'
            ]);

            if ($request->filled('password')) {
                $user->update(['password' => bcrypt($request->password)]);
            }

            $user->syncRoles([$request->role]);

            return response()->json(['message' => 'User updated successfully', 'user' => $user]);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyUser(User $user)
    {
        try {
            $user->delete();
            return response()->json(['message' => 'User deleted successfully']);
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

    public function reports(Request $request)
    {
        try {
            $query = Order::with('user');

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
            }

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $orders = $query->latest()->paginate(20);

            return response()->json($orders);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function tickets(Request $request)
    {
        try {
            $query = Ticket::with('user');
            
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $tickets = $query->latest()->paginate(10);

            return response()->json($tickets);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function updateTicketStatus(Request $request, Ticket $ticket)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:open,closed,in_progress'
            ]);

            $ticket->update(['status' => $request->status]);

            return response()->json([
                'status' => ResponseCode::SUCCESS,
                'message' => 'Ticket status updated',
                'ticket' => $ticket
            ], ResponseCode::SUCCESS);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
