@extends('layouts.app')

@section('content')
    <div class="container py-3">
        <div class="row">
            <div class="col-sm-12">

                <div class="card">
                    <div class="card-body">
                        @if(Auth::user()->isRole(\App\User::USER_TYPE_ADMIN))
                        <form class="form-inline mb-3" method="get">

                            <div class="form-group">
                                <label for="" class="mr-2">Adviser</label>
                                <select name="adviser" id="" class="form-control select2 ml-2">
                                    <option value="">All</option>
                                    @foreach($advisers AS $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-secondary ml-2">Go</button>
                        </form>
                        @endif
                        <canvas id="canvas"></canvas>
                        <div class="row">
                          <table id="projectTable" class="table mb-0 table-hover table-striped">
                            <thead>
                              @if(Auth::user()->isRole(\App\User::USER_TYPE_ADMIN))
                              <tr class="bg-primary text-white">
                                  <th></th>
                                  <th>Project Title</th>
                                  <th>Authors</th>
                                  <th>Panels</th>
                                  <th>Adviser</th>
                                  <th>Subject Area</th>
                                  <th>Date</th>
                                  <th>Call Number</th>
                                  <th></th>
                              </tr>
                              @else 
                                <tr class="bg-primary text-white">
                                  <th>Project Title</th>
                                  <th>Authors</th>
                                  <th>Adviser</th>
                                  <th>Subject Area</th>
                                  <th>Date</th>
                                  <th>Role</th>
                              </tr>
                              @endif
                            </thead>
                            <tbody id="projectTableData">
                             
                            </tbody>
                          </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection


@push('js')
    <script type="text/javascript" src="{{ asset('js/e.js') }}"></script>
    <script type="text/javascript">
      $(document).ready(function () {

        var data = @json($data ?? []);

        console.log(data);

        var semesterGroup = {
          '1st': [],
          '2nd': []
        };

        var result = [];
        for (var year in data) {
          semesterGroup['1st'].push(data[year]['1']);
          semesterGroup['2nd'].push(data[year]['2'])
        }


        var ctx = document.getElementById("canvas").getContext("2d");

        var data = {
          labels: Object.keys(data),
          datasets: [{
            label: "1st Semester",
            backgroundColor: "#ffe0e6",
            data: semesterGroup['1st']
          },{
            label: "2nd Semester",
            backgroundColor: "#dbf2f2",
            data: semesterGroup['2nd']
          }]
        };

        var myBarChart = new Chart(ctx, {
          type: 'bar',
          data: data,
          options: {
            onClick: function(evt, array){
              console.log('array', array);

              if (array.length > 0) {
                var e =  array[0];
                var activePoint = myBarChart.getElementAtEvent(evt)[0];
                var data = activePoint._chart.data;
                var semester = activePoint._datasetIndex;
                var label = data.datasets[semester].label;
                var value = data.datasets[semester].data[activePoint._index];
                var year = data.labels[e._index];
                if (year) {
                  $.ajax({
                    url:'get-project-by-semester',
                    data: {
                      year: year,
                      semester: semester + 1
                    },
                    method: 'GET',
                    success: function(data) {
                      console.log(data);
                      $("#projectTableData").html("");
                      var count = 1;
                      $.each( data.results, function( key, result ) {
                        var authors = "";
                        var panels = "";
                        var authorCount = 1;
                        var panelCount = 1;

                        $.each( result.authors, function( key, author ) {
                          authors += authorCount + ". " + author.fullname + "</br>";
                          authorCount++;
                        });

                        $.each( result.panel, function( key, panel ) {
                          panels += panelCount + ". " + panel.fullname + "</br>";
                          panelCount++;
                        });

                        var status = "";
                        if(result.project_status == 'pending') {
                          status = "<span class='d-block badge badge-secondary text-white mb-3'>PENDING</span>";
                        } else if (result.project_status == 'rejected') {
                          status = "<span class='d-block badge badge-danger text-white mb-3'>REJECTED</span>";
                        } else {
                          status = "<span class='d-block badge badge-success text-white mb-3'>APPROVED</span>";
                        }

                        var actions = "";
                        if (data.isAdviser) {
                          var url = "my-handled-projects/" + result.id;
                          actions += '<a href="' + url + '" class="mr-2">Review</a>';
                          
                          if(result.project_status == 'pending') {
                            var editUrl = "projects/" + result.id + "/edit";
                            actions += '<a href="' + editUrl + '" class="btn btn-info btn-block mb-2">Edit</a>';
                          }
                        } else {
                          var editUrl = "projects/" + result.id + "/edit";
                          actions += '<a href="' + editUrl + '" class="btn btn-info btn-block mb-2">Edit</a>';
                          if(result.project_status == 'pending') {
                            actions += '<button class="btn btn-danger btn-block">Delete</button>';
                          }
                        }

                        if (data.isAdviser) {
                          var html = "<tr>";
                              html += "<td>";
                              html += result.title;
                              html += "</td>";
                              html += "<td>";
                              html += authors;
                              html += "</td>";
                              html += "<td>";
                              html += result.adviser.fullname;
                              html += "</td>";
                              html += "<td>";
                              html += result.area.name;
                              html += "</td>";
                              html += "<td>";
                              html += result.date_submitted;
                              html += "</td>";
                              html += "<td>";
                              html += "Adviser/Panel";
                              html += "</td>";
                            html += "</tr>";
                        } else { //admin
                          var html = "<tr>";
                              html += "<td>";
                              html += count;
                              html += "</td>";
                              html += "<td>";
                              html += result.title;
                              html += "</td>";
                              html += "<td>";
                              html += result.date_submitted;
                              html += "</td>";
                              html += "<td>";
                              html += authors;
                              html += "</td>";
                              html += "<td>";
                              html += panels;
                              html += "</td>";
                              html += "<td>";
                              html += result.adviser.fullname;
                              html += "</td>";
                              html += "<td>";
                              html += result.area.name;
                              html += "</td>";
                              html += "<td>";
                              html += result.date_submitted;
                              html += "</td>";
                              html += "<td>";
                              html += result.call_number;
                              html += "</td>";
                              html += "<td>";
                              html += status;
                              html += actions;
                              html += "</td>";
                            html += "</tr>";
                        }

                            console.log(html);
                          $("#projectTableData").append(html);
                          count++;
                      });

                      $('#projectTable').attr('style', 'display:block');
                    }
                  })
                }
              } else {
                $('#projectTable').attr('style', 'display:none');
              }
            },
            barValueSpacing: 20,
            scales: {
              yAxes: [{
                ticks: {
                  min: 0,
                  precision: 0
                }
              }]
            }
          }
        });

      })
    </script>
@endpush