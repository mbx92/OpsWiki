<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use ZipArchive;

class ImportFileReader
{
    /**
     * @param  list<UploadedFile>  $files
     * @return list<array{name: string, content: string, extension: string}>
     */
    public function entriesFromUploads(array $files): array
    {
        $entries = [];

        foreach ($files as $file) {
            $entries = array_merge($entries, $this->entriesFromFile($file));
        }

        usort($entries, fn (array $a, array $b) => strcmp($a['name'], $b['name']));

        return $entries;
    }

    /**
     * @return list<array{name: string, content: string, extension: string}>
     */
    private function entriesFromFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'zip') {
            return $this->entriesFromZip($file);
        }

        if (! in_array($extension, ['md', 'markdown'], true)) {
            return [];
        }

        return [[
            'name' => $file->getClientOriginalName(),
            'content' => $file->get(),
            'extension' => $extension,
        ]];
    }

    /**
     * @return list<array{name: string, content: string, extension: string}>
     */
    private function entriesFromZip(UploadedFile $file): array
    {
        $entries = [];
        $zip = new ZipArchive;

        if ($zip->open($file->getRealPath()) !== true) {
            return $entries;
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $name = $stat['name'];

            if (str_ends_with($name, '/') || str_starts_with(basename($name), '.')) {
                continue;
            }

            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if (! in_array($extension, ['md', 'markdown'], true)) {
                continue;
            }

            $content = $zip->getFromIndex($i);

            if ($content === false) {
                continue;
            }

            $entries[] = [
                'name' => $name,
                'content' => $content,
                'extension' => $extension,
            ];
        }

        $zip->close();

        return $entries;
    }
}
