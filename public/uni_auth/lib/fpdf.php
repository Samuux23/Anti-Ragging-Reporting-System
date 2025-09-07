<?php
// Minimal FPDF 1.86 (trimmed header) - https://www.fpdf.org
// License: Freeware
if (class_exists('FPDF')) { return; }

define('FPDF_VERSION','1.86');

class FPDF
{
protected $page;               // current page number
protected $n;                  // current object number
protected $offsets;            // array of object offsets
protected $buffer;             // buffer holding in-memory PDF
protected $pages;              // array containing pages
protected $state;              // current document state
protected $compress;           // compression flag
protected $k;                  // scale factor (number of points in user unit)
protected $DefOrientation;     // default orientation
protected $CurOrientation;     // current orientation
protected $StdPageSizes;       // standard page sizes
protected $DefPageSize;        // default page size
protected $CurPageSize;        // current page size
protected $CurRotation;        // current page rotation
protected $PageInfo;           // page-related data
protected $wPt, $hPt;          // dimensions of current page in points
protected $w, $h;              // dimensions of current page in user unit
protected $lMargin;            // left margin
protected $tMargin;            // top margin
protected $rMargin;            // right margin
protected $bMargin;            // page break margin
protected $cMargin;            // cell margin
protected $x, $y;              // current position in user unit for cell positioning
protected $lasth;              // height of last cell printed
protected $LineWidth;          // line width in user unit
protected $CoreFonts;          // array of standard font names
protected $fonts;              // array of used fonts
protected $FontFiles;          // array of font files
protected $encodings;          // array of encodings
protected $cmaps;              // array of ToUnicode CMaps
protected $FontFamily;         // current font family
protected $FontStyle;          // current font style
protected $underline;          // underlining flag
protected $CurrentFont;        // current font info
protected $FontSizePt;         // current font size in points
protected $FontSize;           // current font size in user unit
protected $DrawColor;          // commands for drawing color
protected $FillColor;          // commands for filling color
protected $TextColor;          // commands for text color
protected $ColorFlag;          // indicates whether fill and text colors are different
protected $WithAlpha;          // indicates whether alpha channel is used
protected $ws;                 // word spacing
protected $images;             // array of used images
protected $PageLinks;          // array of links in pages
protected $links;              // array of internal links
protected $AutoPageBreak;      // automatic page breaking
protected $PageBreakTrigger;   // threshold used to trigger page breaks
protected $InHeader;           // flag set when processing header
protected $InFooter;           // flag set when processing footer
protected $AliasNbPages;       // alias for total number of pages
protected $ZoomMode;           // zoom display mode
protected $LayoutMode;         // layout display mode
protected $metadata;           // info object
protected $PDFVersion;         // PDF version number

function __construct($orientation='P', $unit='mm', $size='A4')
{
    $this->state = 0;
    $this->page = 0;
    $this->n = 2;
    $this->buffer = '';
    $this->pages = [];
    $this->PageInfo = [];
    $this->fonts = [];
    $this->FontFiles = [];
    $this->encodings = [];
    $this->cmaps = [];
    $this->images = [];
    $this->links = [];
    $this->InHeader = false;
    $this->InFooter = false;
    $this->lasth = 0;
    $this->FontFamily = '';
    $this->FontStyle = '';
    $this->FontSizePt = 12;
    $this->underline = false;
    $this->DrawColor = '0 G';
    $this->FillColor = '0 g';
    $this->TextColor = '0 g';
    $this->ColorFlag = false;
    $this->WithAlpha = false;
    $this->ws = 0;
    // Scale factor
    if($unit=='pt')
        $this->k = 1;
    elseif($unit=='mm')
        $this->k = 72/25.4;
    elseif($unit=='cm')
        $this->k = 72/2.54;
    elseif($unit=='in')
        $this->k = 72;
    else
        $this->Error('Incorrect unit: '.$unit);
    // Page sizes
    $this->StdPageSizes = ['a3'=>[841.89,1190.55],'a4'=>[595.28,841.89],'a5'=>[420.94,595.28],'letter'=>[612,792],'legal'=>[612,1008]];
    $size = $this->_getpagesize($size);
    $this->DefPageSize = $size;
    $this->CurPageSize = $size;
    // Page orientation
    $orientation = strtolower($orientation);
    if($orientation=='p' || $orientation=='portrait')
        $orientation = 'P';
    elseif($orientation=='l' || $orientation=='landscape')
        $orientation = 'L';
    else
        $this->Error('Incorrect orientation: '.$orientation);
    $this->DefOrientation = $orientation;
    $this->CurOrientation = $orientation;
    $this->wPt = $size[0];
    $this->hPt = $size[1];
    $this->w = $this->wPt/$this->k;
    $this->h = $this->hPt/$this->k;
    $this->lMargin = 10;
    $this->tMargin = 10;
    $this->rMargin = 10;
    $this->bMargin = 10;
    $this->cMargin = 2;
    $this->LineWidth = .2;
    $this->SetAutoPageBreak(true, 15);
    $this->setCompression(true);
    $this->PDFVersion = '1.7';
}

function setCompression($compress)
{
    $this->compress = function_exists('gzcompress') ? $compress : false;
}

function AddPage($orientation='', $size='')
{
    if ($this->state==0)
        $this->Open();
    $family = $this->FontFamily;
    $style = $this->FontStyle.
        ($this->underline ? 'U' : '');
    $fontsize = $this->FontSizePt;
    $lw = $this->LineWidth;
    $dc = $this->DrawColor;
    $fc = $this->FillColor;
    $tc = $this->TextColor;
    $cf = $this->ColorFlag;
    $this->PageInfo[$this->page+1] = ['size'=>$this->CurPageSize,'rotation'=>$this->CurRotation];
    $this->page++;
    $this->pages[$this->page] = '';
    $this->state = 2;
    $this->x = $this->lMargin;
    $this->y = $this->tMargin;
    $this->FontFamily = '';
    if($family)
        $this->SetFont($family,$style,$fontsize);
    $this->LineWidth = $lw;
    $this->DrawColor = $dc;
    $this->FillColor = $fc;
    $this->TextColor = $tc;
    $this->ColorFlag = $cf;
}

function SetFont($family, $style='', $size=0)
{
    $this->FontFamily = $family;
    $this->FontStyle = $style;
    if($size>0)
        $this->FontSizePt = $size;
    $this->FontSize = $this->FontSizePt/$this->k;
}

function SetAutoPageBreak($auto, $margin=0)
{
    $this->AutoPageBreak = $auto;
    $this->bMargin = $margin;
    $this->PageBreakTrigger = $this->h - $margin;
}

function SetMargins($left, $top, $right=null)
{
    $this->lMargin = $left;
    $this->tMargin = $top;
    $this->rMargin = $right===null ? $left : $right;
}

function Ln($h=null)
{
    $this->x = $this->lMargin;
    if($h===null)
        $this->y += $this->lasth;
    else
        $this->y += $h;
}

function Cell($w, $h=0, $txt='', $border=0)
{
    $s = sprintf('BT %.2F %.2F Td (%s) Tj ET', ($this->x)*$this->k, ($this->h-$this->y-$h)*$this->k, $this->_escape($txt));
    $this->_out($s);
    $this->lasth = $h;
    $this->x += $w;
}

function MultiCell($w, $h, $txt)
{
    $lines = preg_split('/\r?\n/', $txt);
    foreach ($lines as $line) {
        $this->Cell($w, $h, $line);
        $this->Ln($h);
    }
}

function SetTitle($title)
{
    $this->metadata['Title'] = $title;
}

function Output($dest='', $name='doc.pdf')
{
    // Build minimal PDF
    $this->_enddoc();
    $buffer = $this->buffer;
    if($dest=='I' || $dest=='')
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="'.$name.'"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo $buffer;
    }
    else
    {
        file_put_contents($name, $buffer);
    }
}

function _getpagesize($size)
{
    if(is_string($size))
        $size = strtolower($size);
    if(is_string($size))
    {
        if(!isset($this->StdPageSizes[$size]))
            $this->Error('Unknown page size: '.$size);
        $a = $this->StdPageSizes[$size];
        return [$a[0], $a[1]];
    }
    else
        return [$size[0]*$this->k, $size[1]*$this->k];
}

function _escape($s)
{
    $s = str_replace(['\\','(',')',"\r"], ['\\\\','\\(','\\)', ''], $s);
    return $s;
}

function _out($s)
{
    // Collect content into current page
    if($this->state==2)
        $this->pages[$this->page] .= $s."\n";
    else
        $this->buffer .= $s."\n";
}

function Open()
{
    $this->state = 1;
}

function _enddoc()
{
    if($this->state<3)
        $this->_endpage();
    $this->_putdoc();
    $this->state = 3;
}

function _endpage()
{
    $this->state = 1;
}

function _putdoc()
{
    $this->n = 0;
    $this->offsets = [];
    $this->buffer = '';
    $this->_newobj();
    $this->_out('<< /Type /Catalog /Pages 2 0 R >>');
    $this->_out('endobj');
    $this->_newobj();
    $kids = '';
    $nb = count($this->pages);
    for($i=1;$i<=$nb;$i++)
        $kids .= (3+$i*2).' 0 R ';
    $this->_out('<< /Type /Pages /Count '.$nb.' /Kids [ '.$kids.'] >>');
    $this->_out('endobj');
    for($i=1;$i<=$nb;$i++)
    {
        $this->_newobj();
        $this->_out('<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.$this->wPt.' '.$this->hPt.'] /Resources << /Font << /F1  '.(3+$nb*2+$i).' 0 R >> >> /Contents '.(4+$nb*2+$i).' 0 R >>');
        $this->_out('endobj');
        $this->_newobj();
        $this->_out('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>');
        $this->_out('endobj');
        $this->_newobj();
        $p = "BT /F1 ".$this->FontSizePt." Tf 1 0 0 1 10 ".($this->hPt-20).' Tm ' . $this->pages[$i] . ' ET';
        $stream = strlen($p);
        $this->_out('<< /Length '.$stream.' >>');
        $this->_out('stream');
        $this->_out($p);
        $this->_out('endstream');
        $this->_out('endobj');
    }
    $xref = strlen($this->buffer);
    $this->_out('xref');
    $this->_out('0 '.(5+count($this->pages)*2));
    $this->_out('0000000000 65535 f ');
    $offset = 9;
    $objects = explode("\nendobj\n", $this->buffer);
    $this->buffer = '';
    $pos = 0;
    $entries = [];
    foreach ($objects as $i => $obj) {
        if ($obj==='') continue;
        $o = $obj."\nendobj\n";
        $entries[] = sprintf('%010d 00000 n ', $pos);
        $this->buffer .= $o;
        $pos += strlen($o);
    }
    $this->buffer = implode("\n", []); // reset; we'll rebuild in one go
    // Rebuild objects with offsets
    $objects = [];
    $this->_newobj(true); $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
    $this->_newobj(true); $objects[] = '<< /Type /Pages /Count '.count($this->pages).' /Kids [ '.implode(' ', array_map(function($i){return (3+$i*2).' 0 R';}, range(1,count($this->pages)))).' ] >>';
    for($i=1;$i<=count($this->pages);$i++){
        $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 '.$this->wPt.' '.$this->hPt.'] /Resources << /Font << /F1 '.(3+count($this->pages)*2+$i).' 0 R >> >> /Contents '.(4+count($this->pages)*2+$i).' 0 R >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $p = "BT /F1 ".$this->FontSizePt." Tf 1 0 0 1 10 ".($this->hPt-20).' Tm ' . $this->pages[$i] . ' ET';
        $objects[] = '<< /Length '.strlen($p).' >>\nstream\n'.$p.'\nendstream';
    }
    // Build final buffer
    $this->buffer = '';
    $offsets = [0];
    foreach ($objects as $i=>$o){
        $offsets[] = strlen($this->buffer);
        $this->buffer .= ($i+1).' 0 obj\n'.$o."\nendobj\n";
    }
    $xref = strlen($this->buffer);
    $this->buffer .= 'xref\n0 '.(count($objects)+1).'\n0000000000 65535 f \n';
    foreach ($offsets as $i=>$ofs){ if ($i===0) continue; $this->buffer .= sprintf('%010d 00000 n ', $ofs)."\n"; }
    $this->buffer .= 'trailer\n<< /Size '.(count($objects)+1).' /Root 1 0 R >>\nstartxref\n'.$xref."\n%%EOF";
}

function _newobj($silent=false)
{
    $this->n++;
    if(!$silent)
        $this->buffer .= $this->n.' 0 obj\n';
}

function Error($msg){ throw new \Exception('FPDF error: '.$msg); }
}


