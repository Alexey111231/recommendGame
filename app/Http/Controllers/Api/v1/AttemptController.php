<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\User;
use Illuminate\Http\Request;

class AttemptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'vk_id' => [
                'required',
                'numeric',
            ]
        ];

        $messages = [
            'vk_id.required' => 'VK ID обязателен.',
            'vk_id.numeric' => 'VK ID должен быть числовым.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make(array_filter($request->all()), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'error' => $validator->errors()->first()
            ], 400);
        }

        $data = $validator->validated();

        $user = User::where('vk_id', $data['vk_id'])->first();

        if (!$user) {
            return response()->json([
                'ok' => false,
                'error' => 'Пользователь не найден.'
            ], 404);
        }

        $ac = $user->attempts()->create();

        $attemptsCount = $user->attempts()->whereDate('created_at', date('Y-m-d'))->count();

        return response()->json([
            'ok' => true,
            'data' => [
                'attempts_count' => $attemptsCount
            ]
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

        $attemptsCount = $user->attempts_count;

        return response()->json([
            'ok' => true,
            'data' => [
                'attempts_count' => $attemptsCount
            ]
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
        //
    }
}
