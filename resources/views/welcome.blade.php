@extends('layouts.home')
@section('content')
<div>
    @foreach ($noticiasDestacadas as $noticia)
        <x-featured-article :noticia="$noticia" />
    @endforeach
</div>
@endsection