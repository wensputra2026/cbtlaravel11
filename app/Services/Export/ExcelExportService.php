<?php

namespace App\Services\Export;

use Symfony\Component\HttpFoundation\Response;

class ExcelExportService
{
    /**
     * Generate real Microsoft Excel document (.xls / SpreadsheetML format) with styling.
     *
     * @param string $sheetTitle Judul lembar kerja
     * @param array $headers Array judul kolom ['No', 'NISN', 'Nama Siswa', ...]
     * @param array $rows Array baris data [[1, '00123', 'Budi', ...], ...]
     * @param string $filename Nama file output (e.g. Rekap_Nilai.xls)
     * @return Response
     */
    public static function download(string $sheetTitle, array $headers, array $rows, string $filename): Response
    {
        if (!str_ends_with(strtolower($filename), '.xls')) {
            $filename = preg_replace('/\.[^.]+$/', '', $filename) . '.xls';
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<?mso-application progid=\"Excel.Sheet\"?>\n";
        $xml .= "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"\n";
        $xml .= " xmlns:o=\"urn:schemas-microsoft-com:office:office\"\n";
        $xml .= " xmlns:x=\"urn:schemas-microsoft-com:office:excel\"\n";
        $xml .= " xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"\n";
        $xml .= " xmlns:html=\"http://www.w3.org/TR/REC-html40\">\n";
        
        // Document Properties
        $xml .= " <DocumentProperties xmlns=\"urn:schemas-microsoft-com:office:office\">\n";
        $xml .= "  <Author>SMANBEN-CBT Engine</Author>\n";
        $xml .= "  <Created>" . date('Y-m-d\TH:i:s\Z') . "</Created>\n";
        $xml .= " </DocumentProperties>\n";

        // Styles
        $xml .= " <Styles>\n";
        $xml .= "  <Style ss:ID=\"Default\" ss:Name=\"Normal\">\n";
        $xml .= "   <Alignment ss:Vertical=\"Center\"/>\n";
        $xml .= "   <Borders/>\n";
        $xml .= "   <Font ss:FontName=\"Segoe UI\" ss:Size=\"10\" ss:Color=\"#1E293B\"/>\n";
        $xml .= "  </Style>\n";
        
        // Header Style
        $xml .= "  <Style ss:ID=\"Header\">\n";
        $xml .= "   <Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Center\" ss:WrapText=\"1\"/>\n";
        $xml .= "   <Borders>\n";
        $xml .= "    <Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#CBD5E1\"/>\n";
        $xml .= "    <Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#CBD5E1\"/>\n";
        $xml .= "    <Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#CBD5E1\"/>\n";
        $xml .= "    <Border ss:Position=\"Top\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#CBD5E1\"/>\n";
        $xml .= "   </Borders>\n";
        $xml .= "   <Font ss:FontName=\"Segoe UI\" ss:Size=\"10\" ss:Color=\"#FFFFFF\" ss:Bold=\"1\"/>\n";
        $xml .= "   <Interior ss:Color=\"#4F46E5\" ss:Pattern=\"Solid\"/>\n";
        $xml .= "  </Style>\n";

        // Cell Styles
        $xml .= "  <Style ss:ID=\"CellText\">\n";
        $xml .= "   <Alignment ss:Vertical=\"Center\"/>\n";
        $xml .= "   <Borders>\n";
        $xml .= "    <Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Top\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "   </Borders>\n";
        $xml .= "  </Style>\n";

        $xml .= "  <Style ss:ID=\"CellCenter\">\n";
        $xml .= "   <Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Center\"/>\n";
        $xml .= "   <Borders>\n";
        $xml .= "    <Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Top\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "   </Borders>\n";
        $xml .= "  </Style>\n";

        $xml .= "  <Style ss:ID=\"CellNumber\">\n";
        $xml .= "   <Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Center\"/>\n";
        $xml .= "   <Borders>\n";
        $xml .= "    <Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Top\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "   </Borders>\n";
        $xml .= "   <NumberFormat ss:Format=\"#,##0.00\"/>\n";
        $xml .= "  </Style>\n";

        $xml .= "  <Style ss:ID=\"CellInteger\">\n";
        $xml .= "   <Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Center\"/>\n";
        $xml .= "   <Borders>\n";
        $xml .= "    <Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Top\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "   </Borders>\n";
        $xml .= "   <NumberFormat ss:Format=\"#,##0\"/>\n";
        $xml .= "  </Style>\n";

        $xml .= " </Styles>\n";

        // Worksheet
        $safeSheetTitle = htmlspecialchars(mb_substr($sheetTitle, 0, 31), ENT_QUOTES, 'UTF-8');
        $xml .= " <Worksheet ss:Name=\"{$safeSheetTitle}\">\n";
        $xml .= "  <Table ss:DefaultRowHeight=\"20\">\n";

        // Column widths
        foreach ($headers as $colIdx => $header) {
            $width = max(80, min(300, (strlen($header) + 4) * 8));
            $xml .= "   <Column ss:Index=\"" . ($colIdx + 1) . "\" ss:Width=\"{$width}\"/>\n";
        }

        // Header Row
        $xml .= "   <Row ss:Height=\"26\">\n";
        foreach ($headers as $header) {
            $safeHeader = htmlspecialchars($header, ENT_QUOTES, 'UTF-8');
            $xml .= "    <Cell ss:StyleID=\"Header\"><Data ss:Type=\"String\">{$safeHeader}</Data></Cell>\n";
        }
        $xml .= "   </Row>\n";

        // Data Rows
        foreach ($rows as $row) {
            $xml .= "   <Row>\n";
            foreach ($row as $cell) {
                if (is_int($cell)) {
                    $xml .= "    <Cell ss:StyleID=\"CellInteger\"><Data ss:Type=\"Number\">{$cell}</Data></Cell>\n";
                } elseif (is_float($cell) || (is_numeric($cell) && str_contains((string)$cell, '.'))) {
                    $xml .= "    <Cell ss:StyleID=\"CellNumber\"><Data ss:Type=\"Number\">{$cell}</Data></Cell>\n";
                } elseif ($cell === null || $cell === '') {
                    $xml .= "    <Cell ss:StyleID=\"CellCenter\"><Data ss:Type=\"String\">-</Data></Cell>\n";
                } else {
                    $valStr = (string)$cell;
                    $safeStr = htmlspecialchars($valStr, ENT_QUOTES, 'UTF-8');
                    $align = (in_array(strtolower($valStr), ['aktif', 'selesai', 'belum mulai', 'sedang mengerjakan', '-']) || strlen($valStr) <= 4)
                        ? 'CellCenter'
                        : 'CellText';
                    $xml .= "    <Cell ss:StyleID=\"{$align}\"><Data ss:Type=\"String\">{$safeStr}</Data></Cell>\n";
                }
            }
            $xml .= "   </Row>\n";
        }

        $xml .= "  </Table>\n";
        $xml .= " </Worksheet>\n";
        $xml .= "</Workbook>\n";

        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }
}
