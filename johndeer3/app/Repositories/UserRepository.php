<?php
//
//namespace App\Repositories;
//
//use App\Exceptions\UserNotFoundException;
//use App\Helpers\ParseQueryParams;
//use App\Models\User\User;
//use Illuminate\Database\Eloquent\Builder;
//use Illuminate\Http\Request;
//
//class UserRepository
//{
//    /**
//     * @param $login
//     * @return mixed
//     * @throws UserNotFoundException
//     */
//    public function getUserByLogin($login)
//    {
//        return User::where('login',$login)->first();
//    }
//
//    public function getUserById($id)
//    {
//        return User::find($id);
//    }
//
//    public function existByEmail($email): bool
//    {
//        return User::query()->where('email', $email)->exists();
//    }
//
//    public function queryUsersByRole($role)
//    {
//        return User::query()
//            ->whereHas('roles', function(Builder $query) use ($role){
//                $query->where('role', $role);
//            });
//    }
//
//    public function getUsersByRole($role)
//    {
//        return $this->queryUsersByRole($role)->get();
//    }
//
//    public function deleteUsersByRole($role)
//    {
//        return $this->queryUsersByRole($role)->delete();
//    }
//
//    public function existByJdId($jdId)
//    {
//        return User::where('jd_id', $jdId)->exists();
//    }
//
//    public function getByJdId($jdId)
//    {
//        return User::where('jd_id', $jdId)->first();
//    }
//
//    public function getByEmail($email)
//    {
//        return User::where('email', $email)->first();
//    }
//
//    public function getAll(Request $request, $forAdmin = false)
//    {
//        $perPage = $request['perPage'] ?? User::DEFAULT_PER_PAGE;
//
//        $query = User::query()->with(['roles', 'profile', 'dealer', 'dealers']);
//
//        if(!$forAdmin){
//            $query->where('status', true);
//        }
//
//        $query->notAdmin();
//
//        // фильтр по name (Имя Фамилия)
//        if(isset($request->name)){
//            $name = ParseQueryParams::name($request->name);
//            $query->whereHas('profile', function(Builder $query) use($name) {
//                if(count($name) == 1){
//                    $query->whereRaw("(first_name LIKE '{$name[0]}%' OR last_name LIKE '{$name[0]}%')");
//                } else {
//                    $query->whereRaw("(first_name LIKE '{$name[0]}%' AND last_name LIKE '{$name[1]}%')");
//                }
//            });
//        }
//        // фильтр по дилеру
//        if(isset($request->dealer)){
//            $dealer = $request->dealer;
//
//            $query->where(function(Builder $query) use($dealer) {
//                $query->whereHas('dealer', function(Builder $q) use ($dealer){
//                    $q->where('name', 'like', $dealer.'%');
//                })
//                    ->orWhereHas('dealers', function (Builder $q) use ($dealer){
//                        $q->where('name', 'like', $dealer.'%');
//                    });
//            });
//        }
//        // фильтр по email
//        if(isset($request->email)){
//            $query->where('email', $request->email);
//        }
//        // фильтр по логину
//        if(isset($request->login)){
//            $query->where('login', 'like', $request->login . '%');
//        }
//        // фильтр по роли
//        if(isset($request->role)){
//            $role = $request->role;
//            $query->whereHas('roles', function(Builder $query) use ($role){
//                $query->where('role', $role);
//            });
//        }
//        // фильтр по стране
//        if(isset($request->country_id)){
//            $query->where('nationality_id', $request->country_id);
//        }
//
//        return $query->paginate($perPage);
//    }
//}
