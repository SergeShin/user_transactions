<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Requests\UserTransactions\StoreRequest;
use App\Http\Resources\UserResource;
use App\Repository\UserRepository;
use App\User;
use Illuminate\Http\Request;

class UserTransactionsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request, User $user, UserRepository $repository)
    {
        $user->transactions()->create($request->only([
            'amount',
            'type'
        ]));

        return response(
            new UserResource($repository->fetchItem($user->id)),
            201
        );
    }
}
