<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;


class StoreTimeRequest extends FormRequest
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
            'start' => 'required|date',
            'end' => array(
                'bail',
                'required',
                'date',
                'after:start',
                function ($attribute, $value, $fail) {
                    //De startdatum ophalen en converteren naar een datetime
                    //De eind datum converteren naar datetime
                    //Vervolgens controleren of de datum gelijk aan elkaar zijn
                    $start = Carbon::create($this->get('start'));
                    $start->setTime(0,0,0);
                    $end = Carbon::create($value);
                    $end->setTime(0,0,0);

                    if (!$end->equalTo($start)) {
                        $fail($attribute . ' date is not equal to start date');
                    }
                },
            )

        ];
    }
}


