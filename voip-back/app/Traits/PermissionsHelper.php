<?php

namespace App\Traits;

trait PermissionsHelper
{
    public static function deleteHiddenPerms(array $perms, string $guard): array
    {
        $deleteKeys = [];
        foreach (config("grants.hidden.$guard.groups") ?? [] as $permissionsClasses) {
            foreach ($permissionsClasses as $permClass){
                $deleteKeys[$permClass::KEY] = $permClass::KEY;
            }
        }

        foreach ($perms ?? [] as $k => $perm){
            foreach ($perm['permissions'] as $i => $p){
                if(array_key_exists($p['key'], $deleteKeys)){
                    unset($perms[$k]['permissions'][$i]);
                }
            }
        }

        foreach ($perms ?? [] as $k => $perm){
            if(empty($perm['permissions'])){
                unset($perms[$k]);
            }
        }

        return $perms;
    }
}
