<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ✅ تسجيل مستخدم جديد
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed', // confirmed = password_confirmation
            'phone'    => 'required|string',
            'role'     => 'required|in:client,vendeur,livreur',
            // لا يمكن التسجيل كـ admin من الـ API
            'shop_name'    => 'required_if:role,vendeur',
            'vehicle_type' => 'required_if:role,livreur',
        ]);

        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'phone'        => $request->phone,
            'role'         => $request->role,
            'address'      => $request->address,
            'shop_name'    => $request->shop_name,
            'vehicle_type' => $request->vehicle_type,
        ]);

        // ✅ نفتح سلة تلقائياً لكل client جديد
        if ($user->isClient()) {
            Cart::create(['client_id' => $user->id]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    // ✅ تسجيل الدخول
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email ou mot de passe incorrect'],
            ]);
        }

        // ✅ نحذف الـ tokens القديمة ونصنع واحداً جديداً
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    // ✅ تسجيل الخروج
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès']);
    }

    // ✅ معلومات المستخدم الحالي
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // ✅ تحديث الملف الشخصي
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'    => 'sometimes|string|max:255',
            'phone'   => 'sometimes|string',
            'address' => 'sometimes|string',
        ]);

        $user->update($request->only(['name', 'phone', 'address']));

        return response()->json($user);
    }
}