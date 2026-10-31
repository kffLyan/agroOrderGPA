@extends('layouts.public')

@section('title', 'Katalog Komoditas Agribisnis | AgroOrder GPA')

@section('content')

<x-public.catalog-hero />

<div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:py-8">
    <div x-data="clientCatalog(@js($commodities))" x-on:keydown.escape.window="mobileCartOpen = false" class="flex flex-col gap-6">
        <x-public.catalog-toolbar />

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="flex flex-col gap-6 lg:col-span-2">
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                    @foreach ($commodities as $item)
                        <x-public.catalog-product :item="$item" />
                    @endforeach
                </div>

                <p x-show="visibleCount() === 0" x-cloak
                    class="rounded-xl border border-dashed border-line-board bg-surface px-4 py-8 text-center text-xs text-ink-body">
                    Tidak ada komoditas yang sesuai dengan kriteria pencarian dan filter saat ini.
                </p>

                <x-public.catalog-contract />
            </div>

            <aside class="hidden lg:flex flex-col gap-6 lg:sticky lg:top-28 lg:h-fit lg:self-start">
                <x-public.catalog-validation />
                <x-public.catalog-cart />
            </aside>
        </div>

        {{-- Mobile floating bar --}}
        <div x-show="draftCount() > 0" x-cloak x-data="{ mobileCartOpen: false }" class="lg:hidden pb-24">
            <div class="fixed inset-x-0 bottom-0 z-40 px-3 pb-[calc(env(safe-area-inset-bottom,0)+0.75rem)]"
                x-show="draftCount() > 0 && !mobileCartOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-y-full opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="translate-y-full opacity-0">
                <button type="button" @click="mobileCartOpen = true"
                    class="mx-auto flex w-full max-w-md items-center justify-between gap-3 rounded-full border border-brand-strong/20 bg-brand-deep px-4 py-2.5 text-white shadow-pop">
                    <div class="flex items-center gap-2">
                        <x-gpa.icon name="cart" class="h-4 w-4" />
                        <span class="text-xs font-semibold">
                            <span x-text="draftCount()"></span> Item • <span x-text="money(draftTotal())"></span>
                        </span>
                    </div>
                    <span class="flex items-center gap-1 text-xs font-bold">
                        Lihat Draft Pesanan
                        <svg class="h-3 w-3" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4.5 2.25L8.25 6L4.5 9.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                </button>
            </div>

            {{-- Bottom sheet --}}
            <div x-show="mobileCartOpen" x-cloak class="fixed inset-0 z-50 lg:hidden">
                <div class="absolute inset-0 bg-black/60 transition-opacity duration-300"
                    x-show="mobileCartOpen"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="mobileCartOpen = false"></div>
                <div class="absolute inset-x-0 bottom-0 flex max-h-[90vh] flex-col rounded-t-2xl bg-canvas shadow-2xl will-change-transform"
                    x-show="mobileCartOpen"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="translate-y-full"
                    x-transition:enter-end="translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="translate-y-0"
                    x-transition:leave-end="translate-y-full">
                    <div class="flex items-center justify-between gap-3 border-b border-line-hair px-4 py-3">
                        <div class="flex items-center gap-2">
                            <x-gpa.icon name="cart" class="h-4 w-4 text-brand-strong" />
                            <h3 class="text-sm font-bold text-ink-strong">Draf Pesanan Komoditas</h3>
                            <span x-show="draftCount() > 0" class="rounded-full bg-accent px-2 py-0.5 gpa-micro-bold text-brand-deep">
                                <span x-text="draftCount()"></span> Item
                            </span>
                        </div>
                        <button type="button" @click="mobileCartOpen = false" class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-surface-shell">
                            <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto px-4 py-3">
                        <x-public.catalog-cart :show-action="false" />
                    </div>
                    <div class="border-t border-line-hair px-4 py-3 pb-[calc(env(safe-area-inset-bottom,0)+0.75rem)]">
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-semibold text-ink-body">Total Estimasi</span>
                            <span class="text-base font-extrabold text-brand-strong" x-text="money(draftTotal())"></span>
                        </div>
                        <x-gpa.btn type="button" variant="accent" size="lg" block
                            @click="submitDraft(); mobileCartOpen = false"
                            x-bind:disabled="draftCount() === 0">
                            Kirim Permintaan ke Sekretaris GPA
                        </x-gpa.btn>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
