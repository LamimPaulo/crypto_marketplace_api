<?php

namespace App\Http\Controllers\User;

use App\Enum\EnumUserLevelType;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserLevelRequest;
use App\Models\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;

class UserLevelController extends Controller
{
    public function index()
    {
        try {

            $type = auth()->user()->country_id == 31 ? EnumUserLevelType::NACIONAL : EnumUserLevelType::INTERNACIONAL;

            $levels = UserLevel::with('product')
                ->whereHas('product', function ($product){
                    return $product->where('is_active', true);
                })
                ->where('type', $type)
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
