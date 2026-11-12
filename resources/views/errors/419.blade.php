@extends('errors.layout')

@section('title', 'Sesi Kedaluwarsa')
@section('code', '419')
@section('description', 'Sesi Anda telah berakhir karena token keamanan kedaluwarsa. Muat ulang halaman lalu ulangi tindakan Anda.')

@section('actions')
    <a class="btn accent" href="javascript:location.reload()">↻ Muat Ulang Halaman</a>
    <a class="btn" href="{{ $dashboardUrl }}">{{ $dashboardLabel }}</a>
@endsection
