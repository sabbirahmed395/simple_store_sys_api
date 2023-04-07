<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserFormRequest;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['role:admin']);
    }

    public function index()
    {
        $users = User::orderBy('id', 'desc');
        $users = $this->_per_page > 0 ? $users->paginate($this->_per_page) : $users->select('id', 'email')->get();

        if ($this->_per_page < 0) {
            return JsonResource::collection($users);
        }

        return UserResource::collection($users);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(UserFormRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);
            unset($data['role']);
            $user = User::create($data);
            $user->syncRoles($request->role);

            DB::commit();

            return new UserResource($user);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json(["message" => "Something went wrong. " . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserFormRequest $request, User $user)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            if (strlen($data['password']) > 0) {
                $data['password'] = Hash::make($data['password']);
            }
            unset($data['role']);
            $user->fill($data);
            $user->save();

            $user->syncRoles($request->role);

            DB::commit();

            return new UserResource($user);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json(["message" => "Something went wrong. " . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        DB::beginTransaction();

        try {
            $user->syncRoles([]);

            $user->delete();

            DB::commit();

            return response()->json(["message" => "User has been deleted."]);
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json(["message" => "Something went wrong. " . $e->getMessage()], 500);
        }
    }
}
