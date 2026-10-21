<div class="mx-auto w-full max-w-3xl overflow-hidden rounded-2xl border-2 border-accent-deep/50 bg-white shadow-2xl">
    <svg viewBox="0 0 640 400" class="block h-auto w-full" aria-hidden="true" focusable="false">
        <rect width="640" height="400" fill="#f8faf3"/>
        <circle cx="500" cy="82" r="54" fill="#bef377" opacity=".3"/>
        <circle cx="500" cy="82" r="34" fill="#bef377"/>
        <g fill="#ffffff">
            <ellipse cx="110" cy="72" rx="40" ry="13"/>
            <ellipse cx="138" cy="61" rx="24" ry="14"/>
            <ellipse cx="86" cy="63" rx="20" ry="11"/>
            <ellipse cx="320" cy="112" rx="32" ry="10"/>
            <ellipse cx="342" cy="103" rx="18" ry="11"/>
            <ellipse cx="580" cy="150" rx="26" ry="8"/>
        </g>
        <path d="M0 250 C90 190 180 190 270 235 C360 280 450 170 640 220 V400 H0 Z" fill="#d6efae"/>
        <path d="M0 285 C120 240 230 250 330 275 C430 300 520 245 640 270 V400 H0 Z" fill="#a9d86a"/>
        <path d="M0 300 H640 V400 H0 Z" fill="#cfe8a0"/>
        <path d="M0 322 H640 M0 352 H640 M0 388 H640" stroke="#a9d86a" stroke-width="3" fill="none"/>

        @foreach ([[318, .55, 30], [348, .8, 20]] as [$y, $s, $off])
            @for ($x = $off; $x < 640; $x += 90)
                @if (abs($x - 405) > 45)
                    <g transform="translate({{ $x }} {{ $y }}) scale({{ $s }})">
                        <ellipse cy="5" rx="18" ry="6" fill="#153a01" opacity=".18"/>
                        <ellipse cy="-2" rx="17" ry="12" fill="#2f6a12"/>
                        <ellipse cy="-5" rx="11" ry="8" fill="#8fbf4a"/>
                        <path d="M-4 -6 Q0 -12 4 -6" stroke="#bef377" stroke-width="2" fill="none" stroke-linecap="round"/>
                    </g>
                @endif
                <g transform="translate({{ $x + 45 }} {{ $y + 3 }}) scale({{ $s }})" stroke="#2f6a12" stroke-width="3" stroke-linecap="round" fill="none">
                    <path d="M0 0 V-12 M0 -6 L-6 -12 M0 -6 L6 -12"/>
                </g>
            @endfor
        @endforeach

        <ellipse cx="400" cy="352" rx="36" ry="6" fill="#153a01" opacity=".2"/>
        <path d="M438 176 L452 346" stroke="#2f6a12" stroke-width="5" stroke-linecap="round"/>
        <path d="M452 346 L428 340 L424 352 L452 355 Z" fill="#153a01"/>
        <rect x="385" y="270" width="12" height="76" rx="5" fill="#153a01"/>
        <rect x="404" y="270" width="12" height="76" rx="5" fill="#153a01"/>
        <ellipse cx="390" cy="348" rx="12" ry="5" fill="#0d2600"/>
        <ellipse cx="411" cy="348" rx="12" ry="5" fill="#0d2600"/>
        <path d="M378 216 Q378 204 390 204 H410 Q422 204 422 216 V276 H378 Z" fill="#bef377"/>
        <rect x="378" y="266" width="44" height="6" fill="#153a01"/>
        <rect x="406" y="224" width="9" height="10" rx="2" fill="#8fbf4a"/>
        <path d="M381 212 L372 262" stroke="#a9d86a" stroke-width="13" stroke-linecap="round"/>
        <path d="M419 214 L436 187" stroke="#a9d86a" stroke-width="13" stroke-linecap="round"/>
        <circle cx="372" cy="267" r="6" fill="#f0d2b0"/>
        <circle cx="437" cy="184" r="6.5" fill="#f0d2b0"/>
        <rect x="395" y="196" width="10" height="11" fill="#f0d2b0"/>
        <circle cx="400" cy="184" r="16" fill="#f0d2b0"/>
        <circle cx="407" cy="185" r="1.6" fill="#153a01"/>
        <path d="M402 191 Q407 194 411 190" stroke="#153a01" stroke-width="1.6" fill="none" stroke-linecap="round"/>
        <path d="M383 181 C383 163 417 163 417 181 Z" fill="#153a01"/>
        <rect x="383" y="177" width="34" height="4" fill="#bef377"/>
        <path d="M413 180 H437 Q439 184 433 184 H413 Z" fill="#153a01"/>
    </svg>
</div>
