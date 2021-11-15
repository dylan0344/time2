@extends('base')

@section('main')
    <div class="row">
        <div class="col-sm-12 shadow-lg p-3 mb-5 bg-white ">
            <h1 class="display">Timesheet</h1>
            <a style="margin: 19px;" href="{{ route('times.create')}}" class="btn btn-primary">New time</a>

            <form method="get" action='/generate-docx'>
                <input class="btn btn-primary" type="submit" value="Export to Word"/>
                <label for="startdate">Start Date:</label>
                <input type="date" id="startDate" name="startDate">
                <label for="enddate">End Date:</label>
                <input type="date" id="endDate" name="endDate">
            </form>
            <table class="table table-hover table-striped">
                <thead>
                <tr>
                    <th scope="col">Start</th>
                    <th scope="col">End</th>
                    <th scope="col">Worked hours</th>
                    <th scope="col">Week number</th>
                </tr>
                </thead>
                <tbody>
                @foreach($times as $time)
                    <tr>
                        <td>{{$time->start}}</td>
                        <td>{{$time->end}}</td>
                        <td>{{$time->getDateDiff()}}</td>
                        <td>{{$time->week_number}}</td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic example">
                                <a href="{{route('times.edit', $time)}}" class="btn btn-warning" type="button">
                                    <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-pencil"
                                         fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                              d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5L13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175l-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                                    </svg>

                                </a>
                            </div>
                        </td>
                        <td>
                            <form action="{{ route('times.destroy', $time)}}" method="post">
                                @csrf
                                @method('DELETE')
                                <input name="_method" type="hidden" value="DELETE">
                                <button type="submit" class="btn btn-danger  show_confirm"
                                        data-toggle="tooltip" title='Delete'>
                                    <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-x-circle-fill"
                                         fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                              d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/>
                                    </svg>
                                    </a>Delete </i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>


                {!! $times->links() !!}

        </div>
    </div>
    <script type="text/javascript"
            src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript">
        $('.show_confirm').click(function (e) {
            if (!confirm('Are you sure you want to delete this?')) {
                e.preventDefault();
            }
        });
    </script>
    <div class="col-sm-12">

        @if(session()->get('success'))
            <div class="alert alert-success">
                {{ session()->get('success') }}
            </div>
        @endif
    </div>

@endsection
