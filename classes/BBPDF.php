<?php
define('FPDF_FONTPATH','font/');

# function hex2dec
# returns an associative array (keys: R,G,B) from a hex html code (e.g. # 3FE5AA)
function hex2dec($couleur = "# 000000") {
    $R = substr($couleur, 1, 2);
    $rouge = hexdec($R);
    $V = substr($couleur, 3, 2);
    $vert = hexdec($V);
    $B = substr($couleur, 5, 2);
    $bleu = hexdec($B);
    $tbl_couleur = array();
    $tbl_couleur['R']=$rouge;
    $tbl_couleur['G']=$vert;
    $tbl_couleur['B']=$bleu;
    return $tbl_couleur;
}

# conversion
# pixel -> millimeter in 72 dpi
function px2mm($px) {
    return $px*25.4/72;
}
function txtentities($html) {
    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans = array_flip($trans);
    return strtr($html, $trans);
}
class BBPDF extends FPDI {
    var $B;
    var $I;
    var $U;
    var $HREF;
    var $fontList;
    var $issetfont;
    var $issetcolor;

	function bbpdf($orientation='P',$unit='mm',$format='A4') {
        $this->FPDF($orientation,$unit,$format);
        $this->B=0;
        $this->I=0;
        $this->U=0;
        $this->HREF='';
        $this->PRE=false;
        $this->SetFont('Times','',12);
        $this->fontlist=array("arial","Times","Courier");
        $this->issetfont=false;
        $this->issetcolor=false;
        $this->articletitle='';
        $this->articleurl='';
        $this->debug='';
        $this->AliasNbPages();
	}

	function AddPage($orientation='', $size='') {
		parent::AddPage($orientation,$size);
		$this->setSourceFile('templates/'.$_SESSION['pdftemplate'].'.pdf');
		$template = $this->ImportPage(1);
		$this->useTemplate($template);
	}

