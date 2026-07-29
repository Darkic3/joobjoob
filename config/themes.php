<?php
return [
    'default' => 'neumorphism',
    'styles' => [
        'neumorphism' => [
            'card' => 'neu-card',
            'button_primary' => 'neu-btn neu-btn-primary',
            'section_large' => 'neu-flat-lg p-8 md:p-12 mb-12 relative overflow-hidden',
        ],
        'flat' => [
            'card' => 'bg-white rounded-lg shadow-sm border border-gray-200',
            'button_primary' => 'bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition',
            'section_large' => 'bg-gray-50 p-8 md:p-12 mb-12 rounded-xl',
        ],
        'glass' => [
            'card' => 'bg-white/30 backdrop-blur-md rounded-2xl border border-white/20 shadow-lg',
            'button_primary' => 'bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-xl hover:bg-white/30',
            'section_large' => 'bg-white/10 backdrop-blur-lg p-8 md:p-12 mb-12 rounded-3xl border border-white/20',
        ]
    ]
];
