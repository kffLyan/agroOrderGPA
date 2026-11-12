@extends('errors.layout')

@section('title', 'Terjadi Kesalahan Server')
@section('code', '500')
@section('description', 'Terjadi kesalahan pada server kami dan sistem telah mencatat laporan kejadian ini. Coba muat ulang halaman beberapa saat lagi.')

@section('actions')
    <a class="btn accent" href="javascript:location.reload()">↻ Muat Ulang</a>
    <a class="btn" href="{{ $dashboardUrl }}">{{ $dashboardLabel }}</a>
@endsection
