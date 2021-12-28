<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        return response()->json(compact('token'));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'avatar' => 'required|image|max:2000',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = new User($request->all());
        //Hash para password
        $user->password = Hash::make($request->get('password'));

        //Guardar imagen
        $path = $request->avatar->store('/public/users');
        $user->avatar = Storage::url($path);

        //Guadar usuario
        $user->save();

        //Generar token
        $token = JWTAuth::fromUser($user);

        //Retornar User y token
        return response()->json(compact('user', 'token'), 201)
            ->withCookie(
                'token',
                $token,
                config('jwt.ttl'),
                '/', //path
                null,
                config('app.env')  !== 'local',
                true, //path
                false,
                config('app.env') !== 'local' ? 'None' : 'Lax'
            );
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['token_absent'], 401);
        }
        return response()->json(compact('user'), 200);
    }

    public function update(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['token_absent'], 401);
        }
        //validacion de campos
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'email' => 'nullable|string|email|max:100|unique:users',
            'password' => 'nullable|string|min:6|confirmed',
            'avatar' => 'nullable|image',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = new User($request->all());

        if (!is_null($request->avatar)) {
            $path = $request->avatar->store('public/users');
            $user->avatar = $path;
            $user->update();
        }
        return response()->json(compact('user'), 200);
    }

    public function delete()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['token_absent'], 401);
        }
        $user->delete();
        return response()->json(null, 204);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                "status" => "success",
                "message" => "Usuario conectado exitosamente."
            ], 200)
                ->withCookie(
                    'token',
                    null,
                    config('jwt.ttl'),
                    '/',
                    null,
                    config('app.env') !== 'local',
                    true,
                    false,
                    config('app.env') !== 'local' ? 'None' : 'Lax'
                );
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(
                [
                    "message" => "No se pudo cerrar la sesión " + $e
                ],
                500
            );
        }
    }
}
