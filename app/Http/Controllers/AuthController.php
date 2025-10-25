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
        $role = '';
        switch ($user->role) {
            case 'admin':
                $role = 'admin';
                break;
            case 'student':
                $role = 'student';
                break;
            default:
                if ($user->clubs->isNotEmpty()) {
                    $role = $user->clubs->first()->pivot->role;
                } else {
                    $role = 'member'; // Default to 'member' if no club association found
                }
                break;
        }
            ;
        

        LogFacade::info('AuthController: User logged in with clubs', ['user_id' => $user->id, 'user_email' => $user->email, 'clubs' => $user->clubs]);

        return $this->respondWithToken($token, $role);
    }

    protected function respondWithToken($token, $role)  
    {
        return response()->json([
            'access_token' => $token,
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            "role" => $role,
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
        $user = Auth::user();
        return response()->json($user ? \App\Models\User::find($user->id)->load('clubs') : null);
    }   

    /**
     * @OA\Get(
     *     path="/api/auth/me",
     *     summary="Get authenticated user details",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="some-uuid"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="role", type="string", example="member"),
     *             @OA\Property(property="clubs", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */



}
