<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StaffUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        $staff = DB::table('staffs')->where('staff_id', $identifier)->first();
        return $staff ? $this->getGenericUser($staff) : null;
    }

    public function retrieveByToken($identifier, $token)
    {
        $staff = DB::table('staffs')->where('staff_id', $identifier)
            ->where('remember_token', $token)
            ->first();
        return $staff ? $this->getGenericUser($staff) : null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        DB::table('staffs')
            ->where('staff_id', $user->getAuthIdentifier())
            ->update(['remember_token' => $token]);
    }

    public function retrieveByCredentials(array $credentials)
    {
        return DB::table('staffs')
            ->where('staff_email', $credentials['email'])
            ->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // Password is stored in plain text in staff_plain_password
        return $credentials['password'] === $user->staff_plain_password;
    }

    protected function getGenericUser($staff)
    {
        if (!$staff) return null;

        return new class($staff) implements Authenticatable {
            public $staff_id, $staff_name, $staff_username, $staff_email, $staff_password, $staff_plain_password, $remember_token;

            public function __construct($staff)
            {
                foreach ($staff as $key => $value) {
                    $this->$key = $value;
                }
            }

            public function getAuthIdentifierName()
            {
                return 'staff_id';
            }

            public function getAuthIdentifier()
            {
                return $this->staff_id;
            }

            public function getAuthPassword()
            {
                return $this->staff_password;
            }

            public function getRememberToken()
            {
                return $this->remember_token ?? null;
            }

            public function setRememberToken($value)
            {
                $this->remember_token = $value;
            }

            public function getRememberTokenName()
            {
                return 'remember_token';
            }
        };
    }
}
