<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Helpers\JWT;
use App\Models\User;
use App\Models\Token;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Access\AuthorizationException;

class LoginController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('api', ['except' => ['login']]);
    }

    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // $user = User::where('email', $request->input('email'))->first();

        // if (! $user) {
        //     throw new AuthorizationException("Incorrect email or password!");
        // }

        // if ($user && ! Hash::check($request->input('password'), $user->password)) {
        //     throw new AuthorizationException("Incorrect email or password!");
        // }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    public function user(Request $request)
    {
        $user = $request->user();

        $user->load('meta');

        Cache::remember("users.$user->id", config('cache.ttl.jwt'), function () use ($user, $request) {
            $user->load('meta');

            $u = new UserResource($user);

            return $u->toArray($request);
        });

        return new UserResource($user);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('auth.verified_token_expire_in'),
            'user' => auth()->user(),
            'roles' => auth()->user()->roles()->pluck('name'),
        ];

        return response()->json($data);
    }
}
