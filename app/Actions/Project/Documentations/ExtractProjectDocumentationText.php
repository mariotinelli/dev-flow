<?php

declare(strict_types = 1);

namespace App\Actions\Project\Documentations;

use App\Models\ProjectDocumentation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use ZipArchive;

class ExtractProjectDocumentationText
{
    /**
     * @return array{0: string|null, 1: string|null}
     */
    public function handle(ProjectDocumentation $projectDocumentation): array
    {
        if (!$projectDocumentation->file_path) {
            return [null, null];
        }

        $contents   = Storage::disk('s3')->get($projectDocumentation->file_path);
        $sourceHash = hash('sha256', $contents);
        $extension  = Str::of($projectDocumentation->file_original_name ?: $projectDocumentation->file_path)->afterLast('.')->lower()->toString();

        $text = match ($extension) {
            'txt', 'md', 'markdown', 'json', 'xml', 'csv', 'html', 'htm' => $contents,
            'pdf'                                                        => $this->extractPdf($contents),
            'docx', 'xlsx', 'pptx'                                       => $this->extractOfficeOpenXml($contents),
            default                                                      => null,
        };

        $text = $this->normalizeText($text);

        return [$text === '' ? null : $text, $sourceHash];
    }

    private function extractPdf(string $contents): ?string
    {
        $inputPath  = tempnam(sys_get_temp_dir(), 'project-documentation-pdf-');
        $outputPath = tempnam(sys_get_temp_dir(), 'project-documentation-text-');

        if ($inputPath === false || $outputPath === false) {
            return null;
        }

        file_put_contents($inputPath, $contents);

        try {
            $process = new Process(['pdftotext', '-layout', $inputPath, $outputPath]);
            $process->setTimeout(60);
            $process->run();

            if (!$process->isSuccessful()) {
                return null;
            }

            return file_get_contents($outputPath) ?: null;
        } finally {
            @unlink($inputPath);
            @unlink($outputPath);
        }
    }

    private function extractOfficeOpenXml(string $contents): ?string
    {
        $path = tempnam(sys_get_temp_dir(), 'project-documentation-office-');

        if ($path === false) {
            return null;
        }

        file_put_contents($path, $contents);

        $zip    = new ZipArchive();
        $opened = false;

        try {
            if ($zip->open($path) !== true) {
                return null;
            }

            $opened = true;

            $parts = [];

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $name = $zip->getNameIndex($index);

                if (!$name || !Str::endsWith($name, '.xml')) {
                    continue;
                }

                if (!Str::startsWith($name, ['word/', 'xl/worksheets/', 'xl/sharedStrings.xml', 'ppt/slides/'])) {
                    continue;
                }

                $xml = $zip->getFromIndex($index);

                if ($xml === false) {
                    continue;
                }

                $parts[] = $this->textFromXml($xml);
            }

            return implode("\n", array_filter($parts));
        } finally {
            if ($opened) {
                $zip->close();
            }

            @unlink($path);
        }
    }

    private function textFromXml(string $xml): string
    {
        $xml = preg_replace('/<[^>]+>/', ' ', $xml) ?? '';

        return html_entity_decode($xml, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function normalizeText(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        $text = str_replace("\r\n", "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }
}
