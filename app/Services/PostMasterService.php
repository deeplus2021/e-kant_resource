<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/10/16
 * Time: 16:44
 */

namespace App\Services;


use App\Models\Post;
use App\Models\PostTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Object_;

class PostMasterService
{
    public function getFieldInfoList($params)
    {
        $user = Auth::user();
        $query = DB::table("t_field")
            ->select(
                "id",
                "name",
                "furigana",
                "tel",
                "address",
                "latitude",
                "longitude",
                "s_time",
                "e_time"
            );

        if($user->staff_role_id != config('constants.staff_roles.super_admin'))
        {
            $query->whereIn("id", $user->staffFields->pluck('field_id')->toArray());
        }

        if(isset($params["name"])){
            $query->where(function($query) use ($params) {
                $query->where("name", "like", "%{$params['name']}%")
                    ->orWhere("furigana", "like", "%{$params['name']}%");
            });
        }

        $limit = $params['limit'];
        $records = $query->paginate($limit);

        return $records;
    }

    public function getPostInfoList($params)
    {
        $query = DB::table("t_post")
            ->select(
                "id",
                "p_week",
                "s_date",
                "e_date"
            )
            ->where("field_id", $params["field_id"])
            ->orderBy("p_week")
            ->orderBy("s_date");

        $limit = $params['limit'];
        $records = $query->paginate($limit);

        return $records;
    }

    public function getPostInfo($params)
    {
        if(isset($params["post_id"])){
            $data = app(Post::class)
                ->with("postTimes")
                ->find($params["post_id"]);
            $selected_date_list = DB::table("t_post")
                ->select(
                    "id",
                    "s_date",
                    "e_date"
                )
                ->where("field_id", $params["field_id"])
                ->whereNull("p_week")
                ->where("id","<>" ,$params["post_id"])
                ->orderBy("s_date")
                ->get();
        }
        else{
            $data = (object) array();
            $selected_date_list = DB::table("t_post")
                ->select(
                    "id",
                    "s_date",
                    "e_date"
                )
                ->where("field_id", $params["field_id"])
                ->whereNull("p_week")
                ->orderBy("s_date")
                ->get();
        }

        $data->selected_date_list = $selected_date_list;

        return $data;
    }

    public function addPost($params)
    {
        $posts = array();
        if(isset($params["p_weeks"])){
            foreach ($params["p_weeks"] as $week){
                $posts[] = Post::updateOrCreate(
                    [
                        "field_id" => $params["field_id"],
                        "p_week" => $week
                    ],
                    []
                );
            }
        }
        else if(isset($params["s_date"])){
            $post_date = new Post();
            $post_date->field_id = $params["field_id"];
            $post_date->s_date = $params["s_date"];
            $post_date->e_date = $params["e_date"];
            $post_date->save();
            $posts[] = $post_date;
        }

        $spacial_post_list = [];
        foreach ($params["special_date_list"] as $special_date){
            $spacial_post = new Post();
            $spacial_post->field_id = $params["field_id"];
            $spacial_post->s_date = $special_date["s_date"];
            $spacial_post->e_date = $special_date["e_date"];
            $spacial_post->save();
            $spacial_post_list[] = $spacial_post;
        }

        foreach ($params["post_times"] as $one){
            foreach ($posts as $post){
                PostTime::updateOrCreate(
                    [
                        "post_id" => $post->id,
                        "s_time" => $one["s_time"],
                        "e_time" => $one["e_time"]
                    ],
                    [
                        "number" => $one["number"]
                    ]
                );
            }

            foreach ($spacial_post_list as $spacial_post){
                $post_time = new PostTime();
                $post_time->post_id = $spacial_post->id;
                $post_time->s_time = $one["s_time"];
                $post_time->e_time = $one["e_time"];
                $post_time->number = $one["number"];
                $post_time->save();
            }
        }
    }

    public function updatePost($params)
    {
        $posts = array();
        if(isset($params["p_weeks"])){
            foreach ($params["p_weeks"] as $week){
                $posts[] = Post::updateOrCreate(
                    [
                        "field_id" => $params["field_id"],
                        "p_week" => $week
                    ],
                    []
                );
            }
        }
        else if(isset($params["s_date"])){
            $post = app(Post::class)->find($params["id"]);
            $post->field_id = $params["field_id"];
            $post->s_date = $params["s_date"];
            $post->e_date = $params["e_date"];
            $post->save();
            $posts[] = $post;
        }

        $spacial_post_list = [];
        foreach ($params["special_date_list"] as $special_date){
            $spacial_post = new Post();
            $spacial_post->field_id = $params["field_id"];
            $spacial_post->s_date = $special_date["s_date"];
            $spacial_post->e_date = $special_date["e_date"];
            $spacial_post->save();
            $spacial_post_list[] = $spacial_post;
        }

        foreach ($params["post_times"] as $one){
            foreach ($posts as $post){
                PostTime::updateOrCreate(
                    [
                        "post_id" => $post->id,
                        "s_time" => $one["s_time"],
                        "e_time" => $one["e_time"]
                    ],
                    [
                        "number" => $one["number"]
                    ]
                );
            }

            foreach ($spacial_post_list as $spacial_post){
                $post_time = new PostTime();
                $post_time->post_id = $spacial_post->id;
                $post_time->s_time = $one["s_time"];
                $post_time->e_time = $one["e_time"];
                $post_time->number = $one["number"];
                $post_time->save();
            }
        }
    }

    public function deletePosts($params)
    {
        app(Post::class)->whereIn("id", $params["post_ids"])->delete();
    }
}
