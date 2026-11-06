<?php

namespace App\Support\Reports;

use Symfony\Component\HttpFoundation\StreamedResponse;

final class CsvExporter
{
    /**
     * @param  list<string>  $headers
     * @param  iterable<int, list<string|int|float|null>>  $rows
     */
    public static function download(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            fputcsv($out, $headers);

            foreach ($rows as $row) {
                fputcsv($out, array_map(
                    fn ($cell) => $cell === null ? '' : (string) $cell,
                    $row,
                ));
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
