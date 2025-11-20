<table class="table table-head-fixed">
    <thead>
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Machine</th>
    </tr>
    </thead>
    <tbody>
    @foreach($reports as $report)
<!--        --><?//= var_dump($report);die();?>
        <tr>
            <td>{{$report->id}}</td>
            <td style="width:100%">{{$report->title}}</td>
            <td style="text-overflow: ellipsis;">

            @foreach($report->reportMachines as $machine)
<?//= var_dump($machine);die();?>
                        <p>{{$machine->trailed_equipment_type}}</p>

            @endforeach

            </td>
        </tr>
    @endforeach
<!--    --><?//= die();?>
    </tbody>
</table>