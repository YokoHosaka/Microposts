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
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
     }
         
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }
        
    public function followers()
    {
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
        //自分自身でないかの確認
        $its_me = $this->id == $userId;
        
        if($exist && !$its_me){
            //すでにフォローしているならフォローを外す
            $this->followings()->detach($userId);
            return true;
        } else {
            //未フォローであればなにもなし
            return false;
            }
        }
        
    public function is_following($userId)
    {
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    
    public function feed_microposts()
    {
        $follow_user_ids = $this->followings()->lists('users.id')->toArray();
        $follow_user_ids[] = $this->id;
        // dump($follow_user_ids); とすると$follow_user_idsの中身を表示できる。表示箇所はこの引数が呼び出される個所で、ここではログイン後の初期画面であるWelcome.blade
        
        return Micropost::whereIn('user_id', $follow_user_ids);
    }
    
   // Favorite 作成
    
    public function favorite()
    {
        return $this->belongsToMany(Micropost::class, 'favorites', 'user_id', 'favorite_id')->withTimestamps(); //'favorite'はだめ。creates_favorites_tableとテーブール名はfavoritesと複数形だから
    }
        
    public function add_favorite($favoriteId) { //すでにお気に入りに追加しているかを確認
       
        if ($this->is_favorite($favoriteId)){
            return false;
            
        } else {
        $this->favorite()->attach($favoriteId);
            return true; //お気に入り未追加なら追加する
            }
        }
        
    public function drop_favorite($favoriteId){
        //お気に入り追加されているものをお気に入りから外す
        if ($this->is_favorite($favoriteId)){
            //お気に入りをやめる
            $this->favorite()->detach($favoriteId);
            return true;
        } else {
            //お気に入り追加されていないのであればなにもなし
            return false;
            }
        }
        
    public function is_favorite($MicropostId)
    {
        return $this->favorite()->where('favorite_id', $MicropostId)->exists(); // favorite_idカラムに含まれるMicropostのID
    }
    
    public function feed_favorite()
    {
        $favorite_user_ids = $this->favorite()->lists('MicropostId')->toArray();
        $favorite_user_ids[] = $this->id;
        return Micropost::whereIn('favorite_id', $favorite_user_ids);
    }
}

  