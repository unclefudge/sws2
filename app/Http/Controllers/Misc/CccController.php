<?php

namespace App\Http\Controllers\Misc;

use DB;
use PDF;
use Mail;
use App\Models\Misc\Equipment\EquipmentLog;
use App\Models\Ccc\Youth;
use App\Models\Ccc\Program;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

class CccController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function importCCCprogram()
    {
        echo "Importing CCC Program<br><br>";

        DB::table('zccc_programs')->truncate();
        $row = 0;
        if (($handle = fopen(public_path("Oct SHP Program.csv"), "r")) !== false) {
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row ++;
                if ($row == 1) continue;
                $num = count($data);

                list($d, $m, $y) = explode('/', $data[1]);
                $date_with_leading_zeros = sprintf('%02d', $d) . '/' . sprintf('%02d', $m) . '/' . str_pad($y, 4, "20", STR_PAD_LEFT);
                $date = Carbon::createFromFormat('d/m/Y H:i', $date_with_leading_zeros . '00:00')->toDateTimeString();

                $import = [
                    'name'    => $data[0],
                    'date'    => $date,
                    'cost'    => $data[2],
                    'max'     => $data[3],
                    'pickups' => $data[4],
                    'brief'   => $data[5],
                    'notes'   => $data[6],
                ];
                //dd($import);
                $program = Program::create($import);
                echo $program->date->format('M d') . " - $program->name<br>";
            }
            fclose($handle);
        }
        echo "<br><br>Completed<br>-------------<br>";
    }

    public function importCCCyouth()
    {
        echo "Importing CCC Youth<br><br>";

        DB::table('zccc_pickups')->truncate();
        DB::table('zccc_programs_youth')->truncate();
        DB::table('zccc_youth')->truncate();
        $row = 0;
        $program_dates = [];
        if (($handle = fopen(public_path("Oct SHP Signups.csv"), "r")) !== false) {
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row ++;
                if ($row == 1) continue;
                if ($row == 2) {
                    $col7 = new Carbon($data[7]);
                    $col8 = new Carbon($data[8]);
                    $col9 = new Carbon($data[9]);
                    $col10 = new Carbon($data[10]);
                    $col11 = new Carbon($data[11]);
                    $col12 = new Carbon($data[12]);
                    $col13 = new Carbon($data[13]);
                    $col14 = new Carbon($data[14]);

                    $program_dates = [
                        '7'  => $col7->format('Y-m-d'),
                        '8'  => $col8->format('Y-m-d'),
                        '9'  => $col9->format('Y-m-d'),
                        '10' => $col10->format('Y-m-d'),
                        '11' => $col11->format('Y-m-d'),
                        '12' => $col12->format('Y-m-d'),
                        '13' => $col13->format('Y-m-d'),
                        '14' => $col14->format('Y-m-d'),
                    ];
                    continue;
                }

                if ($data[0] && $data[1] && $data[2] && $data[3] && $data[4] && $data[5]) {

                    // Convert DOB to carbon date
                    list($d, $m, $y) = explode('/', $data[1]);
                    $date_with_leading_zeros = sprintf('%02d', $d) . '/' . sprintf('%02d', $m) . '/' . str_pad($y, 4, "20", STR_PAD_LEFT);
                    $dob = Carbon::createFromFormat('d/m/Y H:i', $date_with_leading_zeros . '00:00')->toDateTimeString();

                    $import = [
                        'name'               => trim($data[0]),
                        'dob'                => $dob,
                        'address'            => trim($data[2]),
                        'parent'             => trim($data[3]),
                        'phone'              => trim($data[4]),
                        'email'              => trim($data[5]),
                        'pickup'             => trim($data[6]),
                        // 7,8,9,10,11,12,13,14 Program
                        'leave_unsupervised' => trim($data[15]),
                        'consent_photo'      => trim($data[16]),
                        'consent_movie'      => trim($data[17]),
                        'consent_medical'    => trim($data[18]),
                        'medical'            => trim($data[19]),
                        'notes'              => trim($data[20]),
                    ];
                    //dd($import);
                    $youth = Youth::create($import);

                    // Add youth to programs
                    $count = 0;
                    foreach ($program_dates as $col => $date) {
                        $attend = trim($data[$col]);
                        if ($attend) {
                            $status = ($attend[0] == 'x') ? $status = 0 : $attend[1];
                            $program = Program::whereDate('date', $date)->first();
                            $count ++;
                            if ($program)
                                $youth->add2program($program, $status);
                        }
                    }

                    echo "[$count] &nbsp; $youth->name<br>";
                }
            }
            fclose($handle);
        }
        echo "<br><br>Completed<br>-------------<br>";
    }

    public function outputCCCprogram()
    {
        //echo "Output CCC Program<br><br>";

        $programs = Program::all();
        $youths = Youth::all();

        // Sort by Last Name
        $lastnames = [];
        foreach ($youths as $youth) {
            list($first, $last) = explode(" ", trim($youth->name), 2);
            $lastnames[$youth->id] = $last;
        }
        asort($lastnames);

        $attendees = [];
        foreach ($lastnames as $id => $last) {
            $attendees[] = Youth::findOrFail($id);
        }


        //return view('pdf/ccc-program', compact('programs'));
        $pdf = PDF::loadView('pdf/ccc-program', compact('programs', 'attendees'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream();
        echo "<br><br>Completed<br>-------------<br>";
    }
}
