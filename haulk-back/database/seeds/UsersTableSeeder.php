<?php

use App\Models\Users\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        if (App::environment('production')) {
            return;
        }

        try {
            $user2 = new User();
            $user2->first_name = 'bozhok';
            $user2->last_name = 'a';
            $user2->email = 'bozhok.a@wezom.com.ua';
            $user2->setPasswordAttribute('qwerty12');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::SUPERADMIN_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'Guram';
            $user2->last_name = 'Vashakidze';
            $user2->email = 'vashakidze.g.wezom@gmail.com';
            $user2->setPasswordAttribute('qwerty12');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::SUPERADMIN_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'wezom';
            $user2->last_name = 'qa';
            $user2->email = 'wezom.qa@gmail.com';
            $user2->setPasswordAttribute('qwerty12');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::DISPATCHER_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'mazurok';
            $user2->last_name = 'wezom';
            $user2->email = 'mazurok.a.wezom@gmail.com';
            $user2->setPasswordAttribute('123456Mm');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::DRIVER_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'monkas';
            $user2->last_name = 'work';
            $user2->email = 'monkaswork@gmail.com';
            $user2->setPasswordAttribute('monkaswork0');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::DRIVER_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'viktor';
            $user2->last_name = 'padalka';
            $user2->email = 'viktorpadalka01@gmail.com';
            $user2->setPasswordAttribute('123456Pv');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::DRIVER_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'ivanoff';
            $user2->last_name = 'wezom';
            $user2->email = 'ivanoff.wezom@gmail.com';
            $user2->setPasswordAttribute('123456Qa');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::DRIVER_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'chevtaeva';
            $user2->last_name = 'wezom';
            $user2->email = 'chevtaeva.i.wezom@gmail.com';
            $user2->setPasswordAttribute('222222Ww');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::DRIVER_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'wezom';
            $user2->last_name = 'test';
            $user2->email = 'wezom.test.qa@gmail.com';
            $user2->setPasswordAttribute('Qwerty12');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::DRIVER_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'i';
            $user2->last_name = 'chevtaeva';
            $user2->email = 'chevtaeva.i@wezom.com.ua';
            $user2->setPasswordAttribute('admin');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::SUPERADMIN_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'zolotarevskaya';
            $user2->last_name = 'wezom';
            $user2->email = 'zolotarevskaya.i.wezom@gmail.com';
            $user2->setPasswordAttribute('qwerty12');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::SUPERADMIN_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'Aleksey';
            $user2->last_name = 'Savvo';
            $user2->email = 'savvo.a.wezom@gmail.com';
            $user2->setPasswordAttribute('admin');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::SUPERADMIN_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'Vladimir';
            $user2->last_name = 'Tkachuk';
            $user2->email = 'frestboy@gmail.com';
            $user2->setPasswordAttribute('frestboy');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::SUPERADMIN_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $driver = new User();
            $driver->first_name = 'Pavel';
            $driver->last_name = 'Prymak';
            $driver->email = 'primak.p.wezom@gmail.com';
            $driver->setPasswordAttribute('driver111');
            $driver->status = User::STATUS_ACTIVE;
            $driver->carrier_id = 1;
            $driver->save();
            $driver->assignRole(User::DRIVER_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'Dmitriy';
            $user2->last_name = 'Zavgorodniy';
            $user2->email = 'zavgorodniy.d@wezom.com.ua';
            $user2->setPasswordAttribute('admin123');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::SUPERADMIN_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'Ann';
            $user2->last_name = 'Chuliba';
            $user2->email = 'chuliba.a@wezom.com.ua';
            $user2->setPasswordAttribute('123456qw');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::SUPERADMIN_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'wezom001';
            $user2->last_name = 'test';
            $user2->email = 'wezom001@gmail.com';
            $user2->setPasswordAttribute('admin');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::SUPERADMIN_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'Sergii';
            $user2->last_name = 'Boiko';
            $user2->email = 'boisko.s.wezom@gmail.com';
            $user2->setPasswordAttribute('admin');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::SUPERADMIN_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'n';
            $user2->last_name = 'chermyanin';
            $user2->email = 'chermyanin.n.wezom@gmail.com';
            $user2->setPasswordAttribute('admin');
            $user2->status = User::STATUS_ACTIVE;
            $user2->carrier_id = 1;
            $user2->save();
            $user2->assignRole(User::SUPERADMIN_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'BS';
            $user2->last_name = 'Super Admin';
            $user2->email = 'alekhina.i@wezom.com.ua';
            $user2->setPasswordAttribute('qwerty12');
            $user2->status = User::STATUS_ACTIVE;
            $user2->save();
            $user2->assignRole(User::BSSUPERADMIN_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'BS';
            $user2->last_name = 'Admin Darya';
            $user2->email = 'maxtras2019@gmail.com';
            $user2->setPasswordAttribute('qwerty12');
            $user2->status = User::STATUS_ACTIVE;
            $user2->save();
            $user2->assignRole(User::BSADMIN_ROLE);
        } catch (Exception $exception) {
        }

        try {
            $user2 = new User();
            $user2->first_name = 'BS';
            $user2->last_name = 'Admin Max';
            $user2->email = 'admin@admin.com';
            $user2->setPasswordAttribute('qwerty12');
            $user2->status = User::STATUS_ACTIVE;
            $user2->save();
            $user2->assignRole(User::BSADMIN_ROLE);
        } catch (Exception $exception) {
        }
    }
}
