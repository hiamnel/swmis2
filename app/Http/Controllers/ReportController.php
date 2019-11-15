<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use App\Project;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller {
	public function generatePdf(Request $request) 
	{
		$sem = $request->semester + 1;
        $year = $request->year;
        $role = $request->role;
        $adviserId = $request->adviserId;
        $isAdviser = Auth::user()->isRole('adviser');
        if ($year && $sem) {
           // $semester  = Project::determinePeriod($year, $sem);
            $query = Project::query();

            $query->whereNotNull('date_submitted')->where(['semester' => $sem, 'academic_year' => $year, 'project_status' => 'approved']);

            if ($isAdviser) {
                if ($role == 'Adviser') {
                    $query->where(['adviser_id' => Auth::user()->id]);
                } else {
                    $query->whereHas('project_panel', function($q){
                        $q->where(['panel_id' => Auth::user()->id]);
                    });
                }
            } else if (isset($adviserId)) {
                $query->where(['adviser_id' => $adviserId]);
            } 

            $results = $query->with('authors', 'panel', 'adviser', 'area', 'chair_panel')->get();

            // $results = $projects->filter(function (Project $project) use ($semester) {
            //         return Carbon::parse($project->date_submitted)->between(
            //             Carbon::parse($semester[0]),
            //             Carbon::parse($semester[1]),
            //             true
            //         );
            // });

            $semester = '';
            switch ($sem) {
                case 1:
                    $semester = '1st Semester ' . $year;
                    break;
                case 2:
                    $semester = '2nd Semester ' . $year;
                    break;
                case 3:
                    $semester = 'Summer ' . $year;
                    break;
                case 4:
                    $semester = 'Tri-sem ' . $year;
                    break;
                default:
                    break;
            }

            $data = [
				'results' => $results,
				'isAdviser' => $isAdviser,
				'role' => $role,
				'semester' => $semester
			]; 

			$pdf = PDF::loadView('pdf.report', $data);
			return $pdf->stream('report.pdf');
        }

		
	}
}
