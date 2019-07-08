<?php

namespace App\Http\Requests\Api;

// FormRequest 使用 DingoApi 提供的基类
use Dingo\Api\Http\FormRequest as BaseFormRequest;

class FormRequest extends BaseFormRequest
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

}
