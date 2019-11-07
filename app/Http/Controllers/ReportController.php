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
        $adviserId = $request->adviserId;
        $isAdviser = Auth::user()->isRole('adviser');
        if ($year && $sem) {
            $semester  = Project::determinePeriod($year, $sem);
            $query = Project::query();

            $query->whereNotNull('date_submitted')->where(['project_status' => 'approved']);

            if ($isAdviser) {
                $query->where(['adviser_id' => Auth::user()->id]);
            } else if (isset($adviserId)) {
                $query->where(['adviser_id' => $adviserId]);
            } 

            $projects = $query->with('authors', 'panel', 'adviser', 'area')->get();

        
            $results = $projects->filter(function (Project $project) use ($semester) {
                    return Carbon::parse($project->date_submitted)->between(
                        Carbon::parse($semester[0]),
                        Carbon::parse($semester[1]),
                        true
                    );
            });

            $data = [
				'results' => $results,
				'isAdviser' => $isAdviser,
				'semester' => $sem == 1 ? '1st Semester ' . $year : '2nd Semester ' . $year
			]; 

			$pdf = PDF::loadView('pdf.report', $data);
			return $pdf->stream('report.pdf');
        }

		
	}
}
