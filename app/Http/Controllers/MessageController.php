<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\PrivateMessageSent;
use App\Message;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function fetchMessages()
    {
        return Message::with('user')->get();
    }

    public function sendMessage(Request $request)
    {
        if(request()->has('file')){

            $image = request('file');

            $unique_str = Str::random(10);
            $ext= strtolower($image->getClientOriginalExtension());
            $image_name = $unique_str.'.'.$ext;
            $upload_path = 'chat/';
            $image_url = $upload_path.$image_name;
            $image->move($upload_path,$image_name);
            $message=Message::create([
                'user_id' => request()->user()->id,
                'image' => $image_url,
            ]);
        }else{
            $message = auth()->user()->messages()->create(['message' => $request->message]);

        }

        broadcast(new MessageSent(auth()->user(),$message->load('user')))->toOthers();
        
        return response(['status'=>'Message sent successfully','message'=>$message]);

    }

    public function privateMessages(User $user)
    {
        $privateCommunication= Message::with('user')
        ->where(['user_id'=> auth()->id(), 'receiver_id'=> $user->id])
        ->orWhere(function($query) use($user){
            $query->where(['user_id' => $user->id, 'receiver_id' => auth()->id()]);
        })
        ->get();

        return $privateCommunication;
    }

    public function sendPrivateMessage(Request $request,User $user)
    {
        $input= $request->all();
        $input['receiver_id']= $user->id;
        $message=auth()->user()->messages()->create($input);
        broadcast(new PrivateMessageSent($message->load('user')))->toOthers();
        return response(['status'=>'Message private sent successfully','message'=>$message]);

    }
}
