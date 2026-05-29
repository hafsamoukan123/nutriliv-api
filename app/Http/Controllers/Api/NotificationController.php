<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Notification::where('user_id', $request->user()->id)
                        ->latest()->paginate(20)
        );
    }

    public function markAsRead(Request $request, $id)
    {
        Notification::where('id', $id)
                    ->where('user_id', $request->user()->id)
                    ->update(['is_read' => true]);

        return response()->json(['message' => 'Notification lue']);
    }
}