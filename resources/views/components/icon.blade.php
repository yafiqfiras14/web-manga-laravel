@props(['name' => 'music', 'size' => 20, 'stroke' => 1.8])

<svg
    xmlns="http://www.w3.org/2000/svg"
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="{{ $stroke }}"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    {{ $attributes->merge(['class' => 'icon']) }}
>
    @switch($name)
        @case('play')
            <path d="M7 4.5v15l13-7.5-13-7.5Z" fill="currentColor" stroke="none" />
            @break

        @case('pause')
            <rect x="6.5" y="4.5" width="4" height="15" rx="1" fill="currentColor" stroke="none" />
            <rect x="13.5" y="4.5" width="4" height="15" rx="1" fill="currentColor" stroke="none" />
            @break

        @case('skip-back')
            <path d="M19 20 9 12l10-8v16Z" fill="currentColor" stroke="none" />
            <path d="M5 19V5" />
            @break

        @case('skip-forward')
            <path d="m5 4 10 8-10 8V4Z" fill="currentColor" stroke="none" />
            <path d="M19 5v14" />
            @break

        @case('shuffle')
            <path d="M16 3h5v5" />
            <path d="M4 20 21 3" />
            <path d="M21 16v5h-5" />
            <path d="m15 15 6 6" />
            <path d="M4 4l5 5" />
            @break

        @case('repeat')
            <path d="m17 2 4 4-4 4" />
            <path d="M3 11v-1a4 4 0 0 1 4-4h14" />
            <path d="m7 22-4-4 4-4" />
            <path d="M21 13v1a4 4 0 0 1-4 4H3" />
            @break

        @case('repeat-one')
            <path d="m17 2 4 4-4 4" />
            <path d="M3 11v-1a4 4 0 0 1 4-4h14" />
            <path d="m7 22-4-4 4-4" />
            <path d="M21 13v1a4 4 0 0 1-4 4H3" />
            <path d="M11 10h1v4" />
            @break

        @case('heart')
            <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" />
            @break

        @case('heart-filled')
            <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" fill="currentColor" />
            @break

        @case('volume')
            <path d="M11 5 6 9H2v6h4l5 4V5Z" />
            <path d="M15.5 8.5a5 5 0 0 1 0 7" />
            <path d="M18.5 5.5a9 9 0 0 1 0 13" />
            @break

        @case('volume-mute')
            <path d="M11 5 6 9H2v6h4l5 4V5Z" />
            <path d="m22 9-6 6" />
            <path d="m16 9 6 6" />
            @break

        @case('queue')
            <path d="M4 6h11" />
            <path d="M4 12h11" />
            <path d="M4 18h7" />
            <circle cx="18" cy="16" r="3" />
            <path d="M21 16V7l-3 1" />
            @break

        @case('search')
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-4-4" />
            @break

        @case('home')
            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
            @break

        @case('compass')
            <circle cx="12" cy="12" r="9" />
            <path d="m16 8-2.5 5.5L8 16l2.5-5.5L16 8Z" />
            @break

        @case('music')
            <path d="M9 18V5l12-2v13" />
            <circle cx="6" cy="18" r="3" />
            <circle cx="18" cy="16" r="3" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
            @break

        @case('close')
            <path d="M18 6 6 18" />
            <path d="m6 6 12 12" />
            @break

        @case('plus')
            <path d="M12 5v14" />
            <path d="M5 12h14" />
            @break

        @case('trash')
            <path d="M3 6h18" />
            <path d="M8 6V4h8v2" />
            <path d="M19 6l-1 14H6L5 6" />
            <path d="M10 11v5" />
            <path d="M14 11v5" />
            @break

        @case('pencil')
            <path d="M17 3a2.8 2.8 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3Z" />
            @break

        @case('logout')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <path d="m16 17 5-5-5-5" />
            <path d="M21 12H9" />
            @break

        @case('external')
            <path d="M15 3h6v6" />
            <path d="M10 14 21 3" />
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
            @break

        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            @break

        @case('user')
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
            @break

        @case('disc')
            <circle cx="12" cy="12" r="9" />
            <circle cx="12" cy="12" r="2.5" />
            @break

        @case('sparkles')
            <path d="m12 3 1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8L12 3Z" />
            <path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8L19 15Z" />
            @break

        @case('arrow-left')
            <path d="M19 12H5" />
            <path d="m12 19-7-7 7-7" />
            @break

        @case('chevron-left')
            <path d="m15 18-6-6 6-6" />
            @break

        @case('chevron-right')
            <path d="m9 18 6-6-6-6" />
            @break

        @case('menu')
            <path d="M4 6h16" />
            <path d="M4 12h16" />
            <path d="M4 18h16" />
            @break

        @case('check')
            <path d="m5 12 5 5L20 7" />
            @break

        @case('headphones')
            <path d="M4 14v-2a8 8 0 0 1 16 0v2" />
            <path d="M18 19c0 1.1-.9 2-2 2h-1v-6h3v4Z" />
            <path d="M6 19c0 1.1.9 2 2 2h1v-6H6v4Z" />
            @break

        @case('wave')
            <path d="M3 12h2l2-5 3 12 3-16 3 12 2-3h3" />
            @break

        @case('sun')
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2v2" />
            <path d="M12 20v2" />
            <path d="m4.9 4.9 1.4 1.4" />
            <path d="m17.7 17.7 1.4 1.4" />
            <path d="M2 12h2" />
            <path d="M20 12h2" />
            <path d="m6.3 17.7-1.4 1.4" />
            <path d="m19.1 4.9-1.4 1.4" />
            @break

        @case('moon')
            <path d="M21 12.8A8.5 8.5 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" />
            @break

        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
