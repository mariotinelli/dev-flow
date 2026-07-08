# Agentic RAG and Harness Design

## Harness Definition

The harness is everything around the model: context management, tools, permissions, loop control, memory, observability, retries, evals, and safety gates.

The model is not the product. The harness is the product boundary.

## Workflow vs Agent

Use deterministic workflow when:

- You can enumerate the steps.
- Business rules are fixed.
- Actions mutate state.
- Compliance/auditability matters.
- Failure handling needs deterministic rollback/retry.

Use an agent when:

- The path is dynamic.
- The agent must choose among tools based on user input.
- The task is research-like or multi-hop.
- The number of states is too large to map clearly.

Most production systems are workflow first with small agentic sections.

## Agentic RAG Loop

Typical loop:

1. Decide if retrieval is needed.
2. Generate a retrieval query or decompose the question.
3. Call retriever tool.
4. Grade returned documents for relevance/sufficiency.
5. If insufficient, rewrite query or ask clarification.
6. Stop after max iterations.
7. Generate grounded answer with citations.

## Stop Conditions

Always define:

- Max retrieval iterations.
- Max tool calls.
- Max total tokens.
- Max latency budget.
- Min relevance/trust threshold.
- Fallback behavior when evidence is weak.

Examples:

- After 3 retrieval attempts, answer with available evidence or ask a clarifying question.
- If top-k trust average is below 0.6, do not answer high-confidence; escalate retrieval strategy or human review.
- If the same document set repeats after rewrite, stop rewriting.

## Tool Design for Retrieval Agents

Good tools are API contracts for the model.

Tool naming:

- Use verb + object.
- Add namespace when multiple systems exist: `knowledge_search_docs`, `crm_search_customers`, `catalog_find_products`.
- Avoid generic `search`, `execute`, `helper`, `run`, `process`.

Tool descriptions must say:

- What the tool does.
- When to use it.
- When not to use it.
- What it returns.
- What to do if the input is ambiguous.

Schema rules:

- Use strict schemas.
- Make invalid states impossible with enums and min/max constraints.
- Use `additionalProperties: false` where supported.
- Do not expose `tenant_id`, `user_id`, `workspace_id` if app code already knows them.
- Prefer domain-specific tools over raw SQL or raw HTTP tools.

Error rules:

- Return structured errors with recovery hints.
- Do not return stack traces to the model.
- Example:

```json
{
  "error": "missing_search_query",
  "message": "No search query was provided.",
  "hint": "Ask the user for the product, policy, or document topic before calling this tool again."
}
```

## Retriever as a Tool

The retriever tool should return concise, citation-ready context:

```json
{
  "query": "refund annual plan cancellation",
  "results": [
    {
      "source_id": "DOC-42#3",
      "title": "Refund Policy",
      "section": "Annual plans",
      "content": "Customers may cancel annual plans within 7 days...",
      "score": 0.84,
      "trust_score": 0.93
    }
  ]
}
```

Avoid returning full documents unless the agent explicitly needs them and budget allows.

## MCP Boundary

Use MCP when the agent needs access to external systems as tools, especially across clients.

Production MCP requirements:

- Strict JSON Schema for every tool.
- OAuth/audience binding where applicable.
- Gateway/policy layer between agent and tools for enterprise use.
- Rate limits, audit logs, destructive action approvals.
- No pass-through of user tokens to unrelated upstreams.
- Treat third-party MCP servers as prompt-injection surfaces.

Use project/custom tools when domain-specific behavior, tighter schemas, or simpler deployment is better than MCP portability.

## Memory Design

Types:

- Working memory: current conversation context.
- Semantic memory: structured facts about user/account/preferences.
- Episodic memory: retrievable prior interactions.
- Procedural memory: workflows/instructions/tools.

Rules:

- Do not store everything in a vector store.
- Structured facts belong in database/key-value tables.
- Episodic transcripts can use retrieval.
- Memory writes should be explicit, auditable, and reversible.
- For sensitive facts, require opt-in and support deletion.

## Context Management

- Context window is finite working memory; do not fill it just because it fits.
- Long context still suffers from context rot/lost-in-the-middle.
- Prefer retrieving the right evidence over dumping documents.
- Summarize old chat turns; keep recent turns verbatim.
- Keep stable system prompt eligible for prompt caching when provider supports it.

## Observability for Agent Loops

Trace spans:

- User turn.
- LLM decision call.
- Tool call input/output/error.
- Retrieval query and result IDs.
- Rerank call.
- Judge/grader call.
- Final generation.

Metrics:

- Loop length.
- Tool call count.
- Tool error rate.
- Retry count.
- Cost per successful task.
- Latency per span.
- Retrieval abstention rate.
- User correction/escalation rate.

## Common Agentic Failures

- Agent searches when answer is generic.
- Agent does not search when answer requires facts.
- Agent rewrites query forever.
- Agent treats retrieved prompt injection as instruction.
- Agent calls wrong tool because tool names/descriptions overlap.
- Agent asks model to fill known IDs.
- Agent performs a state-changing action without external authorization.

Fix these in harness/tool design before switching models.
