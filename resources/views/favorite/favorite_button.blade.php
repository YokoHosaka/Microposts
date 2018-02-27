@if (Auth::user()->id)
    @if (Auth::user()->is_favorite($micropost->id))
        {!! Form::open(['route' => ['favorite.drop_favorite', $micropost->id], 'method' => 'delete', 'style' => 'display: inline;']) !!}
            {!! Form::submit('お気に入りから外す', ['class' => "btn btn-xs btn-danger"]) !!}
        {!! Form::close() !!}
    @else
        {!! Form::open(['route' => ['favorite.add_favorite', $micropost->id], 'style' => 'display: inline;']) !!}
            {!! Form::submit('お気に入りに登録', ['class' => "btn btn-xs btn-primary"]) !!}
        {!! Form::close() !!}
    @endif
@endif
         