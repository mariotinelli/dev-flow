<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });

const props = withDefaults(
    defineProps<{
        id?: string;
        name?: string;
        defaultValue?: string | null;
        rows?: number;
        class?: HTMLAttributes['class'];
    }>(),
    {
        rows: 8,
        defaultValue: '',
    },
);

const model = defineModel<string>({ default: '' });
const textarea = ref<HTMLTextAreaElement | null>(null);
const activeTab = ref<'edit' | 'preview'>('edit');

if (!model.value && props.defaultValue) {
    model.value = props.defaultValue;
}

watch(
    () => props.defaultValue,
    (value) => {
        if (!model.value && value) {
            model.value = value;
        }
    },
);

const preview = computed(() => renderPreview(model.value));

function escapeHtml(value: string): string {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderInline(value: string): string {
    return escapeHtml(value)
        .replace(/`([^`]+)`/g, '<code>$1</code>')
        .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
        .replace(/__([^_]+)__/g, '<strong>$1</strong>')
        .replace(/\*([^*]+)\*/g, '<em>$1</em>')
        .replace(/_([^_]+)_/g, '<em>$1</em>')
        .replace(/~~([^~]+)~~/g, '<del>$1</del>')
        .replace(/&lt;u&gt;(.+?)&lt;\/u&gt;/g, '<u>$1</u>')
        .replace(/\[([^\]]+)]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noreferrer">$1</a>');
}

function renderPreview(value: string): string {
    if (!value.trim()) {
        return '<p class="text-muted-foreground">Nada para pré-visualizar.</p>';
    }

    return value
        .split('\n')
        .map((line) => {
            const trimmedLine = line.trim();

            if (!trimmedLine) {
                return '<br>';
            }

            if (/^(#{1,3})\s+/.test(trimmedLine)) {
                const level = trimmedLine.match(/^(#{1,3})\s+/)?.[1].length ?? 1;

                return `<h${level}>${renderInline(trimmedLine.replace(/^#{1,3}\s+/, ''))}</h${level}>`;
            }

            if (/^h[1-3]\.\s+/i.test(trimmedLine)) {
                const level = trimmedLine.charAt(1);

                return `<h${level}>${renderInline(trimmedLine.replace(/^h[1-3]\.\s+/i, ''))}</h${level}>`;
            }

            if (/^[-*]\s+/.test(trimmedLine)) {
                return `<p>&bull; ${renderInline(trimmedLine.replace(/^[-*]\s+/, ''))}</p>`;
            }

            if (/^\d+\.\s+/.test(trimmedLine)) {
                return `<p>${renderInline(trimmedLine)}</p>`;
            }

            if (/^>\s+/.test(trimmedLine)) {
                return `<blockquote>${renderInline(trimmedLine.replace(/^>\s+/, ''))}</blockquote>`;
            }

            return `<p>${renderInline(trimmedLine)}</p>`;
        })
        .join('');
}

async function focusTextarea(): Promise<void> {
    activeTab.value = 'edit';
    await nextTick();
    textarea.value?.focus();
}

async function insertMarkup(before: string, after = '', placeholder = ''): Promise<void> {
    await focusTextarea();

    const input = textarea.value;

    if (!input) {
        return;
    }

    const start = input.selectionStart;
    const end = input.selectionEnd;
    const selectedText = model.value.slice(start, end) || placeholder;

    model.value = `${model.value.slice(0, start)}${before}${selectedText}${after}${model.value.slice(end)}`;

    await nextTick();
    input.setSelectionRange(start + before.length, start + before.length + selectedText.length);
}

async function prefixLine(prefix: string): Promise<void> {
    await focusTextarea();

    const input = textarea.value;

    const cursor = input?.selectionStart ?? model.value.length;
    const lineStart = model.value.lastIndexOf('\n', cursor - 1) + 1;

    model.value = `${model.value.slice(0, lineStart)}${prefix}${model.value.slice(lineStart)}`;

    await nextTick();
    textarea.value?.setSelectionRange(cursor + prefix.length, cursor + prefix.length);
}

function insertTable(): Promise<void> {
    return insertMarkup('\n| Cabeçalho | Cabeçalho |\n| --- | --- |\n| Valor | Valor |\n');
}

function insertCodeBlock(): Promise<void> {
    return insertMarkup('\n```\n', '\n```\n', 'codigo');
}
</script>

