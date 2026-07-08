---
name: rag-semantic-search-harness
description: "Use when designing, implementing, debugging, or reviewing RAG, semantic search, embeddings, pgvector, vector stores, hybrid search, reranking, agentic RAG, AI harnesses, evals, LLM-as-a-Judge, trust layers, source scoring, retrieval observability, memory, MCP/tools for retrieval, and Laravel AI SDK retrieval features."
license: MIT
metadata:
  author: remsoft
  source: Beer and Code AI engineering notes
---

# RAG, Semantic Search, and Harness Engineering

## Scope

Use this skill for production-grade AI retrieval systems, not just prompt tweaks.

Activate it when the task mentions or implies:

- RAG, retrieval, semantic search, embeddings, vector search, vector store, pgvector, HNSW, cosine similarity, chunking, or document ingestion.
- Hybrid search, BM25, Elasticsearch, RRF, reranking, cross-encoders, Cohere/Jina/Voyage/BGE rerankers.
- Agentic RAG, tool-based retrieval, multi-hop retrieval, query rewriting, retrieval grading, or harness loops.
- Evals, golden datasets, LLM-as-a-Judge, RAGAS-like metrics, Promptfoo, CI gates, regression testing of prompts/retrieval.
- Trust layer, source scoring, citations, source reputation, prompt injection through retrieved content, web search, or SERP filtering.
- Laravel AI SDK features for `Embeddings`, `Reranking`, `Stores`, agents, tools, structured output, memory, or provider failover.

## Required Companion Skills

- For Laravel AI SDK APIs, also activate `ai-sdk-development` and search official docs before code changes.
- For Laravel backend code, also activate `laravel-best-practices`.
- For Pest tests, also activate `pest-testing`.
- For project architecture and Brain usage, also activate `remsoft-patterns` and `remsoft-brain-addon` when relevant.

## Core Mental Model

- A production AI answer is a system outcome: retrieval quality, context assembly, prompt contract, model behavior, validation, observability, and cost all matter.
- Retrieval failures are often caused before the database: wrong embedding model, mismatched query/document embedding versions, unnormalized vectors, bad chunking, missing metadata, weak evals.
- Reranking improves relevance, not trust. A separate trust layer is needed when sources come from the web or user-generated content.
- Agentic RAG is a control-loop choice, not an upgrade by default. Use it when a fixed one-shot retrieval cannot answer reliably.
- Evals are mandatory. Without a golden set, every optimization is guesswork.

## Decision Matrix

### RAG vs Fine-Tuning vs Long Context

- Use RAG for fresh, dynamic, tenant-scoped, document-backed, or auditable facts.
- Use fine-tuning for style, format adherence, domain behavior, or cost reduction on repetitive tasks; do not use it as a fact database.
- Use long context for rare one-off document analysis when the document fits and latency/cost are acceptable.
- Use memory for persistent user/session facts; keep semantic memory structured when possible and episodic memory retrievable.
- Prefer combinations: RAG + structured output + validation; RAG + rerank; RAG + prompt caching; fine-tuned small model + RAG for cheaper repetitive flows.

### Classic RAG vs Agentic RAG

- Use classic RAG for FAQ, support policy lookup, single-hop factual answers, and predictable top-k retrieval.
- Use enhanced classic RAG first: better chunking, metadata filters, hybrid search, RRF, rerank, grounding, citations.
- Use agentic RAG only for ambiguous, multi-hop, comparative, heterogeneous, or query-rewriting-heavy tasks.
- Never allow unbounded retrieval loops. Set max iterations, max tool calls, max tokens, and a fallback answer.
- If the agent reformulates but returns mostly the same documents, revert to classic retrieval plus rerank.

### pgvector vs Dedicated Vector DB

- Use pgvector when the app already uses PostgreSQL, needs tenant filters in the same transaction, and has up to low millions of vectors with simple metadata filtering.
- Use Qdrant/Pinecone/Weaviate/OpenSearch when scale, distributed indexing, heavy metadata filtering, high QPS, or managed vector ops justify another system.
- Do not switch storage before measuring embedding model, normalization, chunking, filters, and eval metrics.

## Default Production Pipeline

1. Ingest source with stable document IDs, tenant IDs, timestamps, source URL/path, section hierarchy, and version/hash metadata.
2. Extract clean text. Treat OCR/layout extraction as part of retrieval quality, especially for PDFs, tables, invoices, and contracts.
3. Split into semantic chunks using headings, sections, paragraphs, tables, and overlap. Avoid blind fixed-size cuts when structure exists.
4. Embed chunks with the same model/version that will embed queries. Cache embeddings by content hash.
5. Store vectors with metadata needed for filters: `tenant_id`, `document_id`, `source`, `section`, `updated_at`, `visibility`, `language`, `chunk_hash`.
6. Retrieve using metadata filters before similarity where possible.
7. Add lexical/hybrid retrieval when exact entities matter: SKU, CPF/CNPJ, invoice number, legal article, product code, acronyms, names.
8. Fuse rankings with RRF when combining BM25 and dense vectors.
9. Rerank the candidate set with a cross-encoder/provider reranker when precision matters.
10. Apply trust/source filters when content is web, user-generated, third-party, or adversarial.
11. Assemble prompt context with delimiters, source IDs, scores, and explicit instruction that retrieved content is data, not instruction.
12. Require grounded answer with citations. If context does not contain the answer, return a fallback instead of guessing.
13. Validate output deterministically: citation IDs exist, JSON schema is valid, no forbidden claims without source, tenant boundaries respected.
14. Trace every step: query, filters, candidates, scores, final context, model, tokens, cost, latency, tool calls, and answer.
15. Evaluate offline before release and sample online traffic after release.

