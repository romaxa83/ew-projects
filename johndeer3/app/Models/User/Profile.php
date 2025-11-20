<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $country
 * @property string $created_at
 * @property string $updated_at
 */

class Profile extends Model
{
    use HasFactory;

    const TABLE = 'users_profile';
    protected $table = self::TABLE;
}
