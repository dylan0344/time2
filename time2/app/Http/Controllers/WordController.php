<?php

namespace App\Http\Controllers;

use App\Models\Time;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\JcTable;

class WordController extends Controller
{
    const WEEKDAYS = ['Ma', 'Di', 'Wo', 'Do', 'Vr', 'Za', 'Zo'];
    private $fancyTableStyleName = 'Fancy Table';
    private $fancyTableStyle = array('borderSize' => 1,
        'borderColor' => '000000', 'line-Height' => null,
        'cellMargin' => 0,
        'alignment' => JcTable::CENTER);
    private $fancyTableFirstRowStyle = array('borderBottomSize' => 1,
        'borderBottomColor' => '000000',
        'bgColor' => 'ffffff');
    private $fancyTableCellStyle = array(
        'valign' => 'center',
        'space' => array('line' => 1000),
        'cellMarginTop' => 1000,
        'cellMarginLeft' => 1000,
        'cellMarginBottom' => 1000,
        'cellMarginRight' => 1000,);
    private $fancyTableFontStyle = array('bold' => true,
        'lineHeight' => 1);


    // generateDocx method: This method is responsible for generating the docx file.
    // It takes an instance of Illuminate\Http\Request as input, retrieves the start
    // and end dates from the request, and generates a table for each week between those dates.
    // Finally, it generates the page with the required signature lines and returns the generated file.
    public function generateDocx(Request $request)
    {
        $phpWord = new PhpWord();
        $phpWord->addTableStyle($this->fancyTableStyleName, $this->fancyTableStyle, $this->fancyTableFirstRowStyle);
        $section = $phpWord->addSection();
        $section->addText('ROC Rivor AO', array('size' => 11));
        $section->addTextBreak(1);
        $section->addText('Urenverantwoording van: Dylan Jeddi van Hattem',);

        $startDate = Carbon::createFromFormat("Y-m-d", $request->input('startDate'));
        if (!$startDate->isMonday()) {
            $startDate->startOfWeek();
        }

        $endDate = Carbon::createFromFormat("Y-m-d", $request->input('endDate'));
        if (!$endDate->isSunday()) {
            $endDate->endOfWeek();
        }

        $weeks = $startDate->diffInWeeks($endDate);
        $weeks++;
        if ($weeks < 1) {
            $weeks = 1;
        }

        for ($i = 0; $i < $weeks; $i++) {
            $section = $this->generateTable($section, $startDate, $endDate);
            $section->addTextBreak(1);
        }

        $section->addText('Datum:', array('size' => 11));
        $section->addTextBreak(1);
        $section->addText('Naam praktijkopleider:', array('size' => 11));
        $section->addTextBreak(1);
        $section->addText('Handtekening praktijkopleider:', array('size' => 11));
        return $this->generatePage($phpWord);
    }

    /**
     * @param $section
     * @param Carbon $startDate
     * @return mixed
     */

    // This method generates a table for a given week. It takes a Carbon object as input, which represents
    // the start date of the week. It creates a new table with the style defined in the class properties
    // and adds a row to display the week number and the total worked hours for that week. Then, it adds
    // a row for each day of the week, displaying the day of the week, the start and end times, and the
    // total worked time.
    public function generateTable($section, Carbon $startDate)
    {
        $noSpace = array('spaceAfter' => 0);
        $table = $section->addTable($this->fancyTableStyleName);
        $table->addRow();
        $table->addCell(2000, $this->fancyTableCellStyle)->addText('Week' . $startDate->weekOfYear, $this->fancyTableFontStyle, $noSpace,);
        $table->addCell(500, $this->fancyTableCellStyle)->addText('Vrij', $this->fancyTableFontStyle, $noSpace);
        $table->addCell(500, $this->fancyTableCellStyle)->addText('Werk', $this->fancyTableFontStyle, $noSpace);
        $table->addCell(2000, $this->fancyTableCellStyle)->addText('Van', $this->fancyTableFontStyle, $noSpace);
        $table->addCell(2000, $this->fancyTableCellStyle)->addText('Tot', $this->fancyTableFontStyle, $noSpace);
        $table->addCell(2000, $this->fancyTableCellStyle)->addText('Totaal gewerkt:' . $this->getTotalTime($startDate), $this->fancyTableFontStyle, $noSpace);

        // For each iteration, the code initializes four variables to null: $start, $end, $workedTime, and $vrij.
        //
        // It then queries the database using the Eloquent ORM in Laravel to find a record in the "Time" table where the "start"
        // date matches a given date ($startDate). If a record is found, it sets the values of $start, $end, and $workedTime
        // based on the corresponding fields in the database record, and sets $vrij to null. If no record is found, $vrij remains set to 'X'.
        //
        // The "->format('H:i')" method is used to format the "start" and "end" fields as strings representing the hours and minutes in 24-hour format (e.g. "13:45").
        for ($i = 0; $i < 7; $i++) {
            $start = null;
            $end = null;
            $workedTime = null;
            $vrij = ('X');

            $time = Time::whereDate('start', '=', $startDate->format('Y-m-d'))->first();
            if (!is_null($time)) {
                $start = $time->start->format('H:i');
                $end = $time->end->format('H:i');
                $workedTime = $time->getDateDiff();
                $vrij = null;

            }

            $table->addRow();
            $table->addCell(2000)->addText(self::WEEKDAYS[$i] . $startDate->format('m-d'), null, $noSpace);
            $table->addCell(500)->addText($vrij, null, $noSpace);
            $table->addCell(500)->addText(null, null, $noSpace);
            $table->addCell(2000)->addText($start, null, $noSpace);
            $table->addCell(2000)->addText($end, null, $noSpace);
            $table->addCell(2000)->addText($workedTime, null, $noSpace);

            $startDate->addDay();
        }

        return $section;
    }

    /**
     * @param Carbon $date
     * @param $startDate
     * @param $endDate
     */

    // This code calculates the total worked time between Monday and Sunday of a given week.
    // It first calculates the start and end dates (Monday and Sunday) based on the input date,
    // and then retrieves all the time entries that fall within that week.
    // It then loops through those time entries and calculates the total worked hours by adding up
    // the hour and minute components of each entry. Finally, it formats the total worked hours into
    // a string in the format "hh:mm" and returns it.
    private function getTotalTime(Carbon $date)
    {
        $monday = $date->copy();
        $sunday = $date->copy()->addDays(6);
        $workedHours = 0;
        $minuteConverter = 1 / 60; // 1 == 60 minutes, 0,5 == 30 minutes, 0,25 == 15 minutes, 0,0166666667 == 1 minute

        $dates = Time::whereDate('start', '>=', $monday->format('Y-m-d'))->whereDate('start', '<=', $sunday->format('Y-m-d'))->get();
        foreach ($dates as $date) {
            $dateDiff = $date->getHours();
            $workedHours += $dateDiff->format('%H');
            $workedHours += $dateDiff->format('%I') * $minuteConverter;
        }

// Get the hours
        $hours = floor($workedHours);
        $minutes = ($workedHours - $hours) / $minuteConverter;
        $minutes = round($minutes, 2);
        $time = $hours . ':' . str_pad($minutes, 2, 0, STR_PAD_LEFT);

        return $time;
    }
//  Generates the word document to the browser :)
    public function generatePage($phpWord)
    {
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

        return response()->streamDownload(function () use ($objWriter) {
            echo $objWriter->save("php://output");
        }, 'UrenverantwoordingBPV.docx');
    }
}
