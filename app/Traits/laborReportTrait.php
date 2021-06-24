<?php

namespace App\Traits;

use App\Models\Nexus53\{
    GroupMasterFile,
    GroupUsersXRef,
    SMSUserFile
};
use App\Models\Qs36f\{
    SawDet,
    ExtDet,
    ShipDetail,
    FabOperationsDepartment as FABOPRDEPT,
    FabOperationsMasterFile as FABOPRMS,
    FabDet,
    CpnBmDetail,
    CpnPrice,
    CpnMaster,
    UsgEngineeredRevenue
};
use App\Models\Webspt\SessionVariable;
use Carbon\Carbon;

trait laborReportTrait
{
    public static function getDailyLaborGoals()
    {
        return [
            [
                'ANO-1' => .15,
                'ALL-EXT' => .055,
                'ALL-FAB' => .28,
                'PNT-1' => .22
            ],
            [
                'ALL' => .39,
                "ALL-EXT" => .055,
                "EXT-5" => .055,
                "EXT-6" => .055,
                "FAB-6" => .25,
                "FAB-7" => .25,
                "FAB-8" => .29,
                "FAB-12" => .25,
                "FAB-13" => .25,
                "FAB-15" => .27,
                "FAB-16" => .20,
                "FAB-18" => .21,
                "ALL-FAB" => .255,
                "PCK-5" => .016,
                "PNT-4" => .16
            ]
        ];
    }

    public static function getDailyLaborMonths()
    {
        $months = range(0, 12);
        unset($months[0]);
        $monthsArray = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        return array_combine($months, $monthsArray);
    }

    private static function startArrayFrom(
        int $start,
        int $stop,
        array $array
    ): array {
        $newArray = range(0, $stop);
        for ($i=0; $i < $start; $i++) {
            unset($newArray[$i]);
        }

        return array_combine($newArray, $array);
    }

    public static function getExtrusionNetPounds(
        int $fromDate,
        int $toDate,
        int $location,
        int $press,
        int $shift
    ): float {
        $totalPounds = 0.00;
        $query = SawDet::select(
            'SAW_DIE',
            'SAW_LENGTH',
            'SAW_TRUWGT',
            'SAW_PIECES'
        );

        if($fromDate > 0) {
            $query->where('SAW_CYRDAT', '>=', Carbon::createFromFormat('ymd', $fromDate, 'America/Chicago')->format('Ymd'));
        }

        if($toDate > 0) {
            $query->where('SAW_CYRDAT', '<=', Carbon::createFromFormat('ymd', $toDate, 'America/Chicago')->format('Ymd'));
        } else {
            $query->where('SAW_CYRDAT', '<=', Carbon::createFromFormat('ymd', $fromDate, 'America/Chicago')->format('Ymd'));
        }

        if($press > 0) {
            $query->where('SAW_PRESS', $press);
        } else if($location == 1) {
            $query->whereIn('SAW_PRESS', [1,2,3,4]);
        } else if ($location == 2) {
            $query->whereIn('SAW_PRESS', [5,6]);
        }

        if($shift > 0)
        {
            $query->where('SAW_SHIFT', $shift);
        }

        $data = $query->get();

        foreach($data as $d) {
            $totalPounds += ((floatval($d->saw_length) / 12) * floatval($d->saw_truwgt)) * floatval($d->saw_pieces);
        }

        return number_format($totalPounds, 2, '.', '');
    }

    public static function getExtrusionGrossPounds(
        int $fromDate,
        int $toDate,
        int $location,
        int $press,
        int $shift
    ): float {
        $totalPounds = 0.00;
        $bltWeights = [2.01222,2.75800,3.75392,4.90308,6.20546,7.66106];
        $bltWeights = self::startArrayFrom(5, 10, $bltWeights);

        $query = ExtDet::select(
            'EXD_BLTDIA',
            'EXD_BLTLEN',
            'EXD_CHARGE'
        );

        if($fromDate > 0) {
            $query->where('EXD_CYRDAT', '>=', Carbon::createFromFormat('ymd', $fromDate, 'America/Chicago')->format('Ymd'));
        }

        if($toDate > 0) {
            $query->where('EXD_CYRDAT', '<=', Carbon::createFromFormat('ymd', $toDate, 'America/Chicago')->format('Ymd'));
        } else {
            $query->where('EXD_CYRDAT', '<=', Carbon::createFromFormat('ymd', $fromDate, 'America/Chicago')->format('Ymd'));
        }

        if($press > 0) {
            $query->where('EXD_PRESS', $press);
        } else if($location == 1) {
            $query->whereIn('EXD_PRESS', [1,2,3,4]);
        } else if ($location == 2) {
            $query->whereIn('EXD_PRESS', [5,6]);
        }

        if($shift > 0)
        {
            $query->where('EXD_SHIFT', $shift);
        }

        $data = $query->get();

        foreach($data as $d) {
            if ($d->exd_bltdia == 0) {
                continue;
            }
            $totalPounds += floatval($bltWeights[$d->exd_bltdia]) * floatval($d->exd_bltlen) * floatval($d->exd_charge);
        }

        return number_format($totalPounds, 2, '.', '');
    }

