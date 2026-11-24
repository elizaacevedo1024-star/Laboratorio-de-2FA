<?php
// Genera el código QR LOCALMENTE (TCPDF) en SVG. El secreto no sale del servidor.
require_once __DIR__ . '/../TCPDF-main/tcpdf_barcodes_2d.php';

function qrSvg(string $texto): string {
    $qr = new TCPDF2DBarcode($texto, 'QRCODE,M');
    return $qr->getBarcodeSVGcode(5, 5, 'black');
}
