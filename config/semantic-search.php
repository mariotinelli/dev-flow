<?php

declare(strict_types = 1);

return [
    'enabled' => env('SEMANTIC_SEARCH_ENABLED', true),

    'embeddings' => [
        'provider'   => env('SEMANTIC_SEARCH_EMBEDDING_PROVIDER', 'openai'),
        'model'      => env('SEMANTIC_SEARCH_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'dimensions' => 1536,
    ],

    'chunking' => [
        'size'    => 1200,
        'overlap' => 200,
    ],
];
