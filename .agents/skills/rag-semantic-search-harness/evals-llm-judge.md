# Evals and LLM-as-a-Judge

## Principle

No eval, no optimization. Without a golden set, RAG quality is opinion.

Evaluate three layers separately:

1. Retrieval: did we find the right evidence?
2. Generation: did the answer faithfully use the evidence?
3. Product: did the user/business outcome improve?

## Golden Dataset Shape

Each example should include:

```json
{
  "id": "refund-annual-001",
  "query": "Posso cancelar meu plano anual e receber reembolso?",
  "intent": "policy_refund",
  "language": "pt-BR",
  "tenant_id": "demo",
  "expected_sources": ["refund-policy#annual"],
  "required_claims": [
    "Annual plans can be cancelled within the configured refund window"
  ],
  "forbidden_claims": [
    "Refund is always guaranteed after the window"
  ],
  "expected_behavior": "answer_with_citation",
  "difficulty": "medium"
}
```

Add adversarial examples:

- Unsupported questions.
- Conflicting sources.
- Low-trust web sources.
- Tenant boundary attempts.
- Prompt injection hidden in retrieved content.
- Exact entity IDs.
- Multi-hop queries.

## Retrieval Metrics

- Recall@k: expected source appears in top k.
- MRR: reciprocal rank of first expected source.
- NDCG@k: ranking quality with graded relevance.
- Context precision: percentage of retrieved chunks that help answer.
- Empty result correctness: unsupported queries should return no answer/fallback.

Recommended starting gates:

- Recall@5 >= 0.80 for early prototype.
- Recall@10 >= 0.90 for mature curated FAQ/docs.
- MRR should trend up after rerank/chunking changes.
- Track by intent, not only global average.

## Generation Metrics

- Faithfulness: factual claims supported by context.
- Answer relevance: addresses user query.
- Citation validity: every factual claim cites real source IDs.
- Abstention: says “not found” when context is insufficient.
- Format/schema adherence.
- Tone/safety when user-facing.

## LLM-as-a-Judge Rules

- Use clear rubrics, not “score this from 1 to 10”.
- Prefer binary or ternary labels: pass/fail/indeterminate or bad/acceptable/good.
- Use one judge per dimension when possible.
- Give the judge a way out: `indeterminate` when evidence is insufficient.
- Do not ask the judge to do deterministic math or validation that code can do.
- Calibrate judge output against human labels before relying on it.
- Use a different model/family/size than the generator when possible to reduce self-enhancement bias.

## Judge Prompt Pattern

```txt
You are evaluating whether an AI answer is faithful to the provided sources.

Task:
Return PASS only if every factual claim in the answer is directly supported by the sources.
Return FAIL if the answer adds unsupported facts, contradicts sources, or cites a source that does not support the claim.
Return INDETERMINATE if the sources or answer are too ambiguous to judge.

Do not reward longer answers.
Do not use outside knowledge.
Judge only this dimension: faithfulness.

Return JSON with:
- verdict: PASS | FAIL | INDETERMINATE
- reason: one concise sentence
- unsupported_claims: array of strings
```

## Biases and Mitigations

Positional bias:

- Randomize A/B order when comparing outputs.
- Re-run swapped order and discard unstable comparisons.

Verbosity bias:

- Explicitly state that length does not matter.
- Judge one dimension at a time.

Self-enhancement bias:

- Use a judge model different from the generator.
- Prefer smaller/cheaper judge for binary checks and stronger judge for nuanced checks.

Limited reasoning:

- Move calculations, URL validation, JSON schema validation, citation ID checks, and thresholds to code.

## CI Gates

Use CI for regression, not perfection.

Gate examples:

- Retrieval Recall@5 cannot drop more than 3 percentage points.
- Faithfulness pass rate cannot drop below baseline.
- No high-risk unsupported-answer examples may regress.
- Prompt/schema output must pass deterministic validation.
- Tenant boundary tests must always pass.

For small datasets, avoid overreacting to one global score. Inspect failures by category.

## Production Sampling

Sample online traffic for evals:

- 1-10% depending on volume and cost.
- Always sample errors, low-confidence retrieval, escalations, thumbs down, and high-cost loops.
- Feed reviewed samples back into the golden dataset.

Store:

- Query.
- Retrieved source IDs.
- Answer.
- User feedback.
- Judge verdict.
- Human review label when available.
- Model/prompt/retriever versions.

## Human Review

Human labels are still needed for:

- Judge calibration.
- New domains.
- High-risk domains.
- Ambiguous disagreements.
- Adversarial/trust threshold tuning.

Review workflow:

1. Run judge.
2. Inspect judge/human disagreements.
3. Decide whether the human label, rubric, or judge prompt is wrong.
4. Update rubric/examples.
5. Re-run dataset.

## Metrics Dashboard

Track over time:

- Retrieval metrics by intent/source/language.
- Faithfulness pass rate.
- Unsupported answer rate.
- Citation missing/invalid rate.
- Abstention rate.
- Cost per successful answer.
- p95 latency.
- Human escalation rate.
- User thumbs down/correction rate.

## Anti-Patterns

- One generic judge returning a 0-10 score for everything.
- No human-calibrated labels.
- Only evaluating final answer while retrieval silently worsens.
- Evaluating only happy path examples.
- Changing chunking/model/prompt without dataset versioning.
- Using BLEU/ROUGE for open-ended grounded answers as primary quality metrics.
