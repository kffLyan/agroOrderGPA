@extends('errors.layout')

@section('title', 'Terlalu Banyak Permintaan')
@section('code', '429')
@section('description', 'Anda mengirim permintaan terlalu cepat. Tunggu beberapa saat, lalu coba lagi.')

@section('actions')
    <a class="btn accent" href="javascript:location.reload()">↻ Coba Lagi</a>
    <a class="btn" href="{{ $dashboardUrl }}">{{ $dashboardLabel }}</a>
@endsection