	function Bookmark($txt,$level=0,$y=0) {
	    if($y==-1)
	        $y=$this->GetY();
	    $this->outlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->PageNo());
	}

	function _putbookmarks() {
	    $nb=count($this->outlines);
	    if($nb==0)
	        return;
	    $lru=array();
	    $level=0;
	    foreach($this->outlines as $i=>$o)
	    {
	        if($o['l']>0)
	        {
	            $parent=$lru[$o['l']-1];
	            # Set parent and last pointers
	            $this->outlines[$i]['parent']=$parent;
	            $this->outlines[$parent]['last']=$i;
	            if($o['l']>$level)
	            {
	                # Level increasing: set first pointer
	                $this->outlines[$parent]['first']=$i;
	            }
	        }
	        else
	            $this->outlines[$i]['parent']=$nb;
	        if($o['l']<=$level and $i>0)
	        {
	            # Set prev and next pointers
	            $prev=$lru[$o['l']];
	            $this->outlines[$prev]['next']=$i;
	            $this->outlines[$i]['prev']=$prev;
	        }
	        $lru[$o['l']]=$i;
	        $level=$o['l'];
	    }
	    # Outline items
	    $n=$this->n+1;
	    foreach($this->outlines as $i=>$o) {
	        $this->_newobj();
	        $this->_out('<</Title '.$this->_textstring($o['t']));
	        $this->_out('/Parent '.($n+$o['parent']).' 0 R');
	        if(isset($o['prev']))
	            $this->_out('/Prev '.($n+$o['prev']).' 0 R');
	        if(isset($o['next']))
	            $this->_out('/Next '.($n+$o['next']).' 0 R');
	        if(isset($o['first']))
	            $this->_out('/First '.($n+$o['first']).' 0 R');
	        if(isset($o['last']))
	            $this->_out('/Last '.($n+$o['last']).' 0 R');
	        $this->_out(sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]',1+2*$o['p'],($this->h-$o['y'])*$this->k));
	        $this->_out('/Count 0>>');
	        $this->_out('endobj');
	    }
	    # Outline root
	    $this->_newobj();
	    $this->OutlineRoot=$this->n;
	    $this->_out('<</Type /Outlines /First '.$n.' 0 R');
	    $this->_out('/Last '.($n+$lru[0]).' 0 R>>');
	    $this->_out('endobj');
	}
	function _putresources() {
	    parent::_putresources();
	    $this->_putbookmarks();
	}

	function _putcatalog() {
	    parent::_putcatalog();
	    if(count($this->outlines)>0)
	    {
	        $this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
	        $this->_out('/PageMode /UseOutlines');
	    }
	}
	function RoundedRect($x, $y, $w, $h, $r, $corners = '1234', $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));

        $xc = $x+$w-$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
        if (strpos($corners, '2')===false)
            $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k,($hp-$y)*$k ));
        else
            $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);

        $xc = $x+$w-$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        if (strpos($corners, '3')===false)
            $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-($y+$h))*$k));
        else
            $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);

        $xc = $x+$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        if (strpos($corners, '4')===false)
            $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-($y+$h))*$k));
        else
            $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);

        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        if (strpos($corners, '1')===false)
        {
            $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$y)*$k ));
            $this->_out(sprintf('%.2F %.2F l',($x+$r)*$k,($hp-$y)*$k ));
        }
        else
            $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }




    function WriteHTML($html,$bi=true)
    {
        //remove all unsupported tags
        $this->bi=$bi;
        if ($bi)
            $html=strip_tags($html,"<a><img><p><br><font><tr><blockquote><h1><h2><h3><h4><pre><red><blue><ul><li><hr><b><i><u><strong><em>");
        else
            $html=strip_tags($html,"<a><img><p><br><font><tr><blockquote><h1><h2><h3><h4><pre><red><blue><ul><li><hr>");
        $html=str_replace("\n",' ',$html); //replace carriage returns by spaces
        // debug
        if ($this->debug) { echo $html; exit; }

        $html = str_replace('&trade;','™',$html);
        $html = str_replace('&copy;','©',$html);
        $html = str_replace('&euro;','€',$html);

        $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
        $skip=false;
        foreach($a as $i=>$e)
        {
            if (!$skip) {
                if($this->HREF)
                    $e=str_replace("\n","",str_replace("\r","",$e));
                if($i%2==0)
                {
                    // new line
                    if($this->PRE)
                        $e=str_replace("\r","\n",$e);
                    else
                        $e=str_replace("\r","",$e);
                    //Text
                    if($this->HREF) {
                        $this->PutLink($this->HREF,$e);
                        $skip=true;
                    } else
                        $this->Write(5,stripslashes(txtentities($e)));
                } else {
                    //Tag
                    if (substr(trim($e),0,1)=='/')
						#echo strtoupper(substr($e,strpos($e,'/'))).'<hr>';
                        $this->CloseTag(strtoupper(substr($e,strpos($e,'/'))));
                    else {
                        //Extract attributes
                        $a2=explode(' ',$e);
                        $tag=strtoupper(array_shift($a2));
                        $attr=array();
                        foreach($a2 as $v) {
                            if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                                $attr[strtoupper($a3[1])]=$a3[2];
                        }
                        $this->OpenTag($tag,$attr);
                    }
                }
            } else {
                $this->HREF='';
                $skip=false;
            }
        }
    }

    function OpenTag($tag,$attr)
    {
        //Opening tag
        switch($tag){
            case 'STRONG':
            case 'B':
                if ($this->bi)
                    $this->SetStyle('B',true);
                else
                    $this->SetStyle('U',true);
                break;
            case 'H1':
                $this->Ln(5);
                $this->SetFontSize(22);
                break;
            case 'H2':
                $this->Ln(5);
                $this->SetFontSize(18);
                $this->SetStyle('U',true);
                break;
            case 'H3':
                $this->Ln(5);
                $this->SetFontSize(16);
                $this->SetStyle('U',true);
                break;
            case 'H4':
                $this->Ln(5);
                $this->SetFontSize(14);
                if ($this->bi)
                    $this->SetStyle('B',true);
                break;
            case 'PRE':
                $this->SetFont('Courier','',11);
                $this->SetFontSize(11);
                $this->SetStyle('B',false);
                $this->SetStyle('I',false);
                $this->PRE=true;
                break;
            case 'RED':
                $this->SetTextColor(255,0,0);
                break;
            case 'BLOCKQUOTE':
                $this->Ln(3);
                break;
            case 'BLUE':
                $this->SetTextColor(0,0,255);
                break;
            case 'I':
            case 'EM':
                if ($this->bi)
                    $this->SetStyle('I',true);
                break;
            case 'U':
                $this->SetStyle('U',true);
                break;
            case 'A':
                $this->HREF=$attr['HREF'];
                break;
            case 'IMG':
                if(isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
                    if(!isset($attr['WIDTH']))
                        $attr['WIDTH'] = 0;
                    if(!isset($attr['HEIGHT']))
                        $attr['HEIGHT'] = 0;
                    $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
                    $this->Ln(3);
                }
                break;
            case 'LI':
                $this->Ln(2);
                #$this->SetTextColor(190,0,0);
                $this->Write(5,'-  ');
                #$this->mySetTextColor(-1);
                #$this->Ln(2);
                break;
            case 'TR':
                $this->Ln(7);
                $this->PutLine();
                break;
            case 'BR':
                $this->Ln(4);
                break;
            case 'P':
                $this->Ln(1);
                break;
            case 'HR':
                $this->PutLine();
                break;
            case 'FONT':
                if (isset($attr['COLOR']) && $attr['COLOR']!='') {
                    $coul=hex2dec($attr['COLOR']);
                    $this->mySetTextColor($coul['R'],$coul['G'],$coul['B']);
                    $this->issetcolor=true;
                }
                if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
                    $this->SetFont(strtolower($attr['FACE']));
                    $this->issetfont=true;
                }
                break;
        }
    }

    function CloseTag($tag)
    {
        //Closing tag
        if ($tag=='/H1' || $tag=='/H2' || $tag=='/H3' || $tag=='/H4'){
            $this->Ln(6);
            $this->SetFont('arial','',9);
            $this->SetFontSize(9);
            $this->SetStyle('U',false);
            $this->SetStyle('B',false);
        }
        if ($tag=='/PRE'){
            $this->SetFont('Times','',12);
            $this->SetFontSize(12);
            $this->PRE=false;
        }
        if ($tag=='/P'){
            $this->Ln(5);
        }
        if ($tag=='/LI'){
            $this->Ln(2);
        }
        if ($tag=='/UL'){
            $this->Ln(3);
        }
        if ($tag=='RED' || $tag=='BLUE')
            $this->mySetTextColor(-1);
        if ($tag=='BLOCKQUOTE'){
            $this->Ln(3);
        }
        if($tag=='/STRONG')
            $tag='/B';
        if($tag=='/EM')
            $tag='/I';
        if((!$this->bi) && $tag=='/B')
            $tag='U';
        if($tag=='/B' || $tag=='/I' || $tag=='/U')
            $this->SetStyle($tag,false);
        if($tag=='/A')
            $this->HREF='';
        if($tag=='/FONT'){
            if ($this->issetcolor==true) {
                $this->SetTextColor(0,0,0);
            }
            if ($this->issetfont) {
                $this->SetFont('Times','',12);
                $this->issetfont=false;
            }
        }
    }

    function Footer()
    {
        $this->SetY(-8);
        $this->SetFont('Arial','',8);
        $this->Cell(0,4,'Page '.$this->PageNo().' of {nb}. This resource was downloaded from Boys\' Brigade Programmes Online - http://www.boys-brigade.org.uk/pe - User: '.$_SESSION['user']['ContactID'],0,0,'C',0,'http://www.boys-brigade.org.uk/pe');
    }

    function SetStyle($tag,$enable)
    {
        $this->$tag+=($enable ? 1 : -1);
        $style='';
        foreach(array('B','I','U') as $s) {
            if($this->$s>0)
                $style.=$s;
        }
        $this->SetFont('',$style);
    }

    function PutLink($URL,$txt)
    {
        //Put a hyperlink
        $this->SetTextColor(0,0,255);
        $this->SetStyle('U',true);
        $this->Write(5,$txt,$URL);
        $this->SetStyle('U',false);
        $this->mySetTextColor(-1);
    }

    function PutLine()
    {
        $this->Ln(2);
        $this->Line($this->GetX(),$this->GetY(),$this->GetX()+187,$this->GetY());
        $this->Ln(3);
    }

    function mySetTextColor($r,$g=0,$b=0){
        static $_r=0, $_g=0, $_b=0;

        if ($r==-1)
            $this->SetTextColor($_r,$_g,$_b);
        else {
            $this->SetTextColor($r,$g,$b);
            $_r=$r;
            $_g=$g;
            $_b=$b;
        }
    }
}
?>
