<?php

declare(strict_types = 1);

namespace App\Actions\Project\Documentations;

use App\Models\ProjectDocumentation;
use Illuminate\Support\Str;

class ChunkProjectDocumentationText
{
    /**
     * @return list<array{section_path: string|null, content: string, embedded_content: string, content_hash: string}>
     */
    public function handle(ProjectDocumentation $projectDocumentation, string $text): array
    {
        $sections = $this->sections($text);
        $chunks   = [];

        foreach ($sections as $section) {
            foreach ($this->splitSection($section['content']) as $content) {
                $embeddedContent = $this->embeddedContent($projectDocumentation, $section['section_path'], $content);

                $chunks[] = [
                    'section_path'     => $section['section_path'],
                    'content'          => $content,
                    'embedded_content' => $embeddedContent,
                    'content_hash'     => hash('sha256', $embeddedContent),
                ];
            }
        }

        return $chunks;
    }

    /**
     * @return list<array{section_path: string|null, content: string}>
     */
    private function sections(string $text): array
    {
        $sections       = [];
        $currentSection = null;
        $currentContent = [];

        foreach (preg_split('/\n/', $text) ?: [] as $line) {
            if (preg_match('/^#{1,6}\s+(.+)$/', trim($line), $matches) === 1) {
                if ($currentContent !== []) {
                    $sections[] = [
                        'section_path' => $currentSection,
                        'content'      => trim(implode("\n", $currentContent)),
                    ];
                }

                $currentSection = trim($matches[1]);
                $currentContent = [$currentSection];

                continue;
            }

            $currentContent[] = $line;
        }

        if ($currentContent !== []) {
            $sections[] = [
                'section_path' => $currentSection,
                'content'      => trim(implode("\n", $currentContent)),
            ];
        }

        return array_values(array_filter($sections, fn (array $section): bool => $section['content'] !== ''));
    }

    /**
     * @return list<string>
     */
    private function splitSection(string $content): array
    {
        $chunkSize = (int) config('semantic-search.chunking.size', 1200);
        $overlap   = (int) config('semantic-search.chunking.overlap', 200);

        if (Str::length($content) <= $chunkSize) {
            return [$content];
        }

        $chunks = [];
        $offset = 0;

        while ($offset < Str::length($content)) {
            $chunk         = Str::substr($content, $offset, $chunkSize);
            $lastParagraph = mb_strrpos($chunk, "\n\n");

            if ($lastParagraph !== false && $offset + $chunkSize < Str::length($content)) {
                $chunk = Str::substr($chunk, 0, $lastParagraph);
            }

            $chunk = trim($chunk);

            if ($chunk !== '') {
                $chunks[] = $chunk;
            }

            $offset += max(Str::length($chunk) - $overlap, 1);
        }

        return $chunks;
    }

    private function embeddedContent(ProjectDocumentation $projectDocumentation, ?string $sectionPath, string $content): string
    {
        return implode("\n", array_filter([
            "Documento: {$projectDocumentation->title}",
            "Categoria: {$projectDocumentation->category->name}",
            $sectionPath ? "Seção: {$sectionPath}" : null,
            '',
            $content,
        ], fn (?string $line): bool => $line !== null));
    }
}
