<?php

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class BSSuperAdminSeeder extends Seeder
{
    /**
     * @throws Throwable
     */
    public function run(): void
    {
        if (!App::environment('production')) {
            return;
        }

        Model::reguard();

        $bsSuperAdminData = config('bodyshop.superadmin');

        if (empty($bsSuperAdminData['email']) || User::where('email', $bsSuperAdminData['email'])->exists()) {
            return;
        }

        try {
            DB::beginTransaction();

            $user = new User();
            $user->first_name = $bsSuperAdminData['first_name'];
            $user->last_name = $bsSuperAdminData['last_name'];
            $user->email = $bsSuperAdminData['email'];
            $user->setPasswordAttribute($bsSuperAdminData['password']);
            $user->status = User::STATUS_ACTIVE;
            $user->save();
            $user->assignRole(User::BSSUPERADMIN_ROLE);

            DB::commit();
        } catch (Exception $exception) {
            Log::error($exception);

            DB::rollBack();
        }
    }
}
