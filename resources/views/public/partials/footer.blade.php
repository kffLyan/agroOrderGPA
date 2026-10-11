<footer id="kontak" class="mt-14 scroll-mt-36 bg-brand text-white">
    <div class="border-t border-white/10">
        <div class="mx-auto w-full max-w-6xl space-y-10 px-4 py-12 sm:px-6 md:py-14">
            <div class="grid gap-10 md:grid-cols-3">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent text-brand-deep">
                            <x-gpa.icon name="leaf" class="h-5 w-5" />
                        </span>
                        <span class="text-xl font-extrabold uppercase leading-none">AgroOrder GPA</span>
                    </div>

                    <p class="max-w-md text-sm leading-relaxed text-white/90">{{ $footer['tagline'] }}</p>

                    <dl class="space-y-1 pt-2 gpa-mono-xs">
                        @foreach ($footer['identity'] as $line)
                            <dd class="text-white/80">{{ $line }}</dd>
                        @endforeach
                    </dl>
                </div>

                <div class="space-y-3">
                    <h2 class="gpa-mono-xs font-bold uppercase tracking-wider text-white">Hub Operasional</h2>

                    <ul class="space-y-4">
                        @foreach ($footer['hubs'] as $hub)
                            <li class="space-y-1">
                                <p class="flex items-center gap-2.5 gpa-mono-xs font-bold text-accent">
                                    <x-gpa.icon name="map-pin" class="h-3.5 w-3.5 shrink-0" />
                                    {{ $hub['label'] }}
                                </p>
                                <p class="pl-6 text-2xs leading-relaxed text-white/80">{{ $hub['address'] }}</p>
                            </li>
                        @endforeach
                    </ul>

                    <p class="pt-1 text-2xs leading-relaxed text-white/90">
                        @foreach ($footer['contacts'] as $line)
                            {{ $line }}@if (! $loop->last)<br> @endif
                        @endforeach
                    </p>
                </div>

                <div class="space-y-3">
                    <h2 class="gpa-mono-xs font-bold uppercase tracking-wider text-white">Navigasi Publik</h2>

                    <ul class="space-y-2.5">
                        @foreach ($footer['navigation'] as $item)
                            @php
                                $href = match (true) {
                                    str_starts_with($item['href'], '#') => $item['href'],
                                    str_starts_with($item['href'], '/') => url($item['href']),
                                    default => route($item['href']),
                                };
                            @endphp
                            <li>
                                <a href="{{ $href }}"
                                    class="gpa-mono-xs text-white/80 transition-colors hover:text-accent">{{ $item['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div
                class="flex flex-col gap-3 border-t border-white/10 pt-6 gpa-mono-xs text-white/70 md:flex-row md:items-center md:justify-between">
                <p>{{ $footer['copyright'] }}</p>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <p class="flex items-center gap-1.5 text-accent">
                        <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                        {{ $footer['status'] }}
                    </p>

                    @foreach ($footer['policies'] as $policy)
                        <p>• {{ $policy }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</footer>