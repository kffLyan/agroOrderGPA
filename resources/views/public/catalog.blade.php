@extends('layouts.public')

@section('title', 'Katalog Komoditas Agribisnis | AgroOrder GPA')

@section('content')

<x-public.catalog-hero :hero="$hero" :breadcrumb="$breadcrumb" />

<div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:py-8">
    <div x-data="clientCatalog(@js($commodities))" class="flex flex-col gap-6">
        <x-public.catalog-toolbar :toolbar="$toolbar" />

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="flex flex-col gap-6 lg:col-span-2">
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($commodities as $item)
                        <x-public.catalog-product :item="$item" />
                    @endforeach
                </div>

                <p x-show="visibleCount() === 0" x-cloak
                    class="rounded-xl border border-dashed border-line-board bg-surface px-4 py-8 text-center text-xs text-ink-body">
                    {{ $toolbar['emptyState'] }}
                </p>

                <x-public.catalog-contract :contract="$contract" />
            </div>

            <aside class="flex flex-col gap-6 lg:sticky lg:top-28 lg:h-fit lg:self-start">
                <x-public.catalog-validation :validation="$validation" />
                <x-public.catalog-cart :cart="$cart" />
            </aside>
        </div>

    </div>
</div>

@endsection
