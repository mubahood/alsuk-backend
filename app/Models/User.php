<?php

namespace App\Models;

use Encore\Admin\Form\Field\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as RelationsBelongsToMany;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;



class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function send_password_reset()
    {
        $u = $this;
        $u->intro = rand(100000, 999999);
        $u->save();
        $data['email'] = $u->email;
        if ($u->email == null || $u->email == "") {
            $data['email'] = $u->username;
        }
        $data['name'] = $u->name;
        $data['subject'] = env('APP_NAME') . " - Password Reset";
        $data['body'] = "<br>Dear " . $u->name . ",<br>";
        $data['body'] .= "<br>Please use the code below to reset your password.<br><br>";
        $data['body'] .= "CODE: <b>" . $u->intro . "</b><br>";
        $data['body'] .= "<br>Thank you.<br><br>";
        $data['body'] .= "<br><small>This is an automated message, please do not reply.</small><br>";
        $data['view'] = 'mail-1';
        $data['data'] = $data['body'];
        try {
            Utils::mail_sender($data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function send_verification_code($email)
    {
        $u = $this;
        $u->intro = rand(100000, 999999);
        $u->save(); 
        $data['email'] = $email;
        if ($email == null || $email == "") {
            throw new \Exception("Email is required.");
        }

        $data['name'] = $u->name;
        $data['subject'] = env('APP_NAME') . " - Email Verification";
        $data['body'] = "<br>Dear " . $u->name . ",<br>";
        $data['body'] .= "<br>Please use the CODE below to verify your email address.<br><br>";
        $data['body'] .= "CODE: <b>" . $u->intro . "</b><br>";
        $data['body'] .= "<br>Thank you.<br><br>";
        $data['body'] .= "<br><small>This is an automated message, please do not reply.</small><br>";
        $data['view'] = 'mail-1';
        $data['data'] = $data['body'];
        try {
            Utils::mail_sender($data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }



    public function campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    public function programs()
    {
        return $this->hasMany(UserHasProgram::class, 'user_id');
    }
}
