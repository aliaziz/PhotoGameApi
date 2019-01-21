<?php

namespace App\Http\Controllers;

use App\Photo;
use App\ScoreActivity;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Image;
use Response;

class MobileAPI extends Controller
{

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|exists:users,username'
        ]);

        if($validator->fails()){
            return response()->json(['status' => 'error', 'error' => ['code' => 'input_invalid', 'message' => $validator->errors()->all()]])->setStatusCode(422);
        }

        $username = $request->input('username');

        $credentials = [];
        $credentials['username'] = $username;
        $credentials['password'] = $username;

        try {
            if (Auth::attempt($credentials)) {
                $user = User::where('username', $username)->first();
                return response()->json([
                    'status' => 'success',
                    'response' =>
                        [
                            'user' => $user
                        ]

                ])->setStatusCode(200);
            }else{
                return response()->json(array('status' => 'error', 'message' => 'Incorrect credentials'));
            }

        } catch(\Exception $e){
            return response()->json(array('status' => 'error', 'message' => 'Incorrect credentials'));
        }
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username',
            'email' => 'required|unique:users,email'
        ]);

        if($validator->fails()){
            return response()->json(['status' => 'error', 'error' => ['code' => 'input_invalid', 'message' => $validator->errors()->all()]])->setStatusCode(422);
        }

        $username = $request->input('username');
        $email = $request->input('email');
        $token = bcrypt($email.$username);

        $user = new User();
        $user->username = $username;
        $user->password = bcrypt($username);
        $user->api_token = $token;
        $user->email = $email;
        $user->save();

        return response()->json([
            'status' => 'success',
            'response' =>
                [
                    'user' => $user
                ]

        ])->setStatusCode(200);
    }


    public function likePhoto(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:photos,id'
        ]);

        if($validator->fails()){
            return response()->json(['status' => 'error', 'error' => ['code' => 'input_invalid', 'message' => $validator->errors()->all()]])->setStatusCode(422);
        }

        $photoId = $request->input('id');
        $user = Auth::user();

        $like = ScoreActivity::where(['user_id'=>$user->id,'photo_id'=>$photoId,'activity'=>'LIKE'])->first();
        if(!$like){
            $photo = Photo::where(['id'=>$photoId])->first();
            $photo->likes = ($photo->likes) + 1;
            $photo->save();

            $photo->scoreActivity()->create([
                'user_id' => $user->id,
                'photo_id' => $photoId,
                'activity' => "LIKE"
            ]);

            return response()->json(['status'=>'success','message' => 'Photo liked successfully'], 200);
        }else{
            return response()->json(['status' => 'error', 'error' => ['code' => 'input_invalid', 'message' => "You have already liked this photo"]])->setStatusCode(422);
        }


    }

    public function dislikePhoto(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:photos,id'
        ]);

        if($validator->fails()){
            return response()->json(['status' => 'error', 'error' => ['code' => 'input_invalid', 'message' => $validator->errors()->all()]])->setStatusCode(422);
        }

        $photoId = $request->input('id');
        $user = Auth::user();

        //check if previously disliked
        $dislike = ScoreActivity::where(['user_id'=>$user->id,'photo_id'=>$photoId,'activity'=>'DISLIKE'])->first();
        if(!$dislike){
            $photo = Photo::where(['id'=>$photoId])->first();
            $photo->dislikes = ($photo->dislikes) + 1;
            $photo->save();

            $photo->scoreActivity()->create([
                'user_id' => $user->id,
                'photo_id' => $photoId,
                'activity' => "DISLIKE"
            ]);

            return response()->json(['status'=>'success','message' => 'Photo disliked successfully'], 200);
        }else{
            return response()->json(['status' => 'error', 'error' => ['code' => 'input_invalid', 'message' => "You have already disliked this photo"]])->setStatusCode(422);
        }

    }

    public function viewPhoto(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:photos,id'
        ]);

        if($validator->fails()){
            return response()->json(['status' => 'error', 'error' => ['code' => 'input_invalid', 'message' => $validator->errors()->all()]])->setStatusCode(422);
        }

        $photoId = $request->input('id');
        $user = Auth::user();

        $views = ScoreActivity::where(['user_id'=>$user->id,'photo_id'=>$photoId,'activity'=>'VIEW'])->first();
        if(!$views){
            $photo = Photo::where(['id'=>$photoId])->first();
            $photo->views = ($photo->views) + 1;
            $photo->save();

            $photo->scoreActivity()->create([
                'user_id' => $user->id,
                'photo_id' => $photoId,
                'activity' => "VIEW"
            ]);

            return response()->json(['status'=>'success','message' => 'Photo viewed successfully'], 200);
        }else{
            return response()->json(['status' => 'error', 'error' => ['code' => 'input_invalid', 'message' => "You have already disliked this viewed"]])->setStatusCode(422);
        }

    }

    public function allPhotos(){
        $photos = Photo::with('scoreActivity')->get();
        return response()->json([
            'status' => 'success',
            'response' =>
                [
                    'photos' => $photos
                ]
        ])->setStatusCode(200);
    }


    public function getLeadershipBoard(){
        $leadership = Photo::with(['scoreActivity'])
            ->orderBy('likes','ASC')
            ->get();
        return response()->json([
            'status' => 'success',
            'response' =>
                [
                    'leadership' => $leadership
                ]
        ])->setStatusCode(200);
    }


    public function uploadPhoto(Request $request){
        $validator = Validator::make($request->all(), [
            'caption' => 'required',
            'description' => 'required',
            'category' => 'required',
            'location' => 'required',
            'photo' => 'required'
        ]);

        if($validator->fails()){
            return response()->json(['status' => 'error', 'error' => ['code' => 'input_invalid', 'message' => $validator->errors()->all()]])->setStatusCode(422);
        }

        $user = Auth::user();

        if($request->input('photo')){
            $filename = $user->id.'-'.substr( md5( $user->id . time() ), 0, 15) . '.jpg';
            $img = Image::make(file_get_contents($request->input('photo')));


            $image = $request->input('photo');
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = md5( $user->id . time() ).'.'.'png';
            $path = storage_path(). '/app/public/photos/' . $imageName;
           // \File::put($path, base64_decode($image));
            //Storage::disk('local')->put($imageName, $image);

//            // save image
//            $img->save($path);
//
            $photo = new Photo();
            $photo->caption = $request->input('caption');
            $photo->description = $request->input('description');
            $photo->category = $request->input('category');
            $photo->location = $request->input('location');
            $photo->photo_url = "http://www.wrostdevelopers.com/africa.jpg";
            $photo->user_id = $user->id;
            $photo->save();

            return response()->json(['status'=>'success','message' => 'Photo uploaded successfully'], 200);
        }else{
            return response()->json(['status' => 'error', 'error' => ['code' => 'input_invalid', 'message' => "Photo not uploaded"]])->setStatusCode(422);
        }


    }

}
