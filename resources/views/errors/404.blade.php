@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')
@section('code', '404')
@section('description', 'Halaman yang Anda cari tidak ada atau alamatnya salah. Periksa kembali URL, atau kembali ke dashboard operasional Anda.')

@section('actions')
    <a class="btn" href="javascript:history.back()">← Kembali</a>
    <a class="btn accent" href="{{ $dashboardUrl }}">{{ $dashboardLabel }}</a>
@endsection