    public static function getJambSillRevenue(int $date): float
    {
        $totDol=0;
        $query = ShipDetail::select('shp_qtyshp', 'shp_fab$ as shp_fab_dollar')->where('SHP_CUSTNO', 433)
            ->where('SHP_CYRDAT', 'LIKE', '%'.$date.'%')
            ->get();

        if (!$query->isEmpty()) {
            foreach ($query as $q) {
                $totDol += $q->shp_qtyshp * $q->shp_fab_dollar;
            }
        }

        return number_format($totDol, 2);
    }

    public static function getFabricationRevenue(
        int $fromDate,
        int $toDate,
        int $plant,
        int $shift,
        bool $bypassOperations
    ) {
        $totalRevenue=0;
        $entries=0;
        $usgProdHours=0;
        $totalEarnedHours=0;
        $dayProdHrs=0;
        $dayPerStd=0;
        $fabEntries=array();

        if(!$bypassOperations){
            if($plant == 16) //welding
            {
                $hdrPlant = 8;  //Assembly/USG
            }
            else if($plant == 13)//jambsill
            {
                $hdrPlant = 6;  //FAB6
            }
            else if($plant == 15)//Bending
            {
                $hdrPlant = 6;
            }
            else if($plant == 18)//Respironics
            {
                $hdrPlant = 6;
            }
            else
            {
                $hdrPlant = $plant;
            }

            $operationcodeList = self::getOperations($plant,$hdrPlant);
        }

        if (count($operationcodeList) == 0 && !$bypassOperations) {
            return [
                "Total Revenue"         =>(double)$totalRevenue,
                "Production Hours"      =>(double)$dayProdHrs,
                "USG Production Hours"  =>(double)$usgProdHours,
                "Percent Standard"      =>(double)$dayPerStd,
                "Earned Hours"          =>(double)$totalEarnedHours,
                "Entries"               =>$fabEntries
            ];
        }

        $fabDetQuery = FabDet::select(
            'FBD_START',
            'FBD_STOP',
            'FBD_HOURS',
            'FBD_STD',
            'FBD_GOOD',
            'FBD_MISC',
            'FBD_DIE',
            'FBD_LENGTH',
            'FBD_OPER',
            'FBD_FABHRS',
            'FBD_PART',
            'FBD_CUSTNO',
            'FBD_FABPRC'
        );

        if ($fromDate>0 && $toDate==0) {
            $fabDetQuery->where('FBD_CYRDAT', $fromDate);
        } else if ($fromDate>0 && $toDate>0) {
            $fabDetQuery->whereBetween('FBD_CYRDAT', [$fromDate, $toDate]);
        }

        if ($shift > 0) {
            $fabDetQuery->where('FBD_SHIFT', $shift);
        }

        if (!$bypassOperations) {
            if ($plant == 8) {
                $fabDetQuery->whereIn('FBD_PLANT', [6,8,14]);
            } else if($plant == 15) {
                $fabDetQuery->whereIn('FBD_PLANT', [6,8]);
            } else {
                if ($hdrPlant != '') {
                    $fabDetQuery->where('FBD_PLANT', $hdrPlant);
                }

            }
        } else {
            $fabDetQuery->where('FBD_PLANT', $plant);
        }
        $fabDetQuery->orderBy('FBD_DIE', 'ASC');
        $fabDetQuery->orderBy('FBD_LENGTH', 'ASC');
        $fabDetQuery->orderBy('FBD_PART', 'ASC');

        $data = $fabDetQuery->get();

        foreach ($data as $d) {
            if (
                trim($d->fbd_std) > 0 &&
                trim($d->fbd_misc) != "S" &&
                trim($d->fbd_hours) > 0 &&
                (in_array(substr(trim($d->fbd_part), -3), $operationcodeList) || $bypassOperations)
            ) {
                $dieno = trim($d->fbd_die);
                $fabHrs = trim($d->fbd_hours);
                $fabStd = trim($d->fbd_std);
                $fabGood = trim($d->fbd_good);

                $EarnedHrs = $fabGood/$fabStd;
                $pieceHr = $fabGood / $fabHrs;

                $percentStd = $pieceHr / $fabStd;

                $dayPerStd += ($percentStd * $fabHrs);

                $entries++;
                $calculation="";

                $weldQuery = CpnBmDetail::where('CBD_DIE', trim($d->fbd_die))
                    ->where('CBD_LENGTH', trim($d->fbd_length))
                    ->where('CBD_FINTYP', substr(trim($d->fbd_part), 7, 1))
                    ->where('CBD_FINCOD', substr(trim($d->fbd_part), 8, 2))
                    ->where('CBD_FABNO', substr(trim($d->fbd_part), 0, 6))
                    ->first();

                if ($weldQuery) {
                    $priceQuery = CpnPrice::select('CPNPRICE.CPP_FABPC1', 'FBH_HRSPC', 'CPNMASTER.CPN_SOLDBY', 'CPNMASTER.CPN_LENGTH')
                        ->join('CPNMASTER', function($join){
                            $join->on('CPNPRICE.CPP_DIE', 'CPNMASTER.CPN_DIE');
                            $join->on('CPNPRICE.CPP_LENGTH', 'CPNMASTER.CPN_LENGTH');
                            $join->on('CPNPRICE.CPP_FINTYP', 'CPNMASTER.CPN_FINTYP');
                            $join->on('CPNPRICE.CPP_FINCOD', 'CPNMASTER.CPN_FINCOD');
                            $join->on('CPNPRICE.CPP_FABNO', 'CPNMASTER.CPN_FABNO');
                        })
                        ->leftJoin('FABOPERHD', function($join){
                            $join->on('CPNPRICE.CPP_DIE', 'FABOPERHD.FBH_DIE');
                            $join->on('CPNPRICE.CPP_LENGTH', 'FABOPERHD.FBH_LENGTH');
                            $join->on('CPNPRICE.CPP_FABNO', 'FABOPERHD.FBH_FABNO');
                        })
                        ->where('CPNPRICE.CPP_DIE',    trim($d->fbd_die))
                        ->where('CPNPRICE.CPP_LENGTH', trim($d->fbd_length))
                        ->where('CPNPRICE.CPP_FINTYP', trim($d->fbd_finityp))
                        ->where('CPNPRICE.CPP_FINCOD', trim($d->fbd_fincod))
                        ->where('CPNPRICE.CPP_FABNO',  trim($d->fbd_fabno))
                        ->first();

                    if ($priceQuery) {
                        $price = $priceQuery->cpp_fabpc1;
                        $soldby = $priceQuery->cpn_soldby;
                        $length = $priceQuery->cpn_length;

                        if ($price > 999) {
                            $price = 0;
                        }

                        $price = $price / $priceQuery->fbh_hrspc;
                        if ($soldby == 3) {
                            $price = $price * ($length / 12);
                        }

                        $stdHours = $fabGood / $fabStd;
                        $fabDol = $stdHours * $price;
                        $calculation = $stdHours." * ".$price;
                    }
                    else
                    {
                        if ($d->fbd_custno == 8644 && $d->fbd_fabhrs == 0) {
                            $fabDol = (($d->fbd_length / 12) * 	$d->fbd_fabprc * $d->fbd_good);
                            $calculation="((".$d->fbd_length."/". "12) * ".	$d->fbd_fabprc ." * ". $d->fbd_good." )";
                        } else {
                            $fabDol = (($fabGood / $fabStd) * $d->fbd_fabhrs);
                            $calculation="((".$fabGood." / ".$fabStd.")"." * ".$d->fbd_fabhrs.")";
                        }
                    }
                } else {
                    if ($d->fbd_custno == 8644 && $d->fbd_fabhrs == 0) {
                        $fabDol = (($d->fbd_length / 12) * 	$d->fbd_fabprc * $d->fbd_good);
                        $calculation="((".$d->fbd_length."/". "12) * ".	$d->fbd_fabprc ." * ". $d->fbd_good." )";
                    } else {
                        $fabDol = (($fabGood / $fabStd) * $d->fbd_fabhrs);
                        $calculation="((".$fabGood." / ".$fabStd.")"." * ".$d->fbd_fabhrs.")";
                    }
                }

                if ($fabDol == 0) {
                    $usgQuery = UsgEngineeredRevenue::where('URV_DIE', $dieno)
                        ->where('URV_OPCODE', str_pad(substr(trim($d->fbd_part), -3), 3, "0", STR_PAD_LEFT))
                        ->first();

                    if ($usgQuery->urv_ftpric > 0) {
                        $fabHours = $usgQuery->urv_ftpric;
                        $fabDol = (((trim($d->fbd_length) / 12) * $fabHours) * $fabGood);
                        $calculation="(((".trim($d->fbd_length)." / 12) * ".$fabHours.") * ".$fabGood.")";
                    }
                }

                $totalEarnedHours += $EarnedHrs;
                $dayProdHrs += $fabHrs;
                $totalRevenue += $fabDol;
                $weekPerStd = $weekPerStd + ($percentStd * $fabHrs);
                $weekEntries = $weekEntries + 1;
                $fabEntries[] = [
                    "Record"=>$d,
                    "Revenue"=>(double)$fabDol,
                    'Earned Hours'=>(double)$EarnedHrs,
                    "Hours"=>(double)$fabHrs,
                    "Percent Standard"=>($percentStd * $fabHrs),
                    "Calculation"=>$calculation,
                    "Entry"=>$d
                ];
            }
        }

//         $ww_selstring = "SELECT fbd_start, fbd_stop, FBD_HOURS, FBD_STD,FBD_PART, FBD_GOOD, FBD_MISC,"
//                 . " FBD_DIE, FBD_LENGTH, FBD_OPER, FBD_FABHRS, SUBSTRING(FBD_PART, 12, 3) as OPCODE"
//                 . " ,FBD_STD, FBD_CREWSZ, FBD_CUSTNO, FBD_FABPRC FROM QS36F/FABDET"
//                 . " WHERE FBD_CYRDAT = " . $fromDate;
//                 if($shift>0)
//                     $ww_selstring.= " AND FBD_SHIFT = " . $shift;
//
//                 $ww_selstring.= " AND (fbd_plant"
//                 . " = 14) ORDER BY FBD_OVRSEQ DESC, fbd_start"
//                 ." FOR READ ONLY";
//
//         //printOut("<br>USG DAILY <br>");
//         //printOut($ww_selstring);
//         $stmt = db2_exec($db2conn, $ww_selstring);
//         $curStart = 0;
//         $usgRevenue=0;
//         $usgProdHours=0;
//         //usg fabdet
//         //var_dump($operationcodeList);
//
//         while($row = db2_fetch_assoc($stmt))
//         {
//             if($plant != 15 && $plant != 16  ){
//                 continue;
//             }
//             $row=TrimArray($row);
//             //var_dump($row);
//             if($row['FBD_STD'] > 0 && $row['FBD_MISC'] != "S" &&  in_array(substr($row['FBD_PART'],-3),$operationcodeList) )
//             {
//                 // /var_dump($row);
//                 $start = $row['FBD_START'];
//                 $stop = $row['FBD_STOP'];
//                 $dieNo = $row['FBD_DIE'];
//                 $op = $row['OPCODE'];
//
//                 if(floatval($stop) <= floatval($start))
//                 {
//                     $tstop = floatval($stop) + 12;
//                     $stop = $tstop;
//                 }
//
//                 $start = number_format($start, 2);
//                 $stop = number_format($stop, 2);
//                 $start = str_replace(".", "", strval($start));
//                 $stop = str_replace(".", "", strval($stop));
//
//                 if(strlen($start) == 3)
//                 {
//                     $start = "0" . substr($start, 0, 1) . ":" . substr($start, 1, 3) . ":00";
//                 }
//                 else
//                 {
//                     $start = substr($start, 0, 2) . ":" . substr($start, 2, 4) . ":00";
//                 }
//
//                 if(strlen($stop) == 3)
//                 {
//                     $stop = "0" . substr($stop, 0, 1) . ":" . substr($stop, 1, 3) . ":00";
//                 }
//                 else
//                 {
//                     $stop = substr($stop, 0, 2) . ":" . substr($stop, 2, 4) . ":00";
//                 }
//
//                 $format = 'Y-m-d H:i:s';
//
//                 $startTime = strtotime($start);
//                 $stopTime = strtotime($stop);
//
//                 $diff = $stopTime - $startTime;
//                 $hours = $diff / (60*60);
//                 //echo $hours . "</br>";
//
//
//                 $fabStd = $row['FBD_STD'];
//                 $fabGood += $row['FBD_GOOD'];
//
//                 if ($row['FBD_CUSTNO'] == 8644 && $row['FBD_FABHRS'] == 0)
//                 {
//                     $fabDol = (($row['FBD_LENGTH'] / 12) * 	$row['FBD_FABPRC'] * $row['FBD_GOOD']);
//                 }
//                 else
//                     $fabDol = (($fabGood / $fabStd) * $row['FBD_FABHRS']);
//
//                 if($fabDol == 0)
//                 {
//                     $SelectUSGRev_str = "SELECT *"
//                                     . " FROM QS36F/USGENGREV"
//                                     . " WHERE URV_DIE = " . $dieno
//                                     . " AND URV_OPCODE = '" . str_pad($row['OPCODE'], 3, "0", STR_PAD_LEFT) . "'"
//                                     ." FOR READ ONLY";
//                     $usgStmt = db2_exec($db2conn, $SelectUSGRev_str);
//                     $usgRow = db2_fetch_assoc($usgStmt);
//                     if($usgRow['URV_FTPRIC'] > 0)
//                     {
//                         $fabHours = $usgRow['URV_FTPRIC'];
//                         $fabDol = ((($length / 12) * $fabHours) * $good);
//                     }
//                     //error_log($SelectUSGRev_str);
//                 }
//                     //echo "<br> Production Hours Begin: " . $dayProdHrs . " Hrs: " . $hours . " After: " . ($dayProdHrs + $hours) . "</br>";
//                 //if(($curStart != $start || $curStop != $stop) || $curDie != $dieno || $curOp != $op)
//                 //{
//                 $pieceHr = $fabGood / $hours;
//                 $percentStd = $pieceHr / $fabStd;
//                 $dayPerStd = $dayPerStd + ($percentStd * $hours);
//                 $dayEntries++;
//                 if ($row['FBD_CUSTNO'] == 8644 && $row['FBD_FABHRS'] == 0)
//                 {
//                     $fabDol = (($row['FBD_LENGTH'] / 12) * 	$row['FBD_FABPRC'] * $row['FBD_GOOD']);
//                 }
//                 else
//                     $fabDol = (($fabGood / $fabStd) * $row['FBD_FABHRS']);
//
//                 $EarnedHrs=($fabGood/ $fabStd);
//                 $totalEarnedHours+=$EarnedHrs;
//
//                 $dayProdHrs += $hours;
//                 $usgProdHours+= $hours;
//
//                 $curStart = $start;
//                 $curStop = $stop;
//                 $curDie = $dieno;
//                 $curOp = $op;
//                 $fabGood = 0;
//                 $fabEntries[]=array("Record"=>$row,"Revenue"=>(double)number_format($fabDol,2),"Production Hours"=>$hours,"Earned Hours"=>(double)$EarnedHrs,"Hours"=>(double)$hours, "Percent Standard"=>($percentStd * $hours));
//
//                 //}
//                 //var_dump($fabDol);
//                 $totalRevenue += $fabDol;
//                 //$dayPerStd += ($percentStd * $hours);
//                 $dayEntries++;
//                 //var_dump($weekFabDol);
//             }
//
//         }
//
//         //$weekPerStd = ($dayPerStd / $dayProdHrs) * 100;
//         //printOut("_________________TOTALS____________________");
//
//         //	printOut("END:Day Revenue:".$totalRevenue);
//         //	printOut("END:Day Production Hours :".$dayProdHrs);
//         //intOut("END: DAY PER STD".$dayPerStd);
//         //printOut("END: DAY PER STD calc".($dayPerStd / $dayProdHrs) * 100);
//
//         //printOut("END:Day Entries:".$dayEntries);
//         //printOut("END: EarnedHrs:".$EarnedHrs);
//
//
//
//         return array("Total Revenue"=>(double)$totalRevenue,"Production Hours"=>(double)$dayProdHrs,"USG Production Hours"=>(double)$usgProdHours,"Percent Standard"=>(double)$dayPerStd,"Earned Hours"=>(double)$totalEarnedHours,"Entries"=>$fabEntries);
//         //Loop through fab entries
    }

    public static function getOperations(int $plant, int $hdrPlant)
    {
        $query = FABOPRMS::select('FOC_CODE')
            ->join('FABOPRDEPT', 'FABOPRDEPT.FOD_FABDPT', '=', 'FABOPRMS.FOC_DEPT');


        if($hdrPlant == 8 || $hdrPlant == 6 ){
            $query->where('FABOPRDEPT.FOD_PLANT1', $plant);
            $query->orWhere('FABOPRDEPT.FOD_PLANT2', $plant);
            $query->orWhere('FABOPRDEPT.FOD_PLANT3', $plant);

            if($plant == 8){
                $query->orWhere('FABOPRDEPT.FOD_PLANT1', 14);
                $query->orWhere('FABOPRDEPT.FOD_PLANT2', 14);
                $query->orWhere('FABOPRDEPT.FOD_PLANT3', 14);
            }
        }

        $query->orderBy('FOC_CODE', 'ASC');
        $data = $query->get();

        $operations = [];

        foreach($data as $d) {
            array_push($operations, $d);
        }

        return $operations;
    }
}
