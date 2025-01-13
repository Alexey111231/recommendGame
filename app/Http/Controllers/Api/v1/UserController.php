<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Wkhooy\ObsceneCensorRus;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $limit = filter_var($_GET['limit'] ?? null, FILTER_VALIDATE_INT) ?: 10;
        $dateInput = $_GET['date'] ?? null;

        if (!is_null($dateInput)) {
            try {
                $date = \Carbon\Carbon::createFromFormat('dmy', $dateInput)->startOfDay();
            } catch (\Exception $e) {
                return response()->json([
                    'ok' => false,
                    'error' => 'Неверный формат даты. Ожидается формат dmy (например, 221124).'
                ], 400);
            }
        } else {
            $date = null;
        }

        try {
            if (is_null($date)) {
                $users = User::take($limit)->where('active', true)->orderBy('score', 'desc')->get();
            } else {
                $users = User::whereDate('updated_at', $date)->where('active', true)->take($limit)->orderBy('score', 'desc')->get();
            }
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ], 500);
        }

        if ($users->isEmpty()) {
            return response()->json([
                'ok' => false,
                'error' => 'Пользователи не найдены за указанную дату.'
            ], 404);
        }

        $users->makeHidden(['reasons']);

        return response()->json([
            'ok' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = ['vk_id', 'name', 'score', 'app_id', 'reasons', 'abuser', 'active'];
        $rules = [
            'vk_id' => [
                'required',
                'numeric',
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'score' => [
                'required',
                'integer',
                'digits_between:1,10',
            ],
            'app' => [
                'required',
                'string'
            ],
            'reasons' => [
                'required',
                'json'
            ],
        ];

        $messages = [
            'vk_id.required' => 'VK ID обязателен.',
            'vk_id.numeric' => 'VK ID должен быть числовым.',
            'name.required' => 'Имя обязательно для заполнения.',
            'name.string' => 'Имя должно быть строкой.',
            'name.min' => 'Имя должно быть не менее 2 символов.',
            'name.max' => 'Имя не должно превышать 255 символов.',
            'score.required' => 'Очки обязательно для заполнения.',
            'score.integer' => 'Очки должны быть целыми числами.',
            'score.digits_between' => 'Очки должны быть от 1 до 10.',
            'app.required' => 'Должно быть указано название приложения.',
            'app.string' => 'Название приложения должно быть строкой.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make(array_filter($request->all()), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'error' => $validator->errors()->first()
            ], 400);
        }

        $data = $validator->validated();

        if (!ObsceneCensorRus::isAllowed($data['name'])) {
            $data['abuser'] = true;
            $data['active'] = false;
        }

        $app = App::firstOrCreate(['name' => $data['app']]);
        $data['app_id'] = $app->id;

        $data = array_intersect_key($data, array_flip($fields));

        $user = User::firstOrCreate([
            'vk_id' => $data['vk_id']
        ], $data);

        if (!$user->wasRecentlyCreated) {
            $user->update($data);
        }

        $user->makeHidden(['reasons']);

        return response()->json([
            'ok' => true,
            'data' => $user
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = User::where('vk_id', $id)
                ->withCount(['attempts' => function ($query) {
                    $query->whereDate('created_at', \Carbon\Carbon::today());
                }])->firstOrFail();
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Пользователь не найден.'
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'data' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::where('vk_id', $id)->firstOrFail();
            $user->update(['active' => false]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Пользователь не найден.'
            ], 404);
        }
        return response()->json([
            'ok' => true,
            'message' => 'Пользователь деактивирован.',
            'data' => $user
        ]);
    }
}
