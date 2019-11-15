@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-center text-uppercase mb-4">Create new teacher</h5>
                    @if($errors->all())
                        <div class="alert alert-danger">
                            <ul class="list-unstyled mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="post" action="{{ url('advisers') }}">
                        {{ csrf_field() }}
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label"><font color="red"> *</font>First Name</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="firstname" value="{{ old('firstname') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label"><font color="red"> *</font>Last Name</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="lastname" value="{{ old('lastname') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Middle Initial</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="middle_initial" value="{{ old('middle_initial') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Role</label>
                            <div class="col-sm-9">
                                <select name="user_role" class="form-control" id="select2-ajax-main" >
                                    <option value=""></option>
                                    <option value="faculty">Faculty</option>
                                    <option value="adviser">Adviser</option>   
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Educational Attainment</label>
                            <div class="col-sm-9">
                                <select name="title" class="form-control" id="select2-ajax-main">
                                    <option value=""></option>
                                    <option value="Masters">Masters</option>
                                    <option value="PhD">PhD</option>   
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label"><font color="red"> *</font>Username</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="username" value="{{ old('username') }}">
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg px-5">Register</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
