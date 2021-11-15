<?php

namespace App\Models;


use Carbon\Carbon;
use DateInterval;
use Illuminate\Database\Eloquent\Model;

class Time extends Model
{
    protected $fillable = [
        'start',
        'end',


    ];


    protected $dates = [
        'start',
        'end',
    ];


    public function orderBy() {
        return $this->orderby('start');
    }

    public function getDateDiff()
    {
        return $this->getHours()->format('%H:%I');

    }

    public function getHours()
    {
        $tosub = new DateInterval('PT30M');
        $store = $this->end->sub($tosub);

        return $this->start->diff($store);
    }

    public function getWeekNumberAttribute()
    {

    return $this->start->format('W');

    }



}
