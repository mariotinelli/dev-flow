# Checklists

## New RAG Feature Checklist

- [ ] Use case requires retrieval, not only prompt/fine-tune/long-context.
- [ ] Corpus source, ownership, freshness, and deletion needs are understood.
- [ ] Tenant/visibility model is defined before indexing.
- [ ] Extraction quality is validated on real documents.
- [ ] Chunking strategy is chosen and documented.
- [ ] Embedding model is selected with at least small domain eval.
- [ ] Query and document embeddings use same model/version/dimensions.
- [ ] Embeddings are cached by content hash.
- [ ] Vector storage includes metadata for filters and audit.
- [ ] Retrieval query enforces tenant/security filters in code/SQL.
- [ ] Hybrid search is considered for exact entities.
- [ ] Rerank is considered when top-k ordering is weak.
- [ ] Trust layer is considered for web/user/third-party content.
- [ ] Prompt requires citations and abstention.
- [ ] Output validation checks citations and schema.
- [ ] Observability traces retrieval, rerank, prompt, model, tokens, cost.
- [ ] Golden dataset exists before production release.

## Semantic Search Debug Checklist

- [ ] Expected answer exists in source.
- [ ] Text extraction/OCR did not lose it.
- [ ] Chunk contains enough context.
- [ ] Chunk is not too broad or too tiny.
- [ ] Same embedding model/version used for query and document.
- [ ] Vector dimensions match model.
- [ ] Similarity operator matches vector normalization.
- [ ] Tenant/filter conditions do not exclude the expected chunk.
- [ ] Expected chunk appears in top 50 before rerank.
- [ ] Reranker promotes or at least preserves it.
- [ ] Prompt includes it in final context.
- [ ] Answer agent is allowed/required to cite it.

## Agentic RAG Readiness Checklist

- [ ] Classic/enhanced RAG baseline is measured.
- [ ] Weakness is multi-hop/ambiguous/query rewriting, not bad chunking.
- [ ] Retriever tool has clear name/description/schema.
- [ ] Tool does not expose known tenant/user IDs as model arguments.
- [ ] Max iterations/tool calls/tokens/latency are configured.
- [ ] Relevance grading has fallback.
- [ ] Repeated result sets stop the loop.
- [ ] Tool errors are structured and recoverable.
- [ ] Loop is traced span-by-span.
- [ ] Eval compares classic vs agentic quality, latency, and cost.

## Eval Checklist

- [ ] 30-50 initial examples from real usage.
- [ ] Expected source IDs are labeled.
- [ ] Unsupported queries are included.
- [ ] Tenant boundary examples are included.
- [ ] Prompt injection examples are included if retrieval content can be untrusted.
- [ ] Metrics are separated by intent/difficulty/language/source type.
- [ ] Judge prompts use binary/ternary rubrics.
- [ ] Judge is calibrated against human labels.
- [ ] CI gate checks regressions, not arbitrary perfection.
- [ ] Production samples feed back into dataset.

## Security Checklist

- [ ] Retrieved content is labeled as untrusted data.
- [ ] Raw HTML/CSS/scripts/comments are stripped before context.
- [ ] Prompt injection examples are tested.
- [ ] PII policy for embeddings is defined.
- [ ] Right-to-erasure path deletes vectors/caches/traces as required.
- [ ] Tools use least privilege.
- [ ] Destructive tools require authorization and confirmation outside model judgment.
- [ ] Tool schemas reject extra fields and invalid values.
- [ ] Cross-tenant retrieval tests pass.
- [ ] Logs/traces mask secrets and sensitive data.

## Production Readiness Checklist

- [ ] Baseline retrieval and generation metrics recorded.
- [ ] p95 latency measured with retrieval + rerank + generation.
- [ ] Cost per successful answer measured.
- [ ] Provider/model versions pinned where possible.
- [ ] Rollback path exists for prompt/retriever/chunking changes.
- [ ] Reindex jobs are idempotent and retryable.
- [ ] Monitoring alerts for tool errors, high loop count, high cost, low retrieval confidence.
- [ ] Human escalation path exists for low-confidence/high-risk answers.
- [ ] Documentation of architecture/trade-offs exists where project process requires it.

## Code Review Checklist for RAG Changes

- [ ] Does the diff change behavior beyond the requested retrieval feature?
- [ ] Are tenant and authorization constraints enforced outside prompts?
- [ ] Are model/provider APIs verified against docs?
- [ ] Are tests using fakes where external calls are not the subject?
- [ ] Are eval fixtures updated when behavior intentionally changes?
- [ ] Is there any raw user/retrieved content flowing into logs or prompts unsafely?
- [ ] Are costs and latency bounded?
- [ ] Are failure modes explicit and user-safe?
