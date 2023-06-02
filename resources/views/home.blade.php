@extends('layouts.app')

@section('titulo')
    Página Principal
@endsection

@section('contenido')

    {{-- @forelse ($posts as $post)
        <h1>{{ $post->titulo }}</h1>
    @empty
        <p>No hay posts</p>
    @endforelse --}}
    
    <x-listar-post :posts="$posts" />

@endsection