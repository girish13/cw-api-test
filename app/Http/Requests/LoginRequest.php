<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class LoginRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ];
    }

     public function messages()
     {return[
        'email.required'=>'enter an email',
        'email.email'=>'enter valid email',
        'password.required'=>'enter password',
        'password.min' =>'enter valid password'  
    ];
    }

}
