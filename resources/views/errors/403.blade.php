@extends('errors.layout')

@section('title', 'Akses Ditolak')
@section('code', '403')
@section('description', 'Anda tidak memiliki wewenang atau hak akses untuk membuka modul ini.')

@section('actions')
    <a class="btn" href="javascript:history.back()">← Kembali</a>
    <a class="btn accent" href="{{ $dashboardUrl }}">{{ $dashboardLabel }}</a>
@endsection
