<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class RegisterRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'number' => 'required|numeric|digits:10|unique:users,number',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ];
    }
      public function messages()

     {
        return [
        'name.required'=>'Enter the right string',
        'number.required'=>'enter phone number',
        'email.required'=>'enter email address',
        'password.required'=>'enter a password',
        'number.unique'=>'enter another phone number',
        'email.unique'=>'enter another email',
        'password.min'=>'enter minimum 6 characters', 
        'number.numeric'=>'enter valid phone number',
        'email.email'=>'enter a valid email',
        'number.numeric'=>'enter valid phone number',
        'number.digits'=>'enter correct phone number'

       ];      

    }
    
}
