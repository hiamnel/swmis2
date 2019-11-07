<!DOCTYPE html>
<html>
<head>
    <style>
        /* optional styles */
        @page {
            /* size: auto; */
            margin-top: 4cm;
            margin-header: 1cm;
            margin-footer: 1cm;
            header: page-header;
            footer: page-footer;
            text-align: center;
        }
        p {
            padding: -5px 0px;
        }

        table {
            width: 100%;
            border: 1px black;
            font-size:12px;
            overflow:wrap;
        }

        tr, td {
        	border: 1px black;
            padding: 3px;
            font-size:12px;
        }

    </style>
</head>
<body>
    <!-- optional -->
    <htmlpageheader name="page-header">
    	<?php $image_path = '/img/logo_clean.png'; ?>
		<div align="center"><img src="{{ public_path() . $image_path }}" height="120px"></div>
    </htmlpageheader> 
    <!-- optional -->
    <htmlpagefooter name="page-footer">
        <table>
            <tr>
                <td align="right">
                    Page {PAGENO} of {nb}
                </td>
            </tr>
        </table>
    </htmlpagefooter>

    <br>
    <div>
        <div>
        	<h3 align="center">{{ $semester }}</h3>
            <table class="table table-border">
            	<thead>

            		@if ($isAdviser)
						<tr class="bg-primary text-white text-center">
	                      <th>Project Title</th>
                          <th>Authors</th>
                          <th>Adviser</th>
                          <th>Subject Area</th>
                          <th>Date</th>
                          <th>Role</th>
	                  	</tr>
                  	@else 
                  		<tr class="bg-primary text-white text-center">
	                      <th></th>
	                      <th>Project Title</th>
	                      <th>Authors</th>
	                      <th>Panels</th>
	                      <th>Adviser</th>
	                      <th>Subject Area</th>
	                      <th>Date</th>
	                      <th>Call Number</th>
	                  	</tr>
                  	@endif
            	</thead>
            	<?php $count = 1; ?>
                @foreach($results as $result)
                <?php 
                	$authors = "";
            		$panels = "";

            		$authorCount = 1;
	            	$panelCount = 1;

	            	foreach ($result->authors as $author) {
		            	$authors = $authors . $authorCount . ". " . $author->fullname . "\r\n";
		            	$authorCount++;
		            }

		            foreach ($result->panel as $panel) {
		            	$panels = $panels . $panelCount . ". " . $panel->fullname . "\r\n";
		            	$panelCount++;
		            }
                ?>
                @if ($isAdviser)
                    <tr>
                    	<td>{{ $count }}</td>
                        <td>{{ $result->title }}</td>
                        <td>{{ $authors }}</td>
                        <td>{{ $result->adviser->fullname }}</td>
                        <td width="10%">{{ $result->area->name }}</td>
                        <td>{{ $result->date_submitted }}</td>
                        <td width="10%">Adviser</td>
                    </tr>
                @else
                  	<tr>
                    	<td>{{ $count }}</td>
                        <td>{{ $result->title }}</td>
                        <td>{{ $authors }}</td>
                        <td>{{ $panels }}</td>
                        <td>{{ $result->adviser->fullname }}</td>
                        <td width="10%">{{ $result->area->name }}</td>
                        <td>{{ $result->date_submitted }}</td>
                        <td width="10%">{{ $result->call_number }}</td>
                    </tr>
                @endif

                <?php $count++ ?>
                @endforeach
            </table>
        </div> 
    </div>
</body>
</html>

foreach ($results as $key => $result) {
	            	$authorCount = 1;
	            	$panelCount = 1;

	            	foreach ($result->authors as $author) {
		            	$authors = $authors . $authorCount . ". " . $author->fullname . "<br>";
		            	$authorCount++;
		            }

		            foreach ($result->panel as $panel) {
		            	$panels = $panels . $panelCount . ". " . $panel->fullname . "<br>";
		            	$panelCount++;
		            }
	            }