<?php

namespace App\Exports;

use App\Models\parking_log;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ParkingLogExport
{
    /**
     * @param  Collection<int, parking_log>  $logs
     */
    public function __construct(private readonly Collection $logs) {}

    /**
     * Stream the parking logs as a dependency-free XLSX workbook.
     */
    public function download(string $filename): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $path = tempnam(sys_get_temp_dir(), 'parkir-sip-export-');

            $zip = new ZipArchive;
            $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
            $zip->addFromString('_rels/.rels', $this->packageRelationshipsXml());
            $zip->addFromString('xl/workbook.xml', $this->workbookXml());
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
            $zip->addFromString('xl/styles.xml', $this->stylesXml());
            $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml());

            $zip->close();

            readfile($path);
            unlink($path);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Build the worksheet, including the header row and every parking log.
     */
    private function worksheetXml(): string
    {
        $headers = ['Slot', 'Lokasi', 'Plat', 'Pengguna', 'Kendaraan', 'Masuk', 'Keluar', 'Tarif', 'Status'];
        $widths = [12, 18, 20, 24, 12, 20, 20, 14, 12];

        $rows = $this->logs->map(fn (parking_log $log): array => [
            $log->nomer_slot,
            $log->lokasi ?? '-',
            $this->plateFor($log),
            $log->nama_pengguna,
            $log->jenisKendaraanLabel(),
            optional($log->waktu_masuk)->translatedFormat('d M Y, H:i') ?? '-',
            optional($log->waktu_keluar)->translatedFormat('d M Y, H:i') ?? '-',
            $log->tarifFormatted(),
            $log->waktu_keluar === null ? 'Sedang parkir' : 'Selesai',
        ]);

        $lastRow = max($rows->count() + 1, 1);
        $columnCount = count($headers);

        $columnXml = collect($widths)
            ->map(fn (int $width, int $index): string => sprintf(
                '<col min="%d" max="%d" width="%d" customWidth="1"/>',
                $index + 1,
                $index + 1,
                $width,
            ))
            ->implode('');

        $headerXml = $this->rowXml(1, $headers, 1);
        $bodyXml = $rows
            ->values()
            ->map(fn (array $row, int $index): string => $this->rowXml($index + 2, $row))
            ->implode('');

        $lastColumn = $this->columnLetter($columnCount);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<dimension ref="A1:'.$lastColumn.$lastRow.'"/>'
            .'<sheetViews><sheetView workbookViewId="0">'
            .'<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>'
            .'</sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="15"/>'
            .'<cols>'.$columnXml.'</cols>'
            .'<sheetData>'.$headerXml.$bodyXml.'</sheetData>'
            .'</worksheet>';
    }

    /**
     * Build a single worksheet row from cell values.
     *
     * @param  array<int, string|null>  $values
     */
    private function rowXml(int $rowNumber, array $values, int $style = 0): string
    {
        $cells = collect($values)
            ->values()
            ->map(function (?string $value, int $index) use ($rowNumber, $style): string {
                $reference = $this->columnLetter($index + 1).$rowNumber;
                $styleAttribute = $style > 0 ? ' s="'.$style.'"' : '';

                return '<c r="'.$reference.'"'.$styleAttribute.' t="inlineStr"><is><t xml:space="preserve">'
                    .$this->escape($value ?? '')
                    .'</t></is></c>';
            })
            ->implode('');

        return '<row r="'.$rowNumber.'">'.$cells.'</row>';
    }

    /**
     * The plate text shown for a parking log entry.
     */
    private function plateFor(parking_log $log): string
    {
        $plate = $log->plateText();

        if ($plate !== null) {
            return $plate;
        }

        return $log->plateImageUrl() !== null ? 'Tangkapan gambar' : '-';
    }

    /**
     * Convert a 1-based column index into its spreadsheet letter.
     */
    private function columnLetter(int $index): string
    {
        $letter = '';

        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)).$letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }

    /**
     * Escape a value for use inside XML text.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private function packageRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Riwayat parkir" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function workbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2">'
            .'<font><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="2">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'</fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'</cellXfs>'
            .'</styleSheet>';
    }
}
