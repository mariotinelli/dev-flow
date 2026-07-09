<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Bot, Send, User } from '@lucide/vue';
import { nextTick, onMounted, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { index, stream } from '@/routes/intelligence/ai-chat';
import type { Auth } from '@/types';

type Source = {
    id: number;
    chunk_id: number;
    title: string;
    source_type: string;
    source_id: number;
    position: number;
    score: number;
    metadata: Record<string, unknown> | null;
};

type ChatMessage = {
    id: number | string;
    role: 'user' | 'assistant';
    content: string;
    sources: Source[];
    isComplete?: boolean;
};

type AgentStreamEvent = {
    type?: string;
    delta?: string;
    result?: string | { sources?: Source[] };
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Projeto',
                href: index(),
            },
            {
                title: 'Chat IA',
                href: index(),
            },
        ],
    },
});

const props = defineProps<{
    messages: ChatMessage[];
}>();

const page = usePage<{ auth: Auth }>();
const question = ref('');
const messages = ref<ChatMessage[]>(props.messages);
const isStreaming = ref(false);
const errorMessage = ref('');
const messageList = ref<HTMLElement | null>(null);
const isMessageListReady = ref(messages.value.length === 0);

function csrfToken(): string {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}

function escapeHtml(value: string): string {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function renderInlineMarkdown(value: string): string {
    return escapeHtml(value)
        .replace(/`([^`]+?)`/g, '<code class="rounded bg-background px-1 py-0.5 text-[0.85em]">$1</code>')
        .replace(/\*\*([\s\S]+?)\*\*/g, '<strong>$1</strong>')
        .replace(/__([\s\S]+?)__/g, '<strong>$1</strong>')
        .replace(/(?<!\*)\*([^*\n]+?)\*(?!\*)/g, '<em>$1</em>')
        .replace(/(?<!_)_([^_\n]+?)_(?!_)/g, '<em>$1</em>');
}

function renderMarkdown(value: string): string {
    const lines = value.replaceAll('\r\n', '\n').split('\n');
    const html: string[] = [];
    let paragraph: string[] = [];
    let list: 'ul' | 'ol' | null = null;

    function closeParagraph(): void {
        if (paragraph.length === 0) {
            return;
        }

        html.push(`<p>${paragraph.map(renderInlineMarkdown).join('<br>')}</p>`);
        paragraph = [];
    }

    function closeList(): void {
        if (!list) {
            return;
        }

        html.push(`</${list}>`);
        list = null;
    }

    for (const line of lines) {
        const trimmed = line.trim();

        if (trimmed === '') {
            closeParagraph();
            closeList();
            continue;
        }

        const heading = trimmed.match(/^(#{1,3})\s+(.+)$/);

        if (heading) {
            closeParagraph();
            closeList();

            const level = heading[1].length + 2;
            html.push(`<h${level}>${renderInlineMarkdown(heading[2])}</h${level}>`);
            continue;
        }

        const unordered = trimmed.match(/^[-*]\s+(.+)$/);

        if (unordered) {
            closeParagraph();

            if (list !== 'ul') {
                closeList();
                html.push('<ul>');
                list = 'ul';
            }

            html.push(`<li>${renderInlineMarkdown(unordered[1])}</li>`);
            continue;
        }

        const ordered = trimmed.match(/^\d+\.\s+(.+)$/);

        if (ordered) {
            closeParagraph();

            if (list !== 'ol') {
                closeList();
                html.push('<ol>');
                list = 'ol';
            }

            html.push(`<li>${renderInlineMarkdown(ordered[1])}</li>`);
            continue;
        }

        closeList();
        paragraph.push(line);
    }

    closeParagraph();
    closeList();

    return html.join('');
}

async function scrollToBottom(): Promise<void> {
    await nextTick();
    messageList.value?.scrollTo({ top: messageList.value.scrollHeight, behavior: 'smooth' });
}

async function jumpToBottom(): Promise<void> {
    await nextTick();

    if (messageList.value) {
        messageList.value.scrollTop = messageList.value.scrollHeight;
    }

    isMessageListReady.value = true;
}

onMounted(() => {
    void jumpToBottom();
});

async function submit(): Promise<void> {
    const prompt = question.value.trim();

    if (!prompt || isStreaming.value) {
        return;
    }

    errorMessage.value = '';
    question.value = '';

    messages.value.push({ id: Date.now(), role: 'user', content: prompt, sources: [], isComplete: true });

    const assistantMessageId = Date.now() + 1;

    const assistantMessage: ChatMessage = {
        id: assistantMessageId,
        role: 'assistant',
        content: '',
        sources: [],
        isComplete: false,
    };

    messages.value.push(assistantMessage);
    isStreaming.value = true;
    await scrollToBottom();

    try {
        const response = await fetch(stream().url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'text/event-stream',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ question: prompt }),
        });

        if (!response.ok || !response.body) {
            throw new Error('stream_failed');
        }

        await readStream(response, assistantMessageId);
    } catch {
        errorMessage.value = 'Não foi possível responder agora. Tente novamente em instantes.';
        updateAssistantMessage(assistantMessageId, (message) => {
            message.content ||= errorMessage.value;
        });
    } finally {
        isStreaming.value = false;
        await scrollToBottom();
    }
}

async function readStream(response: Response, assistantMessageId: number): Promise<void> {
    const reader = response.body?.getReader();

    if (!reader) {
        return;
    }

    const decoder = new TextDecoder();
    let buffer = '';

    while (true) {
        const { done, value } = await reader.read();

        if (done) {
            break;
        }

        buffer += decoder.decode(value, { stream: true });
        buffer = buffer.replaceAll('\r\n', '\n');

        const events = buffer.split('\n\n');
        buffer = events.pop() ?? '';

        for (const rawEvent of events) {
            handleStreamEvent(rawEvent, assistantMessageId);
        }

        await scrollToBottom();
    }

    if (buffer.trim() !== '') {
        handleStreamEvent(buffer, assistantMessageId);
    }

    updateAssistantMessage(assistantMessageId, (message) => {
        message.isComplete = true;
    });
}

function handleStreamEvent(rawEvent: string, assistantMessageId: number): void {
    const lines = rawEvent.replaceAll('\r\n', '\n').split('\n');
    const data = lines
        .filter((line) => line.startsWith('data: '))
        .map((line) => line.replace('data: ', ''))
        .join('\n');

    if (!data || data === '[DONE]') {
        if (data === '[DONE]') {
            updateAssistantMessage(assistantMessageId, (message) => {
                message.isComplete = true;
            });
        }

        return;
    }

    const payload = JSON.parse(data) as AgentStreamEvent;

    if (payload.type === 'text_delta' && payload.delta) {
        updateAssistantMessage(assistantMessageId, (message) => {
            message.content += payload.delta;
        });
    }

    if (payload.type === 'tool_result') {
        const result = typeof payload.result === 'string' ? JSON.parse(payload.result) as { sources?: Source[] } : payload.result;

        if (result?.sources) {
            updateAssistantMessage(assistantMessageId, (message) => {
                message.sources = result.sources ?? [];
            });
        }
    }
}

function updateAssistantMessage(id: number, callback: (message: ChatMessage) => void): void {
    const index = messages.value.findIndex((message) => message.id === id);

    if (index === -1) {
        return;
    }

    const message = { ...messages.value[index] };
    callback(message);
    messages.value[index] = message;
}
</script>

<template>
    <Head title="Chat IA do Projeto" />

    <div class="flex h-[calc(100svh-8rem)] max-h-[calc(100svh-8rem)] min-h-0 flex-1 flex-col gap-6 overflow-hidden">
        <div class="flex shrink-0 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <Heading
                variant="small"
                title="Chat IA do Projeto"
                :description="`Faça perguntas técnicas usando apenas a base de conhecimento de ${page.props.auth.current_project?.name ?? 'projeto selecionado'}.`"
            />
        </div>

        <div class="grid min-h-0 flex-1 overflow-hidden gap-4 lg:grid-cols-[minmax(0,1fr)_18rem]">
            <section class="relative flex h-full min-h-0 max-h-full flex-col overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-xs dark:border-sidebar-border">
                <div
                    ref="messageList"
                    class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain p-4 scrollbar-thin scrollbar-thumb-sidebar-border/70 scrollbar-track-transparent hover:scrollbar-thumb-sidebar-border [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-sidebar-border/70 hover:[&::-webkit-scrollbar-thumb]:bg-sidebar-border [&::-webkit-scrollbar-track]:bg-transparent"
                    :class="isMessageListReady ? 'opacity-100' : 'opacity-0'"
                >
                    <div v-if="messages.length === 0" class="flex h-full min-h-80 flex-col items-center justify-center text-center text-muted-foreground">
                        <Bot class="mb-4 size-10" />
                        <p class="max-w-md text-sm">
                            Pergunte sobre decisões, documentação, arquitetura ou processos já registrados na base de conhecimento do projeto.
                        </p>
                    </div>

                    <article
                        v-for="message in messages"
                        :key="message.id"
                        class="flex gap-3"
                        :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
                    >
                        <div v-if="message.role === 'assistant'" class="mt-1 flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <Bot class="size-4" />
                        </div>

                        <div class="max-w-3xl rounded-2xl px-4 py-3 text-sm whitespace-pre-wrap" :class="message.role === 'user' ? 'bg-primary text-primary-foreground' : 'bg-muted'">
                            <div
                                v-if="message.content"
                                class="space-y-2 [&_code]:font-mono [&_em]:italic [&_h3]:text-base [&_h3]:font-semibold [&_h4]:text-sm [&_h4]:font-semibold [&_h5]:text-sm [&_h5]:font-medium [&_li]:ml-4 [&_ol]:list-decimal [&_strong]:font-semibold [&_ul]:list-disc"
                                v-html="renderMarkdown(message.content)"
                            ></div>
                            <p v-else class="animate-pulse text-muted-foreground">Consultando a base de conhecimento...</p>

                            <div v-if="message.sources.length > 0 && message.isComplete !== false" class="mt-4 space-y-2 border-t pt-3">
                                <p class="text-xs font-medium text-muted-foreground">Fontes utilizadas</p>
                                <div class="flex flex-wrap gap-2">
                                    <Badge v-for="source in message.sources" :key="`${source.id}-${source.chunk_id}`" variant="secondary">
                                        {{ source.title }}
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        <div v-if="message.role === 'user'" class="mt-1 flex size-8 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground">
                            <User class="size-4" />
                        </div>
                    </article>
                </div>

                <div
                    v-if="!isMessageListReady"
                    class="absolute inset-x-0 top-0 bottom-[6.75rem] space-y-4 overflow-hidden p-4"
                >
                    <div class="flex justify-end">
                        <div class="w-full max-w-xl space-y-2 rounded-2xl bg-muted p-4">
                            <div class="h-3 w-11/12 animate-pulse rounded-full bg-muted-foreground/15"></div>
                            <div class="h-3 w-8/12 animate-pulse rounded-full bg-muted-foreground/15"></div>
                        </div>
                    </div>

                    <div class="flex justify-start gap-3">
                        <div class="size-8 shrink-0 animate-pulse rounded-full bg-muted"></div>
                        <div class="w-full max-w-3xl space-y-2 rounded-2xl bg-muted p-4">
                            <div class="h-3 w-full animate-pulse rounded-full bg-muted-foreground/15"></div>
                            <div class="h-3 w-10/12 animate-pulse rounded-full bg-muted-foreground/15"></div>
                            <div class="h-3 w-7/12 animate-pulse rounded-full bg-muted-foreground/15"></div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <div class="w-full max-w-sm space-y-2 rounded-2xl bg-muted p-4">
                            <div class="h-3 w-9/12 animate-pulse rounded-full bg-muted-foreground/15"></div>
                            <div class="h-3 w-5/12 animate-pulse rounded-full bg-muted-foreground/15"></div>
                        </div>
                    </div>
                </div>

                <form class="shrink-0 border-t p-4" @submit.prevent="submit">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Textarea
                            v-model="question"
                            rows="3"
                            class="resize-none"
                            placeholder="Ex.: Como o projeto organiza deploy e rollback?"
                            :disabled="isStreaming"
                        />
                        <Button type="submit" class="sm:self-end" :disabled="isStreaming || question.trim() === ''">
                            <Send class="size-4" />
                            {{ isStreaming ? 'Respondendo...' : 'Enviar' }}
                        </Button>
                    </div>
                    <p v-if="errorMessage" class="mt-2 text-sm text-destructive">{{ errorMessage }}</p>
                </form>
            </section>

            <aside class="hidden overflow-y-auto rounded-xl border border-sidebar-border/70 bg-card p-4 text-sm text-muted-foreground shadow-xs dark:border-sidebar-border lg:block">
                <h2 class="mb-2 font-medium text-foreground">Escopo da resposta</h2>
                <p>O agente responde somente com chunks recuperados da base de conhecimento do projeto atual.</p>
                <p class="mt-3">Se faltar contexto, ele deve informar a limitação em vez de completar com conhecimento externo.</p>
            </aside>
        </div>
    </div>
</template>
