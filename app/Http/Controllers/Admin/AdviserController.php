<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class AdviserController extends Controller
{
    public function showAdvisersListPage(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'adviser'      => 'sometimes|nullable|string'
        ]);

        $query = User::query();
        $query->whereIn('user_role', [User::USER_TYPE_ADVISER, User::USER_TYPE_FACULTY]);

        if (($q = request('adviser')) && $validator->passes()) {
            $query->where(function ($query) use ($q) {
                    $query->where('firstname', 'like', "%{$q}%")
                        ->orWhere('lastname', 'like', "%{$q}%");
            });
        }
        $advisers = $query->with('handledProjects', 'chairPaneledProjects')->orderBy('lastname')->get();

        return view('advisers.index', [
            'advisers' => $advisers
        ]);
    }

    public function showCreateAdviserPage()
    {
        return view('advisers.create');
    }

    public function showEditAdviserPage($teacherId)
    {
        $adviser = User::find($teacherId);

        return view('advisers.edit', [
            'adviser' => $adviser
        ]);
    }

    public function doCreateAdviser(Request $request)
    {
        $request->validate([
            'firstname'      => 'required|string|max:200',
            'lastname'       => 'required|string|max:200',
            'middle_initial' => 'nullable|string|size:1',
            'username'       => 'required|string|unique:users,username',
        ]);

        $adviser                 = new User();
        $adviser->firstname      = $request->input('firstname');
        $adviser->lastname       = $request->input('lastname');
        $adviser->middle_initial = $request->input('middle_initial');
        $adviser->title          = $request->input('title');
        $adviser->user_role      = $request->input('user_role');
        $adviser->username       = $request->input('username');
        $adviser->password       = bcrypt($adviser::USER_DEFAULT_PASSWORRD);
        //$adviser->user_role      = $adviser::USER_TYPE_ADVISER;
        $adviser->save();

        return redirect('advisers')->with('message', 'New teacher created successfully!');
    }

    public function doUpdateAdviser($id, Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:200',
            'lastname' => 'required|string|max:200',
            'middle_initial' => 'nullable|string|size:1',
            'username'=> ['required','string', Rule::unique('users')->ignore($id)],
        ]);

        $adviser = User::find($id);
        $adviser->firstname = $request->input('firstname');
        $adviser->lastname = $request->input('lastname');
        $adviser->middle_initial = $request->input('middle_initial');
        $adviser->title = $request->input('title');
        $adviser->username = $request->input('username');  
        $adviser->user_role = $request->input('user_role');  
        $adviser->save();

        return redirect('advisers')->with('message', 'Teacher edited successfully!');
    }

    public function doDeleteAdviser($id)
    {
        $adviser = User::find($id);

        $adviser->delete();

        return redirect('advisers')->with('message', 'Teacher deleted successfully!');
    }
}
