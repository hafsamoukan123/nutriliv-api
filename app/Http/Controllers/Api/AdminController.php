<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users()
    {
        return response()->json(User::latest()->paginate(20));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'email', 'role']));
        return response()->json($user);
    }

    public function orders()
    {
        return response()->json(
            Order::with(['client:id,name', 'vendeur:id,name', 'livreur:id,name'])
                 ->latest()->paginate(20)
        );
    }

    public function transactions()
    {
        return response()->json(
            Transaction::with('vendeur:id,name,bank_account')
                       ->where('type', 'transfer')
                       ->where('status', 'pending')
                       ->latest()->paginate(20)
        );
    }

    public function processTransfer(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:completed,rejected']);

        $transaction = Transaction::findOrFail($id);
        $transaction->update(['status' => $request->status]);

        // ✅ إذا قبل الأدمين — نخصم من رصيد البائع
        if ($request->status === 'completed') {
            $transaction->vendeur->decrement('balance', $transaction->amount);
        }

        return response()->json(['message' => 'Transaction traitée']);
    }

    public function analytics()
    {
        return response()->json([
            'total_users'    => User::count(),
            'total_orders'   => Order::count(),
            'total_revenue'  => Order::where('status', 'delivered')->sum('total_amount'),
            'total_commission' => Order::where('status', 'delivered')->sum('commission'),
            'orders_by_status' => Order::selectRaw('status, count(*) as count')
                                       ->groupBy('status')->get(),
        ]);
    }
}