<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\UserFavorite;
use App\Models\BarberService;
use App\Models\User;
use App\Models\UserAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    private $response;
    private $user;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->response['error'] = null;
        $this->user = auth()->user();
    }

    public function show()
    {
        $user = $this->user;
        $user['avatar'] = url('media/users/' . $user['avatar']);

        $this->response['data'] = $user;

        return response($this->response);
    }

    public function update(Request $request)
    {
        $rules = [
            'name'  => 'min:3',
            'email' => 'email|unique:users,email,' . $this->user->id,
            'password' => 'min:6|same:password_confirmation',
            'password_confirmation' => 'same:password'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $this->response['error'] = $validator->messages();
            return response($this->response, 400);
        }

        $name     = $request->input('name');
        $email    = $request->input('email');
        $password = $request->input('password');

        $user = User::find($this->user->id);

        if ($name) $user->name = $name;
        if ($email) $user->email = $email;
        if ($password) $user->password = $password;

        $result = $user->save();

        if ($result) {
            $this->response['data'] = $user;
        }

        return response($this->response);
    }

    public function avatar(Request $request)
    {
        $rules = [
            'avatar' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $this->response['error'] = $validator->messages();
            return response($this->response, 400);
        }

        $avatar = $request->file('avatar');
        $dest   = public_path('/media/users/');
        $name   = md5(time() . rand()) . '.' . $avatar->getClientOriginalExtension();
        $path   = $dest . $name;

        if (!file_exists($dest)) {
            mkdir($dest, 755, true);
        }

        $image = Image::make($avatar->getRealPath());
        $image->fit(300, 300)->save($path);

        $user = User::find($this->user->id);

        if (file_exists($dest . $user->avatar)) {
            unlink($dest . $user->avatar);
        }

        $user->avatar = $name;
        $user->save();

        $this->response['avatar'] = url('media/users/' . $user->avatar);

        return response($this->response);
    }

    public function toggleFavorite(Request $request)
    {
        $barber_id = $request->input('barber');
        $barber = Barber::find($barber_id);

        if (!$barber) {
            $this->response['error'] = 'Barbeiro não encontrado.';
            return response($this->response, 404);
        }

        $hasFavorite  = UserFavorite::select()
            ->where('user_id', $this->user->id)
            ->where('barber_id', $barber->id)
            ->count();

        if (!$hasFavorite) {
            $favorite = new UserFavorite();
            $favorite->user_id   = $this->user->id;
            $favorite->barber_id = $barber->id;
            $result = $favorite->save();
        } else {
            $favorite = UserFavorite::select()
                ->where('user_id', $this->user->id)
                ->where('barber_id', $barber->id)
                ->first();

            $result = $favorite->delete();
        }

        $this->response['result'] = $result;

        return response($this->response);
    }

    public function favorites()
    {
        $this->response['data'] = [];

        $favorites = UserFavorite::select()
            ->where('user_id', $this->user->id)
            ->get();

        if ($favorites) {
            foreach ($favorites as $favorite) {
                $barber = Barber::find($favorite->barber_id);
                $barber['avatar'] = url('media/users/' . $barber['avatar']);
                $this->response['data'][] = $barber;
            }
        }

        return response($this->response);
    }

    public function appointments()
    {
        $this->response['data'] = [];

        $appointments = UserAppointment::select()
            ->where('user_id', $this->user->id)
            ->orderBy('appointment_datetime', 'DESC')
            ->get();

        if ($appointments) {
            foreach ($appointments as $appointment) {
                $barber  = Barber::find($appointment->barber_id);
                $barber['avatar'] = url('media/users/' . $barber['avatar']);

                $service = BarberService::find($appointment->service_id);

                $this->response['data'][] = [
                    'id'       => $appointment->id,
                    'datetime' => $appointment->appointment_datetime,
                    'barber'   => $barber,
                    'service'  => $service
                ];
            }
        }

        return response($this->response);
    }
}
