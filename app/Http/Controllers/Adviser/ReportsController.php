<?php
/**
 * Created by PhpStorm.
 * User: adriannatabio
 * Date: 05/10/2019
 * Time: 1:21 PM
 */

namespace App\Http\Controllers\Adviser;


use App\Http\Controllers\Controller;
use App\Project;
use App\User;
use App\ProjectPanel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Response;

class ReportsController extends Controller
{
    public function adviserReports(Request $request)
    {
        return view('reports.handled-projects', [
            'data'     => $this->prepareData($request),
            'advisers' => User::query()
                              ->select('id', 'firstname', 'lastname', 'middle_initial')
                              ->where('user_role', User::USER_TYPE_ADVISER)
                              ->get()
                              ->pluck('fullname', 'id')
        ]);
    }

    public function adminReports(Request $request)
    {
        return view('reports.handled-projects', [
            'data'     => $this->prepareData($request),
            'advisers' => User::query()
                              ->select('id', 'firstname', 'lastname', 'middle_initial')
                              ->where('user_role', User::USER_TYPE_ADVISER)
                              ->get()
                              ->pluck('fullname', 'id')
        ]);
    }

    protected function prepareData(Request $request)
    {
        if ($request->role == 'Panel') {
            $projects = Project::whereHas('project_panel', function($q){
                $q->where(['panel_id' => Auth::user()->id, 'project_status' => 'approved']);
            })->get();
            
        } else {
            /** @var Collection $projects */
            $projects = Auth::user()->handledProjects()
                        ->whereNotNull('date_submitted')
                        ->where('project_status', '=', 'approved')
                        ->get();
        }
        
        return $this->formatData($projects, $request);
    }

    protected function formatData(Collection $projects, $request = null) : Collection
    {
        $data = collect();

        $currentYear = date('Y');

        $yearRange  = isset($request) && $request->from_year && $request->to_year ? range($request->from_year, $request->to_year) : range($currentYear, $currentYear - 4, -1);

        foreach ($yearRange as $year) {
            $data->put($year, [
                '1' => $projects->filter(function (Project $project) use ($year) {
                    return $project->academic_year == $year && $project->semester == 1;
                })->count(),
                '2' => $projects->filter(function (Project $project) use ($year) {
                    return $project->academic_year == $year && $project->semester == 2;
                })->count(),
                '3' => $projects->filter(function (Project $project) use ($year) {
                    return $project->academic_year == $year && $project->semester == 3;
                })->count(),
                '4' => $projects->filter(function (Project $project) use ($year) {
                    return $project->academic_year == $year && $project->semester == 4;
                })->count()
            ]);
            //$firstSem  = Project::determinePeriod($year, 1);
            //$secondSem = Project::determinePeriod($year, 2);
 
            // $data->put($year, [
            //     '1' => $projects->filter(function (Project $project) use ($firstSem) {
            //         return Carbon::parse($project->date_submitted)->between(
            //             Carbon::parse($firstSem[0]),
            //             Carbon::parse($firstSem[1]),
            //             true
            //         );
            //     })->count(),
            //     '2' => $projects->filter(function (Project $project) use ($secondSem) {
            //         return Carbon::parse($project->date_submitted)->between(
            //             Carbon::parse($secondSem[0]),
            //             Carbon::parse($secondSem[1]),
            //             true
            //         );
            //     })->count()
            // ]);
        }

        return $data;
    }

    public function getProjectBySemester(Request $request)
    {   
        $sem = $request->semester;
        $year = $request->year;
        $adviserId = $request->adviserId;
        $role = $request->role;

        $isAdviser = Auth::user()->isRole('adviser');
        if ($year && $sem) {
            //$semester  = Project::determinePeriod($year, $sem);

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

            return response()->json(['results' => $results, 'isAdviser' => $isAdviser]);
        }
    }
}