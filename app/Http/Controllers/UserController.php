<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Events\Registered;

//
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
            'cel' => 'required|string|max:10|min:10',
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
        $path = $request->file('avatar')->store('users', 's3');

        //Upload File to s3
        Storage::disk('s3')->setVisibility($path, 'public');
        $user->avatar = Storage::disk('s3')->url($path);

        //Guadar usuario
        $user->save();

        //Envio correo de confirmacion
        //event(new Registered($user));

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
            'cel' => 'nullable|string|max:10|min:10',
            'email' => 'nullable|string|email|max:100|unique:users',
            'password' => 'nullable|string|min:6|confirmed',
            'avatar' => 'nullable|image',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user->update($request->all());

        if (!is_null($request->avatar)) {
            $path = $request->file('avatar')->store('users', 's3');
            Storage::disk('s3')->setVisibility($path, 'public');
            $user->avatar = Storage::disk('s3')->url($path);
        }

        $user->save();

        return response()->json($user, 200);
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $user->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return response()->json(null, 204);
    }

    public function logout()
    {
        auth()->logout(true);
        response()->json(["message" => "logged_out"], 200);
    }
}
