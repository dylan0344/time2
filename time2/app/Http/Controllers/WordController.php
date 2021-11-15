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
// loopen door de dagen 7 dagen in de week
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
     * Start tellen bij maandag op basis van datum
     * @param Carbon $date
     * @param $startDate
     * @param $endDate
     */
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

    public function generatePage($phpWord)
    {
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

        return response()->streamDownload(function () use ($objWriter) {
            echo $objWriter->save("php://output");
        }, 'UrenverantwoordingBPV.docx');
    }
}
