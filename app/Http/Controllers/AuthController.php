<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log as LogFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Config;

class AuthController extends Controller
{


    public function login(Request $request)
    {
        LogFacade::info('JWT_SECRET from config: ' . Config::get('jwt.secret'));
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $user = \App\Models\User::find($user->id);
        $user->load('clubs');
        LogFacade::info('AuthController: User logged in with clubs', ['user_id' => $user->id, 'user_email' => $user->email, 'clubs' => $user->clubs]);

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ]);
    }


    public function refresh(Request $request)
    {
        try {
            $token = JWTAuth::parseToken();
            $oldToken = $token->getToken();
            $newToken = $token->refresh();
            
            // Invalidate the old token after issuing the new one
            JWTAuth::invalidate($oldToken);
            
            return $this->respondWithToken($newToken);
        } catch (TokenBlacklistedException $e) {
            return response()->json(['error' => 'Token has been blacklisted'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not refresh token: ' . $e->getMessage()], 401);
        }
    }
    protected function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }
    public function me()
    {
        return response()->json(Auth::user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */



}
