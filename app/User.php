<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * 
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
     
    //追記
    public function microposts(){
        
        return $this->hasMany(Micropost::class);
         }
    public function followings(){
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
        }
    public function followers(){
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
        }
    
    public function follow($userId) { //すでにフォローしているかを確認
        $exist = $this->is_following($userId); //自分自身でフォローしていないことの確認
        $its_me = $this->id == $userId;
        
            if ($exist || $its_me) {
                return false; //すでにフォローしているなら何もなし
    
            } else {
            $this->followings()->attach($userId);
            return true; //未フォローであればフォローする
            }
        }
    public function unfollow($userId){
        //すでにフォローしていればフォローを外す
        $exist = $this->is_following($userId);
        //自分自身っ出ないかの確認
        $its_me = $this->id ==$userId;
        
        if($exist && !$its_me){
            //すでにフォローしているならフォローを外す
            $this->followings()->detach($userId);
            return true;
        } else {
            //未フォローであればなにもなし
            return false;
            }
        }

public function is_following($userId){
    return $this->followings()->where('follow_id', 'user_id')->exists();
    }
}

  