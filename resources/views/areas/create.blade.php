@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-center text-uppercase mb-4">Create new area</h5>
                    @if($errors->all())
                        <div class="alert alert-danger">
                            <ul class="list-unstyled mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="post" action="{{ url('areas') }}">
                        {{ csrf_field() }}
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Area Name</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg px-5">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
