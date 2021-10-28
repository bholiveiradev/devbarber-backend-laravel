<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\BarberAvailability;
use App\Models\BarberPhoto;
use App\Models\BarberService;
use App\Models\BarberTestimonial;
use App\Models\UserAppointment;
use App\Models\UserFavorite;
use Illuminate\Http\Request;

class BarberController extends Controller
{
    private $response;
    private $user;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->response['error'] = null;
        $this->user = auth()->user();
    }

    public function index(Request $request)
    {
        $lat    = $request->input('lat');
        $lng    = $request->input('lng');
        $city   = $request->input('city');
        $offset = $request->input('offset');

        if (!$offset) $offset = 0;

        if (!empty($city)) {
            $result = $this->searchGeo($city);

            if (!count($result['results'])) {
                $this->response['error'] = 'Localização não encontrada';
                return response($this->response);
            }

            $lat = $result['results'][0]['geometry']['location']['lat'];
            $lng = $result['results'][0]['geometry']['location']['lng'];
        } elseif (!empty($lat) && !empty($lng)) {
            $result = $this->searchGeo($lat . ',' . $lng);

            if (!count($result['results'])) {
                $this->response['error'] = 'Localização não encontrada';
                return response($this->response);
            }

            $city = $result['results'][0]['formatted_address'];
        } else {
            // $lat  = '-23.5630907';
            // $lng  = '-46.6682795';
            // $city = 'São Paulo';

            $this->response['error'] = 'Envie a cidade ou as coordenadas (latitude e longitude)';
            return response($this->response, 400);
        }

        $barbers = Barber::select(Barber::raw('*, SQRT(
            POW(69.1 * (latitude - ' . $lat . '), 2) +
            POW(69.1 * (' . $lng . ' - longitude) * COS(latitude / 57.3), 2)) AS distance'))
            ->havingRaw('distance < ?', [10])
            ->orderBy('distance', 'ASC')
            ->offset($offset)
            ->limit(5)
            ->get();

        foreach ($barbers as $key => $value) {
            $barbers[$key]['avatar'] = url('media/users/' . $barbers[$key]['avatar']);
        }

        $this->response['data'] = $barbers;
        //$this->response['loc']  = 'São Paulo';

        return response($this->response);
    }

    public function show($id)
    {
        $barber = Barber::find($id);

        $barber['avatar'] = url('media/users/' . $barber['avatar']);
        $barber['favorited'] = false;
        $barber['photos'] = [];
        $barber['services'] = [];
        $barber['testimonials'] = [];
        $barber['available'] = [];

        if (!$barber) {
            $this->response['error'] = 'Barbeiro não encontrado.';
            return response($this->response, 404);
        }

        // Verificando se o barbeiro é um favorito do usuário autenticado
        $cFavorite = UserFavorite::where('user_id', $this->user->id)
            ->where('barber_id', $barber->id)
            ->count();

        if ($cFavorite) $barber['favorited'] = true;

        // Fotos do barbeiro
        $barber['photos'] = BarberPhoto::select(['id', 'url'])->where('barber_id', $barber->id)->get();

        foreach ($barber['photos'] as $key => $value) {
            $barber['photos'][$key]['url'] = url('media/uploads/' . $barber['photos'][$key]['url']);
        }

        // Serviços do barbeiro
        $barber['services'] = BarberService::select(['id', 'name', 'price'])->where('barber_id', $barber->id)->get();

        // Depoimentos do barbeiro
        $barber['testimonials'] = BarberTestimonial::select(['id', 'name', 'rate', 'body'])->where('barber_id', $barber->id)->get();

        // Disponibilidade do barbeiro
        $availability = [];

        // Pegando a disponibilidade crua
        $avails = BarberAvailability::where('barber_id', $barber->id)->get();
        $availWeekdays = [];

        // Cria o array com o dia da semana como chaves e os horários como valores
        foreach ($avails as $item) {
            $availWeekdays[$item['week_day']] = explode(',', $item['hours']);
        }

        // - Pegando os agendamentos dos próximos 20 dias
        $appointments = [];
        $appointmentQuery = UserAppointment::where('barber_id', $barber->id)
            ->whereBetween('appointment_datetime', [
                date('Y-m-d') . ' 00:00:00',
                date('Y-m-d', strtotime('+20 days')) . ' 23:59:59'
            ])
            ->get();

        foreach ($appointmentQuery as $appointmentItem) {
            $appointments[] = $appointmentItem['appointment_datetime'];
        }

        // Gerar a disponibilidade real
        for ($i = 0; $i < 20; $i++) {
            $timeItem = strtotime('+' . $i . ' days');
            $weekDay  = date('w', $timeItem);

            if (in_array($weekDay, array_keys($availWeekdays))) {
                $hours   = [];
                $dayItem = date('Y-m-d', $timeItem);

                foreach ($availWeekdays[$weekDay] as $hourItem) {
                    $dayFormatted = $dayItem . ' ' . $hourItem . ':00';

                    if (!in_array($dayFormatted, $appointments)) {
                        $hours[] = $hourItem;
                    }
                }

                if (count($hours)) {
                    $availability[] = [
                        'date'  => $dayItem,
                        'hours' => $hours
                    ];
                }
            }
        }

        $barber['available'] = $availability;

        $this->response['data'] = $barber;

        return response($this->response);
    }

    public function addAppointment(Request $request, $id)
    {
        // service, year, month, day, hour
        $service = $request->input('service');
        $year    = (int) $request->input('year');
        $month   = (int) $request->input('month');
        $day     = (int) $request->input('day');
        $hour    = (int) $request->input('hour');

        $month  = ($month < 10) ? '0' . $month : $month;
        $day    = ($day < 10) ? '0' . $day : $day;
        $hour   = ($hour < 10) ? '0' . $hour : $hour;

        // Verificar se o serviço do barbeiro existe
        $barberService = BarberService::select()
            ->where('id', $service)
            ->where('barber_id', $id)
            ->first();

        if (!$barberService) {
            $this->response['error'] = 'Serviço inexistente.';
            return response($this->response);
        }

        // Verificar se a data é uma data válida
        $appointmentDate = $year . '-' . $month . '-' . $day . ' ' . $hour . ':00:00';

        if (!strtotime($appointmentDate)) {
            $this->response['error'] = 'Data inválida.';
            return response($this->response, 400);
        }

        // Verificar se o barbeiro já possui agendamento neste dia/hora
        $userAppointment = UserAppointment::select()
            ->where('barber_id', $id)
            ->where('appointment_datetime', $appointmentDate)
            ->count();

        if ($userAppointment) {
            $this->response['error'] = 'O barbeiro já possui um agendamento neste dia/hora.';
            return response($this->response);
        }

        // Verificar se o barbeiro atende desta data/hora
        $weekDay = date('w', strtotime($appointmentDate));
        $avail = BarberAvailability::select()
            ->where('barber_id', $id)
            ->where('week_day', $weekDay)
            ->first();

        // -- Verificar se o barbeiro atende neste dia
        if (!$avail) {
            $this->response['error'] = 'O barbeiro não atende neste dia.';
            return response($this->response);
        }

        // -- Verificar se o barbeiro atende nesta hora
        $hours = explode(',', $avail['hours']);

        if (!in_array($hour . ':00', $hours)) {
            $this->response['error'] = 'O barbeiro não atende nesta hora.';
            return response($this->response);
        }

        // Fazer o agendamento
        $appointment = new UserAppointment();
        $appointment->user_id = $this->user->id;
        $appointment->barber_id = $id;
        $appointment->barber_service_id = $service;
        $appointment->appointment_datetime = $appointmentDate;
        $appointment->save();

        $this->response['data'] = $appointment;

        return response($this->response);
    }

    public function search(Request $request)
    {
        $q = $request->input('q');

        if (!$q) {
            $this->response['error'] = 'Digite o conteúdo da busca.';
            return response($this->response, 400);
        }

        $this->response['data'] = [];

        $barbers = Barber::select()
            ->where('name', 'LIKE', '%' . $q . '%')
            ->get();

        if ($barbers) {
            foreach ($barbers as $key => $value) {
                $barbers[$key]['avatar'] = url('media/users/' . $barbers[$key]['avatar']);
            }

            $this->response['data'] = $barbers;
        }

        return response($this->response);
    }

    private function searchGeo($address)
    {
        $key     = env('MAPS_KEY', null);
        $address = urlencode($address);
        $url     = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . $key;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
