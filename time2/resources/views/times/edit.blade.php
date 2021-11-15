@extends('base')

@section('main')

    <div class="row">
        <div class="col-sm-12 shadow-lg p-3 mb-5 bg-white rounded">
            <h1 class="display">Edit time</h1>
            <a style="margin: 19px; float: right;" href="{{ route('times.index')}}" class="btn btn-danger">
                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-backspace-fill" fill="currentColor"
                     xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                          d="M15.683 3a2 2 0 0 0-2-2h-7.08a2 2 0 0 0-1.519.698L.241 7.35a1 1 0 0 0 0 1.302l4.843 5.65A2 2 0 0 0 6.603 15h7.08a2 2 0 0 0 2-2V3zM5.829 5.854a.5.5 0 1 1 .707-.708l2.147 2.147 2.146-2.147a.5.5 0 1 1 .707.708L9.39 8l2.146 2.146a.5.5 0 0 1-.707.708L8.683 8.707l-2.147 2.147a.5.5 0 0 1-.707-.708L7.976 8 5.829 5.854z"/>
                </svg>
            </a>
            <br>
            <td>{{$time->start}} | {{$time->end}}  </td>
            <br>
            <br>
            <div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div><br/>
                    <div class="row">
                        <div class="col-sm-12 shadow-lg p-3 mb-5 bg-white rounded">
                            @endif
                            <form method="post" action="{{ route('times.update', $time->id) }}">
                                @method('PATCH')
                                @csrf
                                <div class="form-group">
                                    <label for="start">Start:</label>
                                    <input type="datetime-local"
                                           value="{{old('start', $time->start->format('Y-m-d\TH:i'))}}" name="start">
                                    <label for="end">End:</label>
                                    <input type="datetime-local"
                                           value="{{old('end', $time->end->format('Y-m-d\TH:i'))}}" name="end">
                                    <input type="submit" class="btn btn-primary" value="Submit">
                                </div>
                            </form>
                        </div>
                    </div>
            </div>
        </div>
    </div>
@endsection
