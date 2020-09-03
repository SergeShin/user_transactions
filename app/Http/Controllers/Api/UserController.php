<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Requests\User\StoreRequest;
use App\Http\Requests\Requests\User\UpdateRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Repository\UserRepository;
use App\Service\UserService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function index(Request $request, UserRepository $repository)
    {
        $limit = $request->get('limit');
        $sort = $request->get('sort');

        return new UserCollection($repository->fetchItems($limit, $sort));
    }

    public function store(StoreRequest $request, UserService $service)
    {
        $this->authorize('create', User::class);

        $user = $service->createUser(
            $request->name,
            $request->email,
            $request->password,
            $request->permissions
        );

        return response(
            new UserResource($user),
            201
        );
    }

    public function update(UpdateRequest $request, User $user, UserService $service): Response
    {
        $this->authorize('update', $user);

        $user = $service->updateUser(
            $user,
            $request->name,
            $request->email
        );

        return response(new UserResource($user), 203);
    }

    public function destroy(Request $request, User $user, UserService $service): Response
    {
        $this->authorize('delete', $user);

        $service->deleteUser($user);

        return response()->noContent();
    }
}
