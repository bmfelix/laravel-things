<?php

namespace App\Http\Controllers;

use DB;
use Helper;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Qs36f\{
    LaborComp,
    LaborReportSummaryDaily as LabRptSumD,
    GenProTimeRecordUpload as TimRecGup,
    GenProTimeRecordSummary as TimRecSum,
    DpTempRsts,
    LostPerson
};

/**
 * Labor Report Controller
 *
 * @author Brad Felix <bfelix@custom-aluminum.com>
 */
class LaborReportController extends Controller
{

    private $helper;
    private $goals, $months, $monthStart, $monthEnd, $dayStart, $dayEnd,
        $departmentOnlyMode, $departmentOnlyPlant, $library;

    public function __construct()
    {
        // $this->goals = Helper::getDailyLaborGoals();
        // $this->months = Helper::getDailyLaborMonths();
        // $this->library =  Helper::getCommonLibrary();
        // $this->departmentOnlyMode = null;
        // $this->departmentOnlyPlant = null;
    }

    /**
     * index
     *
     * @param string $view
     * @return void
     */
    public function index(string $view = 'download')
    {
        try {
            if ($view === 'download') {
                $this->populateFiles();
            } else {
                $this->displayData();
            }
        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }
    }

    /**
     * displayData
     *
     * @return void
     */
    private function displayData(): void
    {

    }

    /**
     * populateFiles
     *
     * @return void
     */
    private function populateFiles(): void
    {
        try {
            if ($this->departmentOnlyMode && $this->departmentOnlyPlant) {
                echo "Department Only Mode Active";
            }

            $this->setStartDate(15);
            $this->setEndDate(1);

            //echo 12/0;
            //$this->clearPreviousRecords();
            //$this->loadTimes();
            $this->loadProduction();

        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }
    }

    /**
     * checkInsert
     *
     * @param TimRecGup $timeClockRecord
     * @param Object $locationData
     * @param float $tempRate
     * @param string $code
     * @return void
     */
    private function checkInsert(
        TimRecGup $timeClockRecord,
        Object $locationData = null,
        float $tempRate,
        string $code
    ) {
        try {
            $recordDate = Carbon::parse($timeClockRecord->tmu_cyrdat)->setTimezone('America/Chicago');

            if ($locationData) {
                $query = new LaborComp();
                $query->tmu_empno = $timeClockRecord->tmu_empno;
                $query->tmu_temp = $timeClockRecord->temp;
                $query->tmu_rate = $tempRate;
                $query->tmu_cyrdat = $recordDate->format('Ymd');
                $query->tmu_dptloc = $locationData->tms_dptloc;
                $query->tmu_dptabv = $locationData->tms_dptabv;
                $query->tmu_dptplt = $locationData->tms_dptplt;
                $query->tmu_payhrs = $timeClockRecord->payhours;
                $query->tmu_ovrtim = $timeClockRecord->overtime;
                $query->tmu_catcde = $code;
            } else {
                $query = new LaborComp();
                $query->tmu_empno = $timeClockRecord->tmu_empno;
                $query->tmu_temp = $timeClockRecord->temp;
                $query->tmu_rate = $tempRate;
                $query->tmu_cyrdat = $recordDate->format('Ymd');
                $query->tmu_dptloc = $timeClockRecord->tmu_dptloc;
                $query->tmu_dptabv = $timeClockRecord->tmu_dptabv;
                $query->tmu_dptplt = $timeClockRecord->tmu_dptplt;
                $query->tmu_payhrs = $timeClockRecord->payhours;
                $query->tmu_ovrtim = $timeClockRecord->overtime;
                $query->tmu_catcde = $code;
            }

            return $query->save();

        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }
    }

    /**
     * clearPreviousRecords
     *
     * @return void
     */
    private function clearPreviousRecords(): void
    {
        try {
            echo '<p>*** Clearing Dates ' . $this->dayStart->format('Y-m-d') . ' - ' . $this->dayEnd->format('Y-m-d') . ' *** <br />';

            if ($this->departmentOnlyMode) {
                $this->clearPreviousRecordsMode();
            } else {
                $this->clearPreviousRecordsNonMode();
            }

            echo '*** Done Clearing Dates *** </p>';
        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }
    }

