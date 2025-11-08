<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Log as LogFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Authenticate user and return JWT token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *             @OA\Property(property="expires_in", type="integer", example=3600),
     *             @OA\Property(property="role", type="string", example="student")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
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

    protected function respondWithToken($token, $role = null)
    {
        $payload = [
            'access_token' => $token,
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];

        if ($role) {
            $payload['role'] = $role;
        }

        return response()->json($payload);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     summary="Refresh JWT token",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *             @OA\Property(property="expires_in", type="integer", example=3600)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Token has been blacklisted")
     *         )
     *     )
     * )
     */
    public function refresh(Request $request)
    {
        try {
            $token = JWTAuth::parseToken();
            $oldToken = $token->getToken();
            $newToken = $token->refresh();

            // Invalidate the old token after issuing the new one
            JWTAuth::invalidate($oldToken);

            // Re-authenticate the user with the new token to ensure Auth::user() is available
            $user = JWTAuth::setToken($newToken)->authenticate();

            if (!$user) {
                return response()->json(['error' => 'User not found after token refresh'], 401);
            }

            return $this->respondWithToken($newToken, $user->role);
        } catch (TokenBlacklistedException $e) {
            return response()->json(['error' => 'Token has been blacklisted'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not refresh token: ' . $e->getMessage()], 401);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Log out user (invalidate token)",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
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
    protected function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }
    public function me()
    {
        $user = Auth::user();
        if ($user) {
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
            $user->role = $role; // Update the user object's role
            return response()->json($user->makeHidden('password'));
        }
        return response()->json(null);
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

    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="message", type="string", example="Register Succefly")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or user already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:user',
            'password' => 'required|string|min:6',
        ]);

        $email = $request['email'];
        if (User::where('email', $email)->first()) {
            return response()->json([
                'message' => 'User already exists',
            ], 422);
        }
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'role' => 'student',
            'image' => '/images/default_user_image.jpg',
            'department' => ' ',
        ]);

        return response()->json(['user' => $user, 'message' => 'Register Succefly'], 201);
    }

}