<template>
    <div :class="cn('w-full', props.class)">
        <input v-if="name" type="hidden" :name="name" :value="model" />

        <div class="overflow-hidden rounded-md border border-input bg-background shadow-xs">
            <div
                class="flex min-h-9 flex-wrap items-center border-b border-input bg-muted/35 text-xs text-muted-foreground"
            >
                <button
                    type="button"
                    class="border-r border-input px-3 py-2 text-foreground hover:bg-background"
                    :class="activeTab === 'edit' && 'bg-background'"
                    @click="activeTab = 'edit'"
                >
                    Editar
                </button>
                <button
                    type="button"
                    class="border-r border-input px-3 py-2 hover:bg-background hover:text-foreground"
                    :class="activeTab === 'preview' && 'bg-background text-foreground'"
                    @click="activeTab = 'preview'"
                >
                    Pré-visualizar
                </button>

                <div class="flex flex-wrap items-center gap-0.5 px-2">
                    <button
                        type="button"
                        class="rich-editor-button font-bold"
                        title="Negrito"
                        @click="insertMarkup('**', '**', 'texto')"
                    >
                        B
                    </button>
                    <button
                        type="button"
                        class="rich-editor-button italic"
                        title="Itálico"
                        @click="insertMarkup('*', '*', 'texto')"
                    >
                        I
                    </button>
                    <button
                        type="button"
                        class="rich-editor-button underline"
                        title="Sublinhado"
                        @click="insertMarkup('<u>', '</u>', 'texto')"
                    >
                        U
                    </button>
                    <button
                        type="button"
                        class="rich-editor-button line-through"
                        title="Tachado"
                        @click="insertMarkup('~~', '~~', 'texto')"
                    >
                        S
                    </button>
                    <button
                        type="button"
                        class="rich-editor-button"
                        title="Código inline"
                        @click="insertMarkup('`', '`', 'codigo')"
                    >
                        C
                    </button>
                    <button
                        type="button"
                        class="rich-editor-button font-semibold"
                        title="Título 1"
                        @click="prefixLine('# ')"
                    >
                        H1
                    </button>
                    <button
                        type="button"
                        class="rich-editor-button font-semibold"
                        title="Título 2"
                        @click="prefixLine('## ')"
                    >
                        H2
                    </button>
                    <button
                        type="button"
                        class="rich-editor-button font-semibold"
                        title="Título 3"
                        @click="prefixLine('### ')"
                    >
                        H3
                    </button>
                    <button type="button" class="rich-editor-button" title="Lista" @click="prefixLine('- ')">•</button>
                    <button type="button" class="rich-editor-button" title="Lista numerada" @click="prefixLine('1. ')">
                        1.
                    </button>
                    <button type="button" class="rich-editor-button" title="Citação" @click="prefixLine('> ')">
                        &gt;
                    </button>
                    <button type="button" class="rich-editor-button" title="Recuar" @click="prefixLine('    ')">
                        →
                    </button>
                    <button type="button" class="rich-editor-button" title="Tabela" @click="insertTable">▦</button>
                    <button
                        type="button"
                        class="rich-editor-button font-mono"
                        title="Bloco de código"
                        @click="insertCodeBlock"
                    >
                        pre
                    </button>
                    <button
                        type="button"
                        class="rich-editor-button font-mono"
                        title="Link"
                        @click="insertMarkup('[', '](https://)', 'texto')"
                    >
                        /
                    </button>
                    <button
                        type="button"
                        class="rich-editor-button"
                        title="Imagem"
                        @click="insertMarkup('![', '](https://)', 'alt')"
                    >
                        ▧
                    </button>
                </div>

                <span class="ml-auto px-3 text-muted-foreground" title="Ajuda">?</span>
            </div>

            <textarea
                v-show="activeTab === 'edit'"
                :id="id"
                ref="textarea"
                v-model="model"
                :rows="rows"
                class="block min-h-46 w-full resize-y border-0 bg-background px-3 py-2 text-sm outline-none placeholder:text-muted-foreground focus-visible:ring-0 disabled:cursor-not-allowed disabled:opacity-50"
                v-bind="$attrs"
            />

            <div
                v-show="activeTab === 'preview'"
                class="rich-editor-preview min-h-46 w-full bg-background px-3 py-2 text-sm"
                v-html="preview"
            />
        </div>
    </div>
</template>

<style scoped>
.rich-editor-button {
    display: inline-flex;
    min-height: 1.75rem;
    min-width: 1.75rem;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    border-radius: 0.125rem;
    padding: 0 0.375rem;
    color: var(--foreground);
}

.rich-editor-button:hover {
    background: var(--background);
}

.rich-editor-preview :deep(p) {
    margin-bottom: 0.5rem;
}

.rich-editor-preview :deep(h1) {
    margin-bottom: 0.75rem;
    font-size: 1.5rem;
    font-weight: 700;
}

.rich-editor-preview :deep(h2) {
    margin-bottom: 0.625rem;
    font-size: 1.25rem;
    font-weight: 700;
}

.rich-editor-preview :deep(h3) {
    margin-bottom: 0.5rem;
    font-size: 1rem;
    font-weight: 700;
}

.rich-editor-preview :deep(blockquote) {
    margin-bottom: 0.5rem;
    border-left: 3px solid var(--border);
    padding-left: 0.75rem;
    color: var(--muted-foreground);
}

.rich-editor-preview :deep(code) {
    border-radius: 0.25rem;
    background: var(--muted);
    padding: 0.125rem 0.25rem;
    font-family: var(--font-mono);
    font-size: 0.875em;
}

.rich-editor-preview :deep(a) {
    color: var(--primary);
    text-decoration: underline;
    text-underline-offset: 2px;
}
</style>