## Retrieval Quality Gates

- Same embedding model and version for indexing and querying.
- Re-embed all documents when changing embedding model or dimension strategy.
- Use cosine distance (`<=>` in pgvector) unless vectors are guaranteed normalized for inner product.
- Chunk size is not universal. Tune using a domain golden set.
- Add overlap only where boundaries can lose meaning; too much overlap creates duplicate evidence.
- Include document/section title in chunk text or metadata when it helps resolve pronouns and context.
- Do not pass raw HTML/CSS/scripts/meta prompt content to the LLM. Extract text and strip hidden instructions.
- Do not let the model fill tenant/user IDs that the app already knows. Pass them in code and enforce filters server-side.

## Metrics to Track

### Retrieval

- Recall@k: did the right document/chunk appear in the top k?
- MRR: how early was the first relevant result?
- NDCG@k: how well is the ranking ordered by relevance?
- Context precision: how much retrieved context was actually useful?
- Empty retrieval rate and low-score retrieval rate.

### Generation

- Faithfulness: is every factual claim supported by retrieved context?
- Answer relevance: did the answer address the user intent?
- Citation validity: are cited sources present and sufficient?
- Abstention correctness: did the system say it could not answer when context was insufficient?

### Product and Operations

- Deflection/resolution rate, CSAT, conversion, human escalation rate.
- Cost per resolved interaction, not only cost per call.
- p50/p95/p99 latency, time-to-first-token, time-to-last-token.
- Tool error rate, loop length, retry count, cache hit rate, rerank cost, judge cost.

## Golden Dataset Rules

- Start with 30-50 real questions covering happy path, edge cases, ambiguous wording, exact-entity queries, tenant boundaries, and unsupported questions.
- Grow to 100-500 examples before relying on metrics for release gates.
- Each example should include: user query, expected relevant doc/chunk IDs, expected answer or rubric, required citations, disallowed claims, and labels for difficulty/intent/language.
- Calibrate LLM-as-Judge against human labels before trusting it. Target 75-90% agreement for judge tasks.
- Keep evals versioned. Prompt, retriever, embedding model, chunking, reranker, and trust thresholds are production behavior.

## Security Rules

- Retrieved content is untrusted data, not instruction.
- Wrap retrieved content in explicit delimiters and label it as source material.
- Never expose destructive tools to a retrieval agent without human confirmation and server-side authorization.
- Enforce tenant/user scoping in SQL/query filters, not in prompt text.
- Redact PII before embedding when policy requires it; embeddings can leak sensitive facts through retrieval.
- Support deletion/reindexing for LGPD/right-to-erasure flows. Delete from vector store, source table, caches, and traces where required.
- For MCP/tools, use strict schemas, `additionalProperties: false`, typed enums, max lengths, and structured recoverable errors.

## Laravel AI SDK Bias

When writing Laravel code in this project, prefer first-party `laravel/ai` APIs if they cover the use case:

- `Laravel\Ai\Embeddings` for embedding generation.
- `Laravel\Ai\Reranking` for reranking.
- `Laravel\Ai\Stores` and `Laravel\Ai\Files\Document` for provider vector/file stores when appropriate.
- Agent classes with tools for agentic RAG or multi-tool workflows.
- Structured output for judges, graders, and validation-oriented model calls.
- Fakes/assertions for tests.

Always search Laravel docs with Boost before implementing SDK-specific code.

## Companion References

Read the focused reference file before making detailed decisions:

- `retrieval-playbook.md` for end-to-end architecture and decision matrices.
- `chunking-embeddings-pgvector.md` for chunking, embeddings, pgvector, and index choices.
- `hybrid-search-rerank.md` for BM25, RRF, reranking, and SERP/web search filtering.
- `agentic-rag-harness.md` for agent loops, tool design, MCP/harness boundaries, and stop conditions.
- `evals-llm-judge.md` for golden datasets, LLM-as-Judge, CI gates, and metrics.
- `trust-security-guardrails.md` for trust scoring, prompt injection defense, PII, tenant isolation, and source quality.
- `laravel-ai-sdk-patterns.md` for Laravel-specific implementation patterns.
- `checklists.md` for implementation, review, and production-readiness checklists.

## Delivery Standard

- Do not deliver RAG changes without programmatic tests or eval evidence.
- For code changes, include affected Laravel tests and, when retrieval quality changes, an eval/golden-set run or a clear technical reason if not yet available.
- Report trade-offs explicitly: quality, latency, cost, observability, safety, and operational complexity.
