<?php
// Create fpdf directory if it doesn't exist
if (!file_exists('fpdf')) {
    mkdir('fpdf', 0777, true);
}

// FPDF core files content
$fpdf_content = '<?php
define("FPDF_VERSION","1.84");

class FPDF {
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
    protected $wPt, $hPt;         // dimensions of current page in points
    protected $w, $h;             // dimensions of current page in user unit
    protected $lMargin;           // left margin
    protected $tMargin;           // top margin
    protected $rMargin;           // right margin
    protected $bMargin;           // page break margin
    protected $cMargin;           // cell margin
    protected $x, $y;             // current position in user unit
    protected $lasth;             // height of last printed cell
    protected $LineWidth;         // line width in user unit
    protected $fontpath;          // path containing fonts
    protected $CoreFonts;         // array of core font names
    protected $fonts;             // array of used fonts
    protected $FontFiles;         // array of font files
    protected $encodings;         // array of encodings
    protected $cmaps;             // array of ToUnicode CMaps
    protected $FontFamily;        // current font family
    protected $FontStyle;         // current font style
    protected $underline;         // underlining flag
    protected $CurrentFont;       // current font info
    protected $FontSizePt;        // current font size in points
    protected $FontSize;          // current font size in user unit
    protected $DrawColor;         // commands for drawing color
    protected $FillColor;         // commands for filling color
    protected $TextColor;         // commands for text color
    protected $ColorFlag;         // indicates whether fill and text colors are different
    protected $WithAlpha;         // indicates whether alpha channel is used
    protected $ws;                // word spacing
    protected $images;            // array of used images
    protected $PageLinks;         // array of links in pages
    protected $links;             // array of internal links
    protected $AutoPageBreak;     // automatic page breaking
    protected $PageBreakTrigger;  // threshold used to trigger page breaks
    protected $InHeader;          // flag set when processing header
    protected $InFooter;          // flag set when processing footer
    protected $AliasNbPages;      // alias for total number of pages
    protected $ZoomMode;          // zoom display mode
    protected $LayoutMode;        // layout display mode
    protected $title;             // title
    protected $subject;           // subject
    protected $author;            // author
    protected $keywords;          // keywords
    protected $creator;           // creator
    protected $PDFVersion;        // PDF version number

    function __construct($orientation="P", $unit="mm", $size="A4") {
        // Initialize PDF
        $this->page = 0;
        $this->n = 2;
        $this->buffer = "";
        $this->pages = array();
        $this->state = 0;
        $this->compress = false;
        $this->k = 1;
        $this->DefOrientation = $orientation;
        $this->CurOrientation = $orientation;
        $this->StdPageSizes = array("a3"=>array(841.89,1190.55), "a4"=>array(595.28,841.89), "a5"=>array(420.94,595.28));
        $this->DefPageSize = $size;
        $this->CurPageSize = $size;
        $this->CurRotation = 0;
        $this->PageInfo = array();
        $this->wPt = $this->StdPageSizes[$size][0];
        $this->hPt = $this->StdPageSizes[$size][1];
        $this->w = $this->wPt/$this->k;
        $this->h = $this->hPt/$this->k;
        $this->lMargin = 10;
        $this->tMargin = 10;
        $this->rMargin = 10;
        $this->bMargin = 10;
        $this->cMargin = 0;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->lasth = 0;
        $this->LineWidth = 0.567;
        $this->fontpath = "font/";
        $this->CoreFonts = array("courier", "helvetica", "times", "symbol", "zapfdingbats");
        $this->fonts = array();
        $this->FontFiles = array();
        $this->encodings = array();
        $this->cmaps = array();
        $this->FontFamily = "";
        $this->FontStyle = "";
        $this->underline = false;
        $this->CurrentFont = null;
        $this->FontSizePt = 12;
        $this->FontSize = $this->FontSizePt/$this->k;
        $this->DrawColor = "0 G";
        $this->FillColor = "0 g";
        $this->TextColor = "0 g";
        $this->ColorFlag = false;
        $this->WithAlpha = false;
        $this->ws = 0;
        $this->images = array();
        $this->PageLinks = array();
        $this->links = array();
        $this->AutoPageBreak = true;
        $this->PageBreakTrigger = $this->h-$this->bMargin;
        $this->InHeader = false;
        $this->InFooter = false;
        $this->AliasNbPages = "{nb}";
        $this->ZoomMode = "fullpage";
        $this->LayoutMode = "single";
        $this->title = "";
        $this->subject = "";
        $this->author = "";
        $this->keywords = "";
        $this->creator = "FPDF";
        $this->PDFVersion = "1.3";
    }

    function AddPage($orientation="") {
        // Add a new page
        if($this->state==0)
            $this->Open();
        $family = $this->FontFamily;
        $style = $this->FontStyle;
        $size = $this->FontSizePt;
        $this->AddPage($orientation);
        $this->SetFont($family,$style,$size);
        $this->page++;
    }

    function Output($dest="", $name="", $isUTF8=false) {
        // Output PDF to some destination
        if($this->state<3)
            $this->Close();
        if(strlen($name)==1 && strlen($dest)!=1) {
            // Swap parameters
            $tmp = $dest;
            $dest = $name;
            $name = $tmp;
        }
        if($dest=="")
            $dest = "I";
        if($name=="")
            $name = "doc.pdf";
        switch(strtoupper($dest)) {
            case "I":
                // Send to standard output
                $this->_checkoutput();
                header("Content-Type: application/pdf");
                header("Content-Disposition: inline; filename=".$name);
                header("Cache-Control: private, max-age=0, must-revalidate");
                header("Pragma: public");
                echo $this->buffer;
                break;
            case "D":
                // Download file
                $this->_checkoutput();
                header("Content-Type: application/pdf");
                header("Content-Disposition: attachment; filename=".$name);
                header("Cache-Control: private, max-age=0, must-revalidate");
                header("Pragma: public");
                echo $this->buffer;
                break;
            case "F":
                // Save to local file
                $f = fopen($name,"wb");
                if(!$f)
                    $this->Error("Unable to create output file: ".$name);
                fwrite($f,$this->buffer,strlen($this->buffer));
                fclose($f);
                break;
            case "S":
                // Return as a string
                return $this->buffer;
            default:
                $this->Error("Incorrect output destination: ".$dest);
        }
        return "";
    }
}';

// Write FPDF class to file
file_put_contents('fpdf/fpdf.php', $fpdf_content);

// Create a simple test file
$test_content = '<?php
require("fpdf/fpdf.php");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont("Arial","B",16);
$pdf->Cell(40,10,"Hello World!");
$pdf->Output();
?>';

file_put_contents('test_pdf.php', $test_content);

echo "FPDF has been installed successfully! You can test it by visiting test_pdf.php";
?> 