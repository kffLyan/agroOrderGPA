@props(['title', 'description' => null, 'time' => null])

<div data-gpa-corner class="fixed bottom-4 left-4 right-4 z-50 sm:left-auto sm:max-w-[420px]">
    <x-gpa.notification-banner :title="$title" :description="$description" :time="$time" class="shadow-lg">
        <button type="button" aria-label="Tutup" onclick="this.closest('[data-gpa-corner]').remove()" class="rounded p-0.5 text-base leading-none text-ink-body/50 hover:text-ink transition-colors">&times;</button>
    </x-gpa.notification-banner>
</div>
