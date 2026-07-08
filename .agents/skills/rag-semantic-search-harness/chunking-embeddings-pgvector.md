# Chunking, Embeddings, and pgvector

## Embedding Rules

- Embeddings are dense vector representations of meaning; they are not hashes.
- Query and document embeddings must use the same model, version, dimensions, and preprocessing.
- Changing embedding model means re-embedding the corpus.
- Dimension reduction/truncation must be benchmarked on the domain golden set.
- Cache embeddings by normalized content hash to avoid re-embedding unchanged chunks.
- Store embedding model, version, dimensions, and content hash with every vector.

## Language and Domain Fit

Do not rely only on public leaderboards such as MTEB. Test with project data.

Common PT-BR issues:

- English-first embeddings can underperform on Portuguese synonyms and domain terms.
- Legal, medical, financial, fiscal, SKU/product and code-like domains often need hybrid search.
- Acronyms, part numbers, CNPJ/CPF, invoice numbers, and product codes are lexical signals; dense embeddings alone are weak.

## Similarity and Normalization

Cosine similarity measures vector direction. Inner product only behaves like cosine when vectors are normalized.

pgvector operators:

- `<=>`: cosine distance. Safe default for LLM embeddings.
- `<#>`: negative inner product. Use only when vectors are normalized and benchmarked.
- `<->`: Euclidean/L2 distance. Use only when chosen deliberately.

Default recommendation:

```sql
ORDER BY embedding <=> :query_embedding
```

Use inner product only when you own normalization and have eval evidence.

## Chunking Principles

Chunking is a retrieval quality decision, not a fixed constant.

Prefer semantic boundaries:

- Markdown headings.
- HTML headings/sections.
- Paragraphs.
- Tables as atomic units when possible.
- FAQ question-answer pairs as atomic units.
- Legal clauses/articles.
- Product specs grouped by product/variant.
- Support tickets grouped by problem/resolution.

Avoid:

- Blind fixed-size chunks that cut sentences or tables.
- Giant chunks mixing unrelated topics.
- Tiny chunks that lose referents such as “it”, “this policy”, “the product”.
- Overlap so large that the same fact appears in every candidate.

## Starting Chunk Sizes

Use as initial hypotheses, not final truth:

- FAQ/policies: 200-500 tokens, overlap 25-75.
- Technical docs: 400-800 tokens, overlap 50-120.
- Contracts/legal: clause/article based; keep clause title and parent section.
- PDFs with tables: keep table block intact; include page and caption.
- Chat/tickets: problem + accepted answer/resolution as one chunk when possible.

Evaluate chunking with Recall@k and context precision.

## Chunk Text Construction

The embedded text can include helpful context beyond body text:

```txt
Document: Refund Policy
Section: Subscription cancellations > Annual plans
Updated: 2026-05-10

Customers may cancel annual plans within 7 days...
```

This improves retrieval for short or pronoun-heavy chunks. Keep metadata concise to avoid drowning content.

## Metadata to Store

Minimum fields:

- `id`
- `tenant_id` or visibility scope
- `document_id`
- `chunk_index`
- `source_type`
- `source_uri`
- `title`
- `section_path`
- `content`
- `content_hash`
- `language`
- `embedding_model`
- `embedding_dimensions`
- `embedding`
- `created_at`
- `updated_at`

Useful extras:

- `page_number`
- `bbox` for PDF/OCR provenance
- `published_at`
- `source_authority`
- `trust_score`
- `deleted_at` only if soft-delete is compatible with retrieval filtering

## PostgreSQL/pgvector Table Pattern

Conceptual shape:

```sql
CREATE TABLE knowledge_chunks (
    id bigserial PRIMARY KEY,
    tenant_id bigint NOT NULL,
    document_id bigint NOT NULL,
    chunk_index integer NOT NULL,
    source_uri text NULL,
    title text NULL,
    section_path text NULL,
    content text NOT NULL,
    content_hash char(64) NOT NULL,
    language varchar(16) NULL,
    embedding_model varchar(100) NOT NULL,
    embedding vector(1536) NOT NULL,
    created_at timestamp NOT NULL,
    updated_at timestamp NOT NULL
);
```

Indexes depend on workload:

- B-tree on tenant/document/filter columns.
- HNSW vector index for read-heavy retrieval.
- IVFFlat only when you understand training/build trade-offs.
- Unique index on document + chunk + hash if useful for idempotent reindexing.

## Retrieval Query Pattern

Always scope first:

```sql
SELECT id, document_id, title, section_path, content,
       embedding <=> :query_embedding AS distance
FROM knowledge_chunks
WHERE tenant_id = :tenant_id
  AND embedding_model = :embedding_model
ORDER BY embedding <=> :query_embedding
LIMIT 20;
```

Do not rely on prompt instructions for tenant isolation.

## Reindexing Strategy

Trigger reindex when:

- Source content changes.
- Chunking algorithm changes.
- Embedding model/version/dimensions changes.
- Trust metadata changes in a way that affects filtering/ranking.

Recommended approach:

1. Compute source hash.
2. If unchanged, skip.
3. Generate chunks with deterministic chunk IDs where possible.
4. Embed only new/changed chunks.
5. Mark missing chunks deleted or replace document chunks transactionally.
6. Keep model version metadata to avoid mixing vector spaces.

## Debugging Bad Search

Order of investigation:

1. Does the expected answer exist in the source text?
2. Was the source extracted correctly?
3. Was it chunked into a coherent chunk?
4. Was that chunk embedded with the same model as the query?
5. Does metadata filtering exclude it?
6. Does vector retrieval return it in top 50?
7. Does rerank promote or bury it?
8. Does prompt assembly include it?
9. Does generation cite it?

Do not start by changing databases.
