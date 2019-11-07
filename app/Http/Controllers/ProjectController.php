<?php

namespace App\Http\Controllers;

use Auth;
use App\Area;
use App\User;
use App\Project;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class ProjectController extends Controller
{
    public function showProjectsListPage(Request $request)
    {
        /** @var Validator $validator */
        $validator = \Validator::make($request->all(), [
//            'academic_year' => 'sometimes|required|date_format:Y',
//            'semester'      => 'sometimes|nullable|in:1,2',
            'title'      => 'sometimes|nullable|string',
            'adviser_id' => 'sometimes|nullable|int'
        ]);


        $query = Project::with(['adviser', 'area', 'authors'])
                        ->when(Auth::user()->isRole(User::USER_TYPE_ADMIN),
                            function (Builder $builder) use ($validator) {
                                return $builder->where('project_status', '=', 'approved')
                                               ->when((($q = request('title')) && $validator->passes()),
                                                   function (Builder $query) use ($q) {
                                                       return $query->where('title', 'like', "%{$q}%");
                                                   })
                                               ->when((($adviserId = request('adviser_id')) && $validator->passes()),
                                                   function (Builder $query) use ($adviserId) {
                                                       return $query->where('adviser_id', '=', $adviserId);
                                                   });
                            });

//
//        if ($validator->passes()) {
//            $dates = Project::determinePeriod(
//                $request->input('academic_year', date('Y')),
//                $request->input('semester', null)
//            );
//            $query = $query->whereBetween('date_submitted', $dates);
//        }


        $projects = $query->paginate(5);

        return view('projects.index', [
            'projects' => $projects,
            'advisers' => User::query()
                              ->select('id', 'firstname', 'lastname', 'middle_initial')
                              ->where('user_role', '=', User::USER_TYPE_ADVISER)
                              ->orderBy('lastname')
                              ->get()
                              ->pluck('fullname', 'id')
                              ->all()

        ]);
    }

    public function showEditProjectPage(Project $project)
    {
        $project->load(['adviser', 'area', 'authors']);

        $faculty = User::where('user_role', User::USER_TYPE_ADVISER)
                       ->orderBy('lastname')
                       ->get();

        $areas    = Area::orderBy('name')->get();
        $students = User::ofType(User::USER_TYPE_STUDENT)->get();

        return view('projects.edit', [
            'project'  => $project,
            'faculty'  => $faculty,
            'areas'    => $areas,
            'students' => $students
        ]);
    }

    public function showCreateProjectPage()
    {
        $faculty = User::where('user_role', User::USER_TYPE_ADVISER)
                       ->orderBy('lastname')
                       ->get();

        $areas    = Area::orderBy('name')->get();
        $students = User::ofType(User::USER_TYPE_STUDENT)->get();

        return view('projects.create', [
            'faculty'  => $faculty,
            'areas'    => $areas,
            'students' => $students
        ]);
    }

    public function doCreateProject(Request $request)
    {
        $rules = [
            'doi'            => 'nullable|string',
            'title'          => 'required|string',
            'author_ids'     => 'required|array',
            'author_ids.*'   => 'required|exists:users,id|distinct',
            'abstract'       => 'required|string',
            'adviser_id'     => ['required', 'exists:users,id', Rule::notIn($request->input('panel_ids', []))],
            'area_id'        => 'required|exists:areas,id',
            'panel_ids'      => 'required|array',
            'panel_ids.*'    => 'required|exists:users,id|distinct',
            'keywords'       => 'required|string',
            'pages'          => 'required|integer',
            'year_published' => 'required|date_format:Y',
            'file'           => 'required|mimes:pdf',
        ];

        if (Auth::user()->isRole(User::USER_TYPE_ADMIN)) {
            $rules += [
                'call_number'    => 'required|string|max:255',
                'date_submitted' => 'required|date|before:tomorrow',
            ];
        }

        \DB::transaction(function () use ($request, $rules) {
            $request->validate($rules, [
                'adviser_id.not_in' => 'The adviser cannot be present in the panel list'
            ]);

            $project                 = new Project();
            $project->doi            = $request->input('doi');
            $project->title          = $request->input('title');
            $project->abstract       = $request->input('abstract');
            $project->adviser_id     = $request->input('adviser_id');
            $project->area_id        = $request->input('area_id');
            $project->keywords       = $request->input('keywords');
            $project->pages          = $request->input('pages');
            $project->year_published = $request->input('year_published');

            if (Auth::user()->isRole(User::USER_TYPE_ADMIN)) {
                $project->call_number    = $request->input('call_number');
                $project->date_submitted = $request->input('date_submitted');
                $project->project_status = 'approved';
            }

            $project->uploaded_file_path = $request->file('file')->store($request->user()->id, 'public');

            $project->save();
            $project->panel()->attach($request->input('panel_ids'));
            $project->authors()->attach($request->input('author_ids'));

            $this->saveImagePreviews($project->uploaded_file_path);
        });

        $redirect = Auth::user()->isRole('student')
            ? redirect('my-projects')
            : redirect('projects');

        return $redirect->with('message', 'New project has been successfully created! Please Update Adviser for approval ');
    }

    public function doEditProject(Project $project, Request $request)
    {
        if (
            $project->is('rejected') // rejecte projects cannot be edited
            || ($project->is('approved') && ! Auth::user()->isRole('admin')) // approved projects can only be edited by admin
        ) {
            return redirect()->back();
        }

        $rules = [
            'doi'            => 'nullable|string',
            'title'          => 'required|string',
            'author_ids'     => 'required|array',
            'author_ids.*'   => 'required|exists:users,id|distinct',
            'abstract'       => 'required|string',
            'adviser_id'     => 'required|exists:users,id',
            'area_id'        => 'required|exists:areas,id',
            'panel_ids'      => 'required|array',
            'panel_ids.*'    => 'required|exists:users,id|distinct',
            'keywords'       => 'required|string',
            'pages'          => 'required|integer',
            'year_published' => 'required|date_format:Y',
            'file'           => 'nullable|mimes:pdf',
        ];

        if (Auth::user()->isRole(User::USER_TYPE_ADMIN)) {
            $rules += [
                'call_number'    => 'required|string|max:255',
                'date_submitted' => 'required|date|before:tomorrow',
            ];
        }

        \DB::transaction(function () use ($request, $rules, $project) {
            $request->validate($rules);

            $project->doi            = $request->input('doi');
            $project->title          = $request->input('title');
            $project->abstract       = $request->input('abstract');
            $project->adviser_id     = $request->input('adviser_id');
            $project->area_id        = $request->input('area_id');
            $project->keywords       = $request->input('keywords');
            $project->pages          = $request->input('pages');
            $project->year_published = $request->input('year_published');

            if (Auth::user()->isRole(User::USER_TYPE_ADMIN)) {
                $project->call_number    = $request->input('call_number');
                $project->date_submitted = $request->input('date_submitted');
            }

            if ($request->hasFile('file')) {
                $project->uploaded_file_path = $request->file('file')->store($request->user()->id, 'public');
                $this->saveImagePreviews($project->uploaded_file_path);
            }

            $project->save();
            $project->panel()->sync($request->input('panel_ids'));
            $project->authors()->sync($request->input('author_ids'));
        });

        $redirect = Auth::user()->isRole('student')
            ? redirect('my-projects')
            : redirect('projects');

        return $redirect->with('message', 'Project has been successfully updated!');
    }

    protected function saveImagePreviews($filePath)
    {
        $storagePath = Storage::disk('public')->getDriver()->getAdapter()->getPathPrefix();
        $file        = new \Imagick($storagePath . $filePath);
        $lastIndex   = $file->getNumberImages() > 5 ? 4 : ($file->getNumberImages() - 1);

        foreach (range(0, $lastIndex) as $index) {
            $page = $index + 1;
            $file->setIteratorIndex($index);
            $file->setCompression(\Imagick::COMPRESSION_JPEG);
            $file->setImageFormat('jpeg');
            $filename = Str::replaceLast('.pdf', "-page-{$page}.jpg", $filePath);
            Storage::disk('public')->put($filename
                , $file);
        }

        return true;
    }

    public function preview(Request $request, Project $project)
    {
        return response()->download(
            public_path("storage/{$project->uploaded_file_path}"),
            sprintf('%s.pdf', Str::snake($project->title)),
            [],
            'inline'
        );
    }

    public function doDeleteProject(Project $project)
    {
        $project->delete();

        $redirect = Auth::user()->isRole('student')
            ? redirect('my-projects')
            : redirect('projects');

        return $redirect->with('message', 'Project has been successfully deleted!');
    }

    public function doSearch(Request $request)
    {
        if ($term = $request->input('search', null)) {
            $results = Project::query()
                              ->select('title', 'id')
                              ->where('title', 'like', "%{$term}%")
                              ->where('project_status', '=', 'approved')
                              ->get()
                              ->map(function (Project $project) {
                                  return [
                                      'id'   => $project->id,
                                      'text' => $project->title
                                  ];
                              })
                              ->toArray();

            return compact('results');
        }

        return response()->json([]);
    }
}