    /**
     * clearPreviousRecordsMode
     *
     * @return void
     */
    private function clearPreviousRecordsMode(): void
    {
        try {
            //clear summary table
            $query = DB::table($this->library . '.LABRPTSUMD')
                ->where('LRD_CYRDAT', '>=', $this->dayStart->format('ymd'))
                ->where('LRD_CYRDAT', '<=', $this->dayEnd->format('ymd'))
                ->where('LRD_DPTABV', $this->departmentOnlyMode);

            if ($this->departmentOnlyPlant) {
                $query = DB::table($this->library . '.LABRPTSUMD')
                    ->where('LRD_CYRDAT', '>=', $this->dayStart->format('ymd'))
                    ->where('LRD_CYRDAT', '<=', $this->dayEnd->format('ymd'))
                    ->where('LRD_DPTABV', $this->departmentOnlyMode)
                    ->where('LRD_DPTPLT', $this->departmentOnlyPlant);
            }

            $this->deleteRecord($query);

            //clear time record upload
            $query = DB::table($this->library . '.LABORCOMP')
                ->where('TMU_CYRDAT', '>=', $this->dayStart->format('ymd'))
                ->where('TMU_CYRDAT', '<=', $this->dayEnd->format('ymd'))
                ->where('TMU_DPTABV', $this->departmentOnlyMode);

            $this->deleteRecord($query);
        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }
    }

    /**
     * clearPreviousRecordsNonMode
     *
     * @return void
     */
    private function clearPreviousRecordsNonMode(): void
    {
        try {
            //clear summary table
            $query = DB::table($this->library . '.LABRPTSUMD')
                ->where('LRD_CYRDAT', '>=', $this->dayStart->format('ymd'))
                ->where('LRD_CYRDAT', '<=', $this->dayEnd->format('ymd'));

            $this->deleteRecord($query);

            //clear time record upload
            $query = DB::table($this->library . '.LABORCOMP')
                ->where('TMU_CYRDAT', '>=', $this->dayStart->format('ymd'))
                ->where('TMU_CYRDAT', '<=', $this->dayEnd->format('ymd'));

            $this->deleteRecord($query);
        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }
    }

    /**
     * dayExists
     *
     * @param Collection $dayData
     * @param TimRecGup $timeClockRecord
     * @param Object $locationData
     * @param float $tempRate
     * @return void
     */
    private function dayExists(
        Collection $dayData = null,
        TimRecGup $timeClockRecord,
        Object $locationData = null,
        float $tempRate
    ) {
        try {
            $recordDate = Carbon::parse($timeClockRecord->tmu_cyrdat)->setTimezone('America/Chicago');

            if ($locationData) {
                if ($dayData) {
                    if ($timeClockRecord->temp == 'Y') {
                        $dayData->lrd_tmplrr = number_format($dayData->lrd_tmplrr + $timeClockRecord->payhours, 2);
                        $dayData->lrd_tmplbr = number_format($dayData->lrd_tmplbr + ($timeClockRecord->payhours * $tempRate), 2, '.', '');
                        $dayData->lrd_ottlrr = number_format($dayData->lrd_ottlrr + $timeClockRecord->overtime, 2);
                        $dayData->lrd_ottlbr = number_format($dayData->lrd_ottlbr + ($timeClockRecord->overtime * ($tempRate * 1.5)), 2);
                    } else {
                        $dayData->lrd_reglrr = number_format($dayData->lrd_reglrr + $timeClockRecord->payhours, 2);
                        $dayData->lrd_reglbr = number_format($dayData->lrd_reglbr + ($timeClockRecord->payhours * $tempRate), 2, '.', '');
                        $dayData->lrd_ovtlrr = number_format($dayData->lrd_ovtlrr + $timeClockRecord->overtime, 2);
                        $dayData->lrd_ovtlbr = number_format($dayData->lrd_ovtlbr + ($timeClockRecord->overtime * ($tempRate * 1.5)), 2, '.', '');
                    }

                    $result = $this->processQuery($dayData, 'update');
                } else {
                    if ($timeClockRecord->temp == 'Y') {
                        $query = new LabRptSumD();
                        $query->lrd_dptloc = $locationData->tms_dptloc;
                        $query->lrd_dptabv = $locationData->tms_dptabv;
                        $query->lrd_dptplt = $locationData->tms_dptplt;
                        $query->lrd_cyrdat = $recordDate->format('ymd');
                        $query->lrd_tmplrr = number_format($timeClockRecord->payhours, 2);
                        $query->lrd_tmplbr = number_format($timeClockRecord->payhours * $tempRate, 2, '.', '');
                        $query->lrd_ottlrr = number_format($timeClockRecord->overtime, 2);
                        $query->lrd_ottlbr = number_format($timeClockRecord->overtime * ($tempRate * 1.5), 2, '.', '');
                    } else {
                        $query = new LabRptSumD();
                        $query->lrd_dptloc = $locationData->tms_dptloc;
                        $query->lrd_dptabv = $locationData->tms_dptabv;
                        $query->lrd_dptplt = $locationData->tms_dptplt;
                        $query->lrd_cyrdat = $recordDate->format('ymd');
                        $query->lrd_reglrr = number_format($timeClockRecord->payhours, 2);
                        $query->lrd_reglbr = number_format($timeClockRecord->payhours * $tempRate, 2, '.', '');
                        $query->lrd_ovtlrr = number_format($timeClockRecord->overtime, 2);
                        $query->lrd_ovtlbr = number_format($timeClockRecord->overtime * ($tempRate * 1.5), 2, '.', '');
                    }
                    $result = $query->save();
                    if (!$result) {
                        throw new \Exception('Could not insert a record: ' . $query->toSql() . '<br />' . $query->getBindings());
                    }
                }
            } else {
                if ($dayData) {
                    if ($timeClockRecord->temp == 'Y') {
                        $dayData->lrd_tmplrr =  number_format($dayData->lrd_tmplrr + $timeClockRecord->payhours, 2);
                        $dayData->lrd_tmplbr =  number_format($dayData->lrd_tmplbr + ($timeClockRecord->payhours * $tempRate), 2, '.', '');
                        $dayData->lrd_ottlrr =  number_format($dayData->lrd_ottlrr + $timeClockRecord->overtime, 2);
                        $dayData->lrd_ottlbr =  number_format($dayData->lrd_ottlbr + ($timeClockRecord->overtime * ($tempRate * 1.5)), 2, '.', '');
                    } else {
                        $dayData->lrd_reglrr =  number_format($dayData->lrd_reglrr + $timeClockRecord->payhours, 2);
                        $dayData->lrd_reglbr =  number_format($dayData->lrd_reglbr + ($timeClockRecord->payhours * $tempRate), 2, '.', '');
                        $dayData->lrd_ovtlrr =  number_format($dayData->lrd_ovtlrr + $timeClockRecord->overtime, 2);
                        $dayData->lrd_ovtlbr =  number_format($dayData->lrd_ovtlbr + ($timeClockRecord->overtime * ($tempRate * 1.5)), 2, '.', '');
                    }
                    $result = $this->processQuery($dayData, 'update');
                } else {
                    if (!isset($timeClockRecord->tmu_dptplt)) {
                        $timeClockRecord->tmu_dptplt = 0;
                    }

                    if ($timeClockRecord->temp == 'Y') {
                        $query = new LabRptSumD();
                        $query->lrd_dptloc = $timeClockRecord->tmu_dptloc;
                        $query->lrd_dptabv = $timeClockRecord->tmu_dptabv;
                        $query->lrd_dptplt = $timeClockRecord->tmu_dptplt;
                        $query->lrd_cyrdat = $recordDate->format('ymd');
                        $query->lrd_tmplrr =  number_format($timeClockRecord->payhours, 2);
                        $query->lrd_tmplbr =  number_format($timeClockRecord->payhours * $tempRate, 2, '.', '');
                        $query->lrd_ottlrr =  number_format($timeClockRecord->overtime, 2);
                        $query->lrd_ottlbr =  number_format($timeClockRecord->overtime * ($tempRate * 1.5), 2, '.', '');
                    } else {
                        $query = new LabRptSumD();
                        $query->lrd_dptloc = $timeClockRecord->tmu_dptloc;
                        $query->lrd_dptabv = $timeClockRecord->tmu_dptabv;
                        $query->lrd_dptplt = $timeClockRecord->tmu_dptplt;
                        $query->lrd_cyrdat = $recordDate->format('ymd');
                        $query->lrd_reglrr =  number_format($timeClockRecord->payhours, 2);
                        $query->lrd_reglbr =  number_format($timeClockRecord->payhours * $tempRate, 2, '.', '');
                        $query->lrd_ovtlrr =  number_format($timeClockRecord->overtime, 2);
                        $query->lrd_ovtlbr =  number_format($timeClockRecord->overtime * ($tempRate * 1.5), 2, '.', '');
                    }

                    $result = $query->save();
                    if (!$result) {
                        throw new \Exception('Could not insert a record: ' . $query->toSql() . '<br />' . $query->getBindings());
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }
    }

    /**
     * deleteRecord
     *
     * @param Object $record
     * @return void
     */
    private function deleteRecord(Builder $record)
    {
        try {
            $result = null;

            if ($record->first()) {
                $result = $record->delete();

                if (!$result) {
                    throw new \Exception('Could not delete record. ' . json_encode($record));
                }
            }

            return $result;
        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }
    }

    /**
     * foundOnRoster
     *
     * @param DpTempRsts $roster
     * @param TimRecGup $timeClockRecord
     * @return void
     */
    private function foundOnRoster(
        DpTempRsts $roster = null,
        TimRecGup $timeClockRecord = null
    ) {
        if ($roster !== null) {
            $this->processRoster($roster, $timeClockRecord);
        } else {
            $this->processHomeless($timeClockRecord);
        }
    }

    /**
     * insertLostEmployee
     *
     * @param TimRecGup $timeClockRecord
     * @return void
     */
    private function insertLostEmployee(TimRecGup $timeClockRecord)
    {
        $query = new LostPerson();
        $query->lsp_empno = $timeClockRecord->tmu_empno;
        $query->lsp_cyrdat = Carbon::parse($timeClockRecord->tmu_cyrdat)->setTimezone('America/Chicago')->format('ymd');
        $query->lsp_dptloc = $timeClockRecord->tmu_dptloc;
        $query->lsp_dptabv = $timeClockRecord->tmu_dptabv;
        $query->lsp_dptplt = $timeClockRecord->tmu_dptplt;
        return $query->save();
    }

    /**
     * isEmployeeLost
     *
     * @param string $abv
     * @return boolean
     */
    private function isEmployeeLost(string $abv): bool
    {
        $lost = false;
        switch (strtoupper($abv)) {
            case 'MNT':
            case 'TR ':
            case 'FRK':
            case 'OFC':
            case 'PRC':
            case 'QC ':
            case 'JAN':
            case 'DIE':
            case 'GP ':
            case 'SHP':
            case 'PCK':
            case 'PNT':
            case 'ANO':
                $lost = false;
                break;

            default:
                $lost = true;
                break;
        }

        return $lost;
    }

    private function loadProduction()
    {
        if ($this->departmentOnlyMode) {
            if ($this->deprtmentOnlyPlant) {
                $query = LabRptSumD::where('LRD_CYRDAT', '>=', $this->dayStart->format('ymd'))
                    ->where('LRD_CYRDAT', '<=', $this->dayEnd->format('ymd'))
                    ->where('LRD_DPTABV', $this->departmentOnlyMode)
                    ->where('LRD_DPTPLT', $this->deprtmentOnlyPlant)
                    ->orderBy('LRD_DPTLOC', 'asc')
                    ->orderBy('LRD_DPTABV', 'asc')
                    ->orderBy('LRD_DPTPLT', 'asc')
                    ->get();
            } else {
                $query = LabRptSumD::where('LRD_CYRDAT', '>=', $this->dayStart->format('ymd'))
                    ->where('LRD_CYRDAT', '<=', $this->dayEnd->format('ymd'))
                    ->where('LRD_DPTABV', $this->departmentOnlyMode)
                    ->orderBy('LRD_DPTLOC', 'asc')
                    ->orderBy('LRD_DPTABV', 'asc')
                    ->orderBy('LRD_DPTPLT', 'asc')
                    ->get();
            }
        } else {
            $query = LabRptSumD::where('LRD_CYRDAT', '>=', $this->dayStart->format('ymd'))
                ->where('LRD_CYRDAT', '<=', $this->dayEnd->format('ymd'))
                ->orderBy('LRD_DPTLOC', 'asc')
                ->orderBy('LRD_DPTABV', 'asc')
                ->orderBy('LRD_DPTPLT', 'asc')
                ->get();
        }

        $this->loopDays($query);
    }

    private function loopDays(Collection $days)
    {
        foreach ($days as $day) {
            $tLoc = $day->lrd_dptloc;
            $tDpt = $day->lrd_dptabv;
            $tPlt = $day->lrd_dptplt;
            $tDate = $day->lrd_cyrdat;

            if (
                (
                    $day->lrd_dptabv == 'EXT' ||
                    $day->lrd_dptabv == 'FAB' ||
                    $day->lrd_dptabv == 'RLF' ||
                    $day->lrd_dptabv == 'PNT'
                ) && $day->lrd_dptplt == 0) {
                continue;
            }

            switch($day->lrd_dptabv) {
                case 'EXT':
                   // $this->prodExt($day);
                    break;

                case 'FAB':
                    $this->prodFab($day);
                    break;

                case 'PNT':
                   // $this->prodPnt();
                    break;

                case 'ANO':
                    //$this->prodAno();
                    break;

                case 'PCK':
                    //$this->prodPck();
                    break;

                case 'SHP':
                    //$this->prodShp();
                    break;
            }
        }
    }

    /**
     * loadTimes
     *
     * @return void
     */
    private function loadTimes()
    {
        try {
            echo "<p>*** Loading Times *** <br />";
            if ($this->departmentOnlyMode) {
                $this->loadTimesMode();
            } else {
                $this->loadTimesNonMode();
            }

            echo "*** Done Loading Times ***</p>";
        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }
    }

    /**
     * loadTimesMode
     *
     * @return void
     */
    private function loadTimesMode()
    {
        try {
            $query = TimRecGup::select(
                'TMU_CYRDAT',
                'TMU_EMPNO',
                'TMU_DPTLOC',
                'TMU_DPTABV',
                DB::raw('sum(TMU_PAYHRS) as payhours'),
                DB::raw('sum(TMU_OVRTIM) as overtime'),
                DB::raw('max(TMU_RATE) as rate'),
                DB::raw('max(TMU_TEMP) as temp')
            )
                ->whereBetween('TMU_CYRDAT', [$this->dayStart->format('Ymd'), $this->dayEnd->format('Ymd')])
                ->where('TMU_DPTABV', $this->departmentOnlyMode)
                ->whereIn('TMU_DPTLOC', [1, 2])
                ->groupBy('TMU_CYRDAT', 'TMU_EMPNO', 'TMU_DPTLOC', 'TMU_DPTABV')
                ->orderBy('TMU_CYRDAT', 'ASC')
                ->orderBy('TMU_EMPNO', 'ASC');

            $data = $query->get();
            foreach ($data as $d) {
                $this->processByEmployee($d);
            }
        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }
    }

    /**
     * loadTimesNonMode
     *
     * @return void
     */
    private function loadTimesNonMode()
    {
        try {
            $query = TimRecGup::select(
                'TMU_CYRDAT',
                'TMU_EMPNO',
                'TMU_DPTLOC',
                'TMU_DPTABV',
                DB::raw('sum(TMU_PAYHRS) as payhours'),
                DB::raw('sum(TMU_OVRTIM) as overtime'),
                DB::raw('max(TMU_RATE) as rate'),
                DB::raw('max(TMU_TEMP) as temp')
            )
                ->whereBetween('TMU_CYRDAT', [$this->dayStart->format('Ymd'), $this->dayEnd->format('Ymd')])
                ->where('TMU_DPTABV', '!=', '')
                ->whereIn('TMU_DPTLOC', [1, 2])
                ->groupBy('TMU_CYRDAT', 'TMU_EMPNO', 'TMU_DPTLOC', 'TMU_DPTABV')
                ->orderBy('TMU_CYRDAT', 'ASC')
                ->orderBy('TMU_EMPNO', 'ASC');

            $data = $query->get();

            foreach ($data as $d) {
                $this->processByEmployee($d);
            }
        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }
    }

    /**
     * processByEmployee
     *
     * @param TimRecGup $timeClockRecord
     * @return void
     */
    private function processByEmployee(TimRecGup $timeClockRecord)
    {
        try {
            $recordDate = Carbon::parse($timeClockRecord->tmu_cyrdat)->setTimezone('America/Chicago');

            if ($timeClockRecord->tmu_dptabv == '   ') {
                $timeClockRecord->tmu_dptabv = 'GP ';
            }

            $selectLocation = TimRecSum::select(
                'TMS_DPTLOC',
                'TMS_DPTABV',
                'TMS_DPTPLT',
                'TMS_SHIFT'
            )
                ->where('TMS_EMPNO', $timeClockRecord->tmu_empno)
                ->where('TMS_CYRDAT', $recordDate->format('Ymd'))
                ->orderBy('TMS_HRS', 'DESC');

            $data = $selectLocation->first();

            if ($data) {
                $this->swipeHoursFound($data, $timeClockRecord);
            } else {
                $this->swipeHoursNotFound($timeClockRecord);
            }
        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }
    }

    /**
     * processHomeless
     *
     * @param TimRecGup $timeClockRecord
     * @return void
     */
    private function processHomeless(TimRecGup $timeClockRecord)
    {
        $recordDate = Carbon::parse($timeClockRecord->tmu_cyrdat)->setTimezone('America/Chicago');
        $tempRate = $timeClockRecord->rate;

        if ($recordDate->format('ymd') <= 160931 && $timeClockRecord->temp == 'Y') {
            if ($timeClockRecord->tmu_dptloc == 1) {
                $tempRate = $tempRate + ($tempRate * .23);
            } else {
                $tempRate = $tempRate + ($tempRate * .04);
            }
        }

        if ($timeClockRecord->payhours == 0 && $timeClockRecord->overtime == 0) {
            return;
        }

        if ($timeClockRecord->tmu_dptabv == 'ANO') {
            $timeClockRecord->tmu_dptplt = 1;
        }

        if ($timeClockRecord->tmu_dptabv == 'EXT') {
            $timeClockRecord->tmu_dptplt = 0;
        }

        if ($timeClockRecord->tmu_dptabv == 'GP ') {
            if ($timeClockRecord->tmu_dptloc == 1) {
                $timeClockRecord->tmu_dptplt = 1;
            } else {
                $timeClockRecord->tmu_dptplt = 5;
            }
        }

        if (!$timeClockRecord->tmu_dptplt) {
            $timeClockRecord->tmu_dptplt = 0;
        }

        if ($this->isEmployeeLost($timeClockRecord->tmu_dptabv)) {
            $this->insertLostEmployee($timeClockRecord);
        }

        $selectDay = LabRptSumD::where('LRD_DPTLOC', $timeClockRecord->tms_dptloc)
            ->where('LRD_DPTABV', $timeClockRecord->tms_dptabv)
            ->where('LRD_DPTPLT', $timeClockRecord->tmu_dptplt)
            ->where('LRD_CYRDAT', $recordDate->format('ymd'))
            ->first();

        if (!$selectDay) {
            $this->dayExists($selectDay, $timeClockRecord, null, $tempRate);
        } else {
            $this->dayExists(null, $timeClockRecord, null, $tempRate);
        }

        $this->checkInsert($timeClockRecord, null, $tempRate, 'GEN');
    }

     /**
     * processQuery
     *
     * @param Collection $query
     * @param string $type
     * @return void
     */
    private function processQuery(Collection $query, string $type)
    {
        try {
            if ($type === 'update') {
                $result = $query->update();
                if (!$result) {
                    throw new \Exception('Could not insert a record: ' . $query->toSql() . '<br />' . $query->getBindings());
                }
            }
        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }
    }

    /**
     * processRoster
     *
     * @param DpTempRsts $roster
     * @param TimRecGup $timeClockRecord
     * @return void
     */
    private function processRoster(DpTempRsts $roster, TimRecGup $timeClockRecord)
    {

        $recordDate = Carbon::parse($timeClockRecord->tmu_cyrdat)->setTimezone('America/Chicago');
        $tempRate = $timeClockRecord->rate;

        if ($recordDate->format('ymd') <= 160931 && $timeClockRecord->temp == 'Y') {
            if ($roster->tms_dptloc == 1) {
                $tempRate = $tempRate + ($tempRate * .23);
            } else {
                $tempRate = $tempRate + ($tempRate * .04);
            }
        }

        if ($roster->tms_shift == 2) {
            $tempRate = $tempRate + .20;
        } else if ($roster->tms_shift == 3) {
            $tempRate = $tempRate + .35;
        }

        $selectDay = LabRptSumD::where('LRD_DPTLOC', $roster->tms_dptloc)
            ->where('LRD_DPTABV', $roster->tms_dptabv)
            ->where('LRD_DPTPLT', $roster->tms_dptplt)
            ->where('LRD_CYRDAT', $recordDate->format('ymd'))
            ->first();

        if (!$selectDay) {
            $result = $this->dayExists($selectDay, $timeClockRecord, $roster, $tempRate);
        } else {
            $result = $this->dayExists(null, $timeClockRecord, $roster, $tempRate);
        }

        $this->checkInsert($timeClockRecord, $roster, $tempRate, 'ROST');
    }

    private function prodAno(LabRptSumD $day)
    {

    }

    private function prodExt(LabRptSumD $day)
    {
        $query = LabRptSumD::where('LRD_DPTLOC', $day->lrd_dptloc)
            ->whereIn('LRD_DPTABV', [$day->lrd_dptabv, 'RLF'])
            ->where('LRD_DPTPLT', $day->lrd_dptplt)
            ->where('LRD_CYRDAT', $day->lrd_cyrdat)
            ->first();

        if ($query) {
            $query->lrd_netlbs = Helper::getExtrusionNetPounds($day->lrd_cyrdat, 0, $day->lrd_dptloc, $day->lrd_dptplt, 0);
            $query->lrd_grslbs = Helper::getExtrusionGrossPounds($day->lrd_cyrdat, 0, $day->lrd_dptloc, $day->lrd_dptplt, 0);
            $query->update();
        }
    }

    private function prodFab(LabRptSumD $day)
    {

        $recordDate = Carbon::parse($day->lrd_cyrdat)->setTimezone('America/Chicago');

        // $tLoc = $dayRow['LRD_DPTLOC'];
		// $tDpt = $dayRow['LRD_DPTABV'];
		// $tPlt = $dayRow['LRD_DPTPLT'];
		// $tDate = $dayRow['LRD_CYRDAT'];
        if ($day->lrd_dptplt == 13) {
            $revenue = Helper::getJambSillRevenue($day->lrd_cyrdat);
        } else {
            $revenue = Helper::getFabricationRevenue($recordDate->format('Ymd'), 0, $day->lrd_dptplt, 0, false);
        }

        // if ($tPlt == 12 && $tLoc == 1)
        //     $rev = 0;
        // $update = "UPDATE " . $library . "/LABRPTSUMD"
        //     . " SET LRD_REVENU = " . number_format((float)$rev['Total Revenue'], 2, '.', '')
        //     . " WHERE LRD_DPTLOC = " . $tLoc
        //     . " AND LRD_DPTABV = '" . $tDpt . "'"
        //     . " AND LRD_DPTPLT = " . $tPlt
		// 		. " AND LRD_CYRDAT = " . $tDate;
    }

    private function prodPck(LabRptSumD $day)
    {

    }

    private function prodPnt(LabRptSumD $day)
    {

    }

    private function prodShp(LabRptSumD $day)
    {

    }

    /**
     * swipeHoursFound
     *
     * @param TimRecSum $locationData
     * @param TimRecGup $timeClockRecord
     * @return void
     */
    private function swipeHoursFound(TimRecSum $locationData, TimRecGup $timeClockRecord)
    {
        try {
            $recordDate = Carbon::parse($timeClockRecord->tmu_cyrdat)->setTimezone('America/Chicago');
            $tempRate = $timeClockRecord->rate;

            if ($recordDate->format('ymd') <= 160931 && $timeClockRecord->temp == 'Y') {
                if ($locationData->tms_dptloc == 1) {
                    $tempRate = $tempRate + ($tempRate * .23);
                } else {
                    $tempRate = $tempRate + ($tempRate * .04);
                }
            }

            if ($locationData->tms_shift == 2) {
                $tempRate = $tempRate + .20;
            } else if ($locationData->tms_shift == 3) {
                $tempRate = $tempRate + .35;
            }

            $selectDay = LabRptSumD::where('LRD_DPTLOC', $locationData->tms_dptloc)
                ->where('LRD_DPTABV', $locationData->tms_dptabv)
                ->where('LRD_DPTPLT', $locationData->tms_dptplt)
                ->where('LRD_CYRDAT', $recordDate->format('ymd'))
                ->first();

            if (!$selectDay) {
                $result = $this->dayExists($selectDay, $timeClockRecord, $locationData, $tempRate);
            } else {
                $result = $this->dayExists(null, $timeClockRecord, $locationData, $tempRate);
            }

            $this->checkInsert($timeClockRecord, $locationData, $tempRate, 'TREC');
        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }
    }

    /**
     * swipeHoursNotFound
     *
     * @param TimRecGup $timeClockRecord
     * @return void
     */
    private function swipeHoursNotFound(TimRecGup $timeClockRecord)
    {
        try {
            $recordDate = Carbon::parse($timeClockRecord->tmu_cyrdat)->setTimezone('America/Chicago');
            $query = DpTempRsts::select(
                'TMS_DPTLOC',
                'TMS_DPTABV',
                'TMS_DPTPLT',
                'TMS_SHIFT'
            )
                ->where('TMS_EMPNO', $timeClockRecord->tmu_empno)
                ->where('TMS_WEEK', $recordDate->format('W'))
                ->where('TMS_YEAR', $recordDate->format('Y'));

            if ($query->first()) {
                $this->foundOnRoster($query->first(), $timeClockRecord);
            } else {
                $this->foundOnRoster(null, $timeClockRecord);
            }
        } catch (\Exception $e) {
            Helper::abortApplication($e);
        }

    }

    /**
     * setDepartmentOnlyMode
     *
     * @param string $value
     * @return void
     */
    private function setDepartmentOnlyMode(string $value = ''): void
    {
        $this->departmentOnlyMode = $value;
    }

    /**
     * setEndDate
     *
     * @param integer $daysToSubtract
     * @return void
     */
    private function setEndDate(int $daysToSubtract = 1): void
    {
        $this->dayEnd = Carbon::now()->subDays($daysToSubtract)->setTimezone('America/Chicago');
    }

    /**
     * setStartDate
     *
     * @param integer $daysToSubtract
     * @return void
     */
    private function setStartDate(int $daysToSubtract = 15): void
    {
        $this->dayStart = Carbon::now()->subDays($daysToSubtract)->setTimezone('America/Chicago');
    }
}
