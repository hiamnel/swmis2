@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center py-5" style="background:url({{ asset('img/bg.jpeg') }}) top center;background-size:cover;height:400px">
            <img src="{{ asset('img/logo_clean.png') }}" class="img-fluid mx-auto" alt="">
            <form action="{{ url('/') }}" method="get">
                <div class="row">
                
                    <div class="col-sm-8 offset-sm-2 my-5">
                        <div class="input-group">
                            <select name="q[]" class="form-control form-control-lg select2" multiple>
                                @foreach($keywords AS $item)
                                    <option value="{{ $item }}" {{ in_array($item, request()->input('q', []))? 'selected="selected"' : '' }}>{{ $item }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <button class="btn btn-success" type="submit" id="button-addon1">Search</button>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-2 offset-sm-2">
            <h3 class="my-4">Advanced Search</h3>
            <div class="card ">
                <div class="card-body">
                    <form action="">
                        <div class="form-group">
                            <label for="">Document Type</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="option1" checked>
                                <label class="form-check-label" for="exampleRadios1">
                                      Journal
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="option2">
                                <label class="form-check-label" for="exampleRadios2">
                                    Thesis
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="">Area</label>
                            <select name="" id="" class="form-control">
                                <option value="">** ALL AREAS ** </option>
                                @foreach($areas AS $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary text-white btn-block">Search</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <h3 class="my-4">Recent Submissions</h3>
            <div class="card">
                @foreach($projects as $project)
                    <div class="card-body border-bottom">
                        <dl class="row">
                            <dt class="col-md-2">Title</dt>
                            <dd class="col-md-10">
                                <h3 class="card-title mb-0" style="font-size:1.2rem">
                                    <a class="text-decoration-none" href="{{ url("view/{$project->id}") }}" >{{ $project->title }}</a>
                                </h3>
                            </dd>
                            <dt class="col-md-2">Authors</dt>
                            <dd class="col-md-10">
                                <span class="badge badge-secondary text-white mr-1">
                                    <i class="fas fa-user fa-fw"></i>
                                    {!! $project->authors->pluck('fullname')->implode('</span><span class="badge badge-secondary mr-1 text-white"><i class="fas fa-user fa-fw"></i>') !!}
                                </span>
                            </dd>
                            <dt class="col-md-2">Keywords</dt>
                            <dd class="col-md-10">
                                <span class="badge badge-pill badge-info text-white mr-1">
                                    {!! $project->keywords_collection->implode('</span><span class="badge badge-pill badge-info mr-1 text-white">') !!}
                                </span>
                            </dd>
                            <dt class="col-md-2">Area</dt>
                            <dd class="col-md-10">
                                {{ $project->area->name }}
                            </dd>
                        </dl>
                        <p class="card-text">
                             {{ str_limit($project->abstract, 250) }}
                        </p>
                        <p class="card-text text-muted">
                            Submitted on: {{ date_create_immutable($project->created_at)->format('F d, Y') }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
