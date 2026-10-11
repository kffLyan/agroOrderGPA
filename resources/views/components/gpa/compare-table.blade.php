@props([
    'rows' => [],
    'regulerLabel' => 'Klien Reguler / Instan',
    'enterpriseLabel' => 'Klien Kontrak B2B (Enterprise)',
])

<div {{ $attributes->merge(['class' => 'gpa-panel overflow-hidden']) }}>
    <div class="flex items-center justify-between gap-3 bg-surface-muted px-4 py-2.5">
        <h2 class="gpa-label uppercase tracking-wider text-ink">Matriks Perbandingan Hak Akses Sistem GPA</h2>
        <span class="gpa-eyebrow hidden sm:inline">Panduan Pemilihan Akun</span>
    </div>

    <div class="gpa-scroll-x hidden md:block">
        <table class="w-full border-collapse text-left">
            <caption class="sr-only">Perbandingan hak akses klien reguler dan klien kontrak B2B</caption>
            <thead>
                <tr class="border-b border-line bg-surface-muted gpa-mono-xs uppercase text-ink">
                    <th scope="col" class="w-[28%] border-r border-line px-3 py-2 font-semibold">Parameter Layanan</th>
                    <th scope="col" class="w-[36%] border-r border-line bg-surface-raised/60 px-3 py-2 font-semibold">
                        {{ $regulerLabel }}
                    </th>
                    <th scope="col" class="px-3 py-2 font-semibold">{{ $enterpriseLabel }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line-faint">
                @foreach ($rows as $row)
                    <tr class="transition-colors hover:bg-surface-muted">
                        <th scope="row" class="border-r border-line px-3 py-2.5 text-left align-top text-2xs font-semibold text-ink">
                            {{ $row['label'] }}
                        </th>
                        <td
                            class="border-r border-line px-3 py-2.5 align-top text-2xs leading-relaxed {{ ($row['highlight'] ?? false) ? 'font-semibold text-ink' : 'text-ink-muted' }}">
                            {{ $row['reguler'] }}
                        </td>
                        <td class="px-3 py-2.5 align-top text-2xs leading-relaxed text-ink-muted">
                            {{ $row['enterprise'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <ul class="divide-y divide-line-faint md:hidden">
        @foreach ($rows as $row)
            <li class="p-3">
                <p class="mb-2 text-2xs font-semibold uppercase tracking-wide text-ink">{{ $row['label'] }}</p>
                <div class="space-y-2">
                    <div class="rounded bg-surface-muted p-2 shadow-card">
                        <p class="gpa-eyebrow mb-0.5">{{ $regulerLabel }}</p>
                        <p class="text-2xs leading-relaxed {{ ($row['highlight'] ?? false) ? 'font-semibold text-ink' : 'text-ink-muted' }}">
                            {{ $row['reguler'] }}
                        </p>
                    </div>
                    <div class="rounded bg-surface p-2 shadow-card">
                        <p class="gpa-eyebrow mb-0.5">{{ $enterpriseLabel }}</p>
                        <p class="text-2xs leading-relaxed text-ink-muted">{{ $row['enterprise'] }}</p>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>
