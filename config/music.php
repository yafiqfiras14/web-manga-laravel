<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas situs
    |--------------------------------------------------------------------------
    */

    'site' => [
        'name' => 'SoundKu',
        'tagline' => 'Streaming musik dari SoundCloud',
    ],

    /*
    |--------------------------------------------------------------------------
    | Genre
    |--------------------------------------------------------------------------
    |
    | "urn" dipakai untuk endpoint charts SoundCloud. Kalau chart untuk genre
    | tersebut kosong, aplikasi otomatis fallback ke pencarian "query".
    |
    */

    'genres' => [
        'all-music' => [
            'label' => 'Semua Genre',
            'urn' => 'soundcloud:genres:all-music',
            'query' => 'music',
        ],
        'electronic' => [
            'label' => 'Electronic',
            'urn' => 'soundcloud:genres:electronic',
            'query' => 'electronic music',
        ],
        'hiphoprap' => [
            'label' => 'Hip-hop / Rap',
            'urn' => 'soundcloud:genres:hiphoprap',
            'query' => 'hip hop rap',
        ],
        'pop' => [
            'label' => 'Pop',
            'urn' => 'soundcloud:genres:pop',
            'query' => 'pop music',
        ],
        'house' => [
            'label' => 'House',
            'urn' => 'soundcloud:genres:house',
            'query' => 'house music',
        ],
        'techno' => [
            'label' => 'Techno',
            'urn' => 'soundcloud:genres:techno',
            'query' => 'techno',
        ],
        'chillout' => [
            'label' => 'Chill / Lo-fi',
            'urn' => 'soundcloud:genres:chillout',
            'query' => 'lofi chill',
        ],
        'rb' => [
            'label' => 'R&B / Soul',
            'urn' => 'soundcloud:genres:rbsoul',
            'query' => 'rnb soul',
        ],
        'rock' => [
            'label' => 'Rock',
            'urn' => 'soundcloud:genres:rock',
            'query' => 'rock music',
        ],
        'jazzblues' => [
            'label' => 'Jazz & Blues',
            'urn' => 'soundcloud:genres:jazzblues',
            'query' => 'jazz blues',
        ],
        'latin' => [
            'label' => 'Latin',
            'urn' => 'soundcloud:genres:latin',
            'query' => 'latin music',
        ],
        'soundtrack' => [
            'label' => 'Soundtrack',
            'urn' => 'soundcloud:genres:soundtrack',
            'query' => 'soundtrack',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Section kurasi di halaman utama
    |--------------------------------------------------------------------------
    */

    'sections' => [
        'chill' => [
            'title' => 'Chill & Lo-fi',
            'subtitle' => 'Teman fokus, kerja, dan tidur larut malam.',
            'query' => 'lofi chill',
            'limit' => 8,
        ],
        'fresh' => [
            'title' => 'Baru Naik',
            'subtitle' => 'Track yang lagi rame didengerin minggu ini.',
            'query' => 'new music 2026',
            'limit' => 8,
        ],
    ],

];
