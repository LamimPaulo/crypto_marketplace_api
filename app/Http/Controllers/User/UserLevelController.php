<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLevelRequest;
use App\Models\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

class UserLevelController extends Controller
{
    public function index()
    {
        try {
            $levels = UserLevel::with('product')
                ->whereHas('product', function ($product){
                    return $product->where('is_active', true);
                })
                ->orderBy('product_id')->get();
            return response($levels, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(UserLevelRequest $request)
    {
        return $request->all();
    }
}
