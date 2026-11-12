<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class Controller
{
    /**
     * Helper terpusat untuk menghasilkan respon unduhan berkas CSV dengan header standar,
     * UTF-8 Byte Order Mark (BOM) untuk Microsoft Excel, dan pemisah titik koma (Prinsip DRY).
     *
     * @param string $filename Nama berkas CSV yang diunduh
     * @param callable $writer Callback yang menerima resource stream $handle
     * @return StreamedResponse
     */
    protected function streamCsv(string $filename, callable $writer): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($writer) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBFsep=;\r\n");

            $writer($handle);

            fclose($handle);
        }, 200, $headers);
    }
}
