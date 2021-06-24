<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $response;

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['create', 'login', 'unauthenticated']]);
        $this->response['error'] = null;
    }

    public function create(Request $request)
    {
        $rules = [
            'name'  => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|same:password_confirmation',
            'password_confirmation' => 'same:password',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $this->response['error'] = $validator->messages();
            return response($this->response, 400);
        }

        $user = new User();
        $user->name  = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->save();

        $token = auth()->attempt([
            'email'    => $request->input('email'),
            'password' => $request->input('password')
        ]);

        $user = auth()->user();
        $user['avatar'] = url('media/users/' . $user['avatar']);

        $this->response['data']  = $user;
        $this->response['token'] = $token;

        return response($this->response);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        if ($validator->fails()) {
            $this->response['error'] = $validator->messages();
            return response($this->response, 400);
        }

        $token = auth()->attempt([
            'email'     => $request->input('email'),
            'password'  => $request->input('password')
        ]);

        if (!$token) {
            $this->response['error'] = 'Usuário e/ou senha inválidos.';
            return response($this->response, 401);
        }

        $user = auth()->user();
        $user['avatar'] = url('media/users/' . $user['avatar']);

        $this->response['data']  = $user;
        $this->response['token'] = $token;

        return response($this->response);
    }

    public function logout()
    {
        auth()->logout();
        return [];
    }

    public function refresh()
    {
        $token  = auth()->refresh();
        $user   = auth()->user();
        $user['avatar'] = url('media/users/' . $user['avatar']);

        $this->response['data']  = $user;
        $this->response['token'] = $token;

        return response($this->response);
    }

    public function unauthenticated()
    {
        $this->response['error'] = 'Usuário não autenticado.';
        return response($this->response, 401);
    }
}
