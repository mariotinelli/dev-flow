<?php

declare(strict_types = 1);

return [
    'enabled' => env('SEMANTIC_SEARCH_ENABLED', true),

    'embeddings' => [
        'provider'   => env('SEMANTIC_SEARCH_EMBEDDING_PROVIDER', 'openai'),
        'model'      => env('SEMANTIC_SEARCH_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'dimensions' => 1536,
    ],

    'retrieval' => [
        'limit'          => (int) env('SEMANTIC_SEARCH_RETRIEVAL_LIMIT', 10),
        'min_similarity' => (float) env('SEMANTIC_SEARCH_MIN_SIMILARITY', 0.4),
    ],

    'chunking' => [
        'size'    => 1200,
        'overlap' => 200,
    ],
];
