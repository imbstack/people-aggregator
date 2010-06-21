<?php
/** !
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* [filename] is a part of PeopleAggregator.
* [description including history]
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* @author [creator, or "Original Author"]
* @license http://bit.ly/aVWqRV PayAsYouGo License
* @copyright Copyright (c) 2010 Broadband Mechanics
* @package PeopleAggregator
*/
?>
<?php
// GifSplit by Laszlo Zsidi, http://gifs.hu
// From http://www.phpclasses.org/browse/file/15123.htmlL
class GifSplit {
    /*===========================================*/
    /*==           V A R I A B L E S           ==*/
    /*===========================================*/
    var $image_count = 0;
    var $buffer      = array();
    var $global      = array();
    var $gif = array(
        0x47,
        0x49,
        0x46,
    );
    var $logical_screen_descriptor;
    var $global_color_table_size;
    var $global_color_table_code;
    var $global_color_table_flag;
    var $image_descriptor;
    var $global_sorted;
    var $fin;
    var $fou;
    var $sp;
    var $fm;
    var $es;
    var $output;

    function GifSplit($image, $format, $path, $output) {
        error_reporting(0);
        $this->fm     = $format;
        $this->sp     = $path;
        $this->output = $output;
        if($this->fin = fopen($image, "rb")) {
            $this->getbytes(6);
            if(!$this->arrcmp($this->buffer, $this->gif, 3)) {
                $this->es = "error #1";
                return(0);
            }
            $this->getbytes(7);
            $this->logical_screen_descriptor = $this->buffer;
            $this->global_color_table_flag   = ($this->buffer[4]&0x80) ? TRUE : FALSE;
            $this->global_color_table_code   = ($this->buffer[4]&0x07);
            $this->global_color_table_size   = 2 << $this->global_color_table_code;
            $this->global_sorted             = ($this->buffer[4]&0x08) ? TRUE : FALSE;
            if($this->global_color_table_flag) {
                $this->getbytes(3*$this->global_color_table_size);
                for($i = 0; $i < ((3*$this->global_color_table_size)); $i++) {
                    $this->global[$i] = $this->buffer[$i];
                }
            }
            //  $i= 0;
            for($loop = true; $loop;) {
                $this->getbytes(1);
                switch($this->buffer[0]) {
                    case 0x21:
                        $this->read_extension();
                        break;
                    case 0x2C:
                        $this->read_image_descriptor();
                        $loop = false;
                        // For Only First Frame when we found the value of $this->buffer[0] = 44 , we Break the for Loop
                        break;
                    case 0x3B:
                        $loop = false;
                        break;
                    default:
                        $this->es = sprintf("Unrecognized byte code %u\n<br>", $this->buffer[0]);
                }
                // This is case of only 1 image of animated gif
            }
            fclose($this->fin);
        }
        else {
            $this->es = "error #2";
            return(0);
        }
        $this->es = "ok";
    }

    /*///////////////////////////////////////////////*/
    /*//        Function :: read_extension()       //*/
    /*///////////////////////////////////////////////*/
    function read_extension() {
        /* Reset global variables */
        $this->buffer = array();
        $this->getbytes(1);
        for(;;) {
            $this->getbytes(1);
            if(($u = $this->buffer[0]) == 0) {
                break;
            }
            $this->getbytes($u);
        }
    }

    /*///////////////////////////////////////////////*/
    /*//    Function :: read_image_descriptor()    //*/
    /*///////////////////////////////////////////////*/
    function read_image_descriptor() {
        /* Reset global variables */
        $this->buffer = array();
        $this->fou = '';

        /* Header -> GIF89a */
        $this->fou .= "\x47\x49\x46\x38\x39\x61";
        $this->getbytes(9);
        for($i = 0; $i < 9; $i++) {
            $this->image_descriptor[$i] = $this->buffer[$i];
        }
        $local_color_table_flag = ($this->buffer[8]&0x80) ? TRUE : FALSE;
        if($local_color_table_flag) {
            $code = ($this->buffer[8]&0x07);
            $sorted = ($this->buffer[8]&0x20) ? TRUE : FALSE;
        }
        else {
            $code = $this->global_color_table_code;
            $sorted = $this->global_sorted;
        }
        $size                                = 2 << $code;
        $this->logical_screen_descriptor[4] &= 0x70;
        $this->logical_screen_descriptor[4] |= 0x80;
        $this->logical_screen_descriptor[4] |= $code;
        if($sorted) {
            $this->logical_screen_descriptor[4] |= 0x08;
        }
        $this->putbytes($this->logical_screen_descriptor, 7);
        if($local_color_table_flag) {
            $this->getbytes(3*$size);
            $this->putbytes($this->buffer, 3*$size);
        }
        else {
            $this->putbytes($this->global, 3*$size);
        }
        $this->fou .= "\x2C";
        $this->image_descriptor[8] &= 0x40;
        $this->putbytes($this->image_descriptor, 9);

        /* LZW minimum code size */
        $this->getbytes(1);
        $this->putbytes($this->buffer, 1);

        /* Image Data */
        for(;;) {
            $this->getbytes(1);
            $this->putbytes($this->buffer, 1);
            if(($u = $this->buffer[0]) == 0) {
                break;
            }
            $this->getbytes($u);
            $this->putbytes($this->buffer, $u);
        }

        /* trailer */
        $this->fou .= "\x3B";

        /* Write to file */
        switch($this->fm) {
            /* Write as BMP */
            case "BMP":
                $im = imageCreateFromString($this->fou);
                $framename = $this->sp.$this->image_count++.".bmp";
                if(!$this->imageBmp($im, $framename)) {
                    $this->es = "error #3";
                    return(0);
                }
                imageDestroy($im);
                break;

            /* Write as PNG */
            case "PNG":
                $im = imageCreateFromString($this->fou);
                $framename = $this->sp.$this->image_count++.".png";
                if(!imagePng($im, $framename)) {
                    $this->es = "error #3";
                    return(0);
                }
                imageDestroy($im);
                break;

            /* Write as JPG */
            case "JPG":
                $im = imageCreateFromString($this->fou);
                $framename = $this->sp.$this->image_count++.".jpg";
                if(!imageJpeg($im, $framename)) {
                    $this->es = "error #3";
                    return(0);
                }
                imageDestroy($im);
                break;

            /* Write as GIF */
            case "GIF":
                $im = imageCreateFromString($this->fou);
                $framename = $this->output.$this->sp.".gif";
                if(!imageGif($im, $framename)) {
                    $this->es = "error #3";
                    return(0);
                }
                imageDestroy($im);
                break;
        }
    }

    /*///////////////////////////////////////////////*/
    /*//             BMP creation group            //*/
    /*///////////////////////////////////////////////*/
    /* ImageBMP */
    function imageBmp($img, $file, $RLE = 0) {
        $ColorCount    = imagecolorstotal($img);
        $Transparent   = imagecolortransparent($img);
        $IsTransparent = $Transparent !=-1;
        if($IsTransparent) {
            $ColorCount--;
        }
        if($ColorCount == 0) {
            $ColorCount = 0;
            $BitCount = 24;
        }
        if(($ColorCount > 0) && ($ColorCount <= 2)) {
            $ColorCount = 2;
            $BitCount = 1;
        }
        if(($ColorCount > 2) && ($ColorCount <= 16)) {
            $ColorCount = 16;
            $BitCount = 4;
        }
        if(($ColorCount > 16) && ($ColorCount <= 256)) {
            $ColorCount = 0;
            $BitCount = 8;
        }
        $Width  = imageSX($img);
        $Height = imageSY($img);
        $Zbytek = (4-($Width/(8/$BitCount))%4)%4;
        if($BitCount < 24) {
            $palsize = pow(2, $BitCount)*4;
        }
        $size   = (floor($Width/(8/$BitCount))+$Zbytek)*$Height+54;
        $size  += $palsize;
        $offset = 54+$palsize;
        // Bitmap File Header
        $ret  = 'BM';
        $ret .= $this->int_to_dword($size);
        $ret .= $this->int_to_dword(0);
        $ret .= $this->int_to_dword($offset);
        // Bitmap Info Header
        $ret .= $this->int_to_dword(40);
        $ret .= $this->int_to_dword($Width);
        $ret .= $this->int_to_dword($Height);
        $ret .= $this->int_to_word(1);
        $ret .= $this->int_to_word($BitCount);
        $ret .= $this->int_to_dword($RLE);
        $ret .= $this->int_to_dword(0);
        $ret .= $this->int_to_dword(0);
        $ret .= $this->int_to_dword(0);
        $ret .= $this->int_to_dword(0);
        $ret .= $this->int_to_dword(0);
        // image data
        $CC = $ColorCount;
        $sl1 = strlen($ret);
        if($CC == 0) {
            $CC = 256;
        }
        if($BitCount < 24) {
            $ColorTotal = imagecolorstotal($img);
            if($IsTransparent) {
                $ColorTotal--;
            }
            for($p = 0; $p < $ColorTotal; $p++) {
                $color = imagecolorsforindex($img, $p);
                $ret  .= $this->inttobyte($color["blue"]);
                $ret  .= $this->inttobyte($color["green"]);
                $ret  .= $this->inttobyte($color["red"]);
                $ret  .= $this->inttobyte(0);
            }
            $CT = $ColorTotal;
            for($p = $ColorTotal; $p < $CC; $p++) {
                $ret .= $this->inttobyte(0);
                $ret .= $this->inttobyte(0);
                $ret .= $this->inttobyte(0);
                $ret .= $this->inttobyte(0);
            }
        }
        if($BitCount <= 8) {
            for($y = $Height-1; $y >= 0; $y--) {
                $bWrite = "";
                for($x = 0; $x < $Width; $x++) {
                    $color = imagecolorat($img, $x, $y);
                    $bWrite .= $this->decbinx($color, $BitCount);
                    if(strlen($bWrite) == 8) {
                        $retd .= $this->inttobyte(bindec($bWrite));
                        $bWrite = "";
                    }
                }
                if((strlen($bWrite) < 8) and (strlen($bWrite) != 0)) {
                    $sl = strlen($bWrite);
                    for($t = 0; $t < 8-$sl; $t++) {
                        $sl .= "0";
                    }
                    $retd .= $this->inttobyte(bindec($bWrite));
                }
                for($z = 0; $z < $Zbytek; $z++) {
                    $retd .= $this->inttobyte(0);
                }
            }
        }
        if(($RLE == 1) and ($BitCount == 8)) {
            for($t = 0; $t < strlen($retd); $t += 4) {
                if($t != 0) {
                    if(($t)%$Width == 0) {
                        $ret .= chr(0).chr(0);
                    }
                }
                if(($t+5)%$Width == 0) {
                    $ret .= chr(0).chr(5).substr($retd, $t, 5).chr(0);
                    $t += 1;
                }
                if(($t+6)%$Width == 0) {
                    $ret .= chr(0).chr(6).substr($retd, $t, 6);
                    $t += 2;
                }
                else {
                    $ret .= chr(0).chr(4).substr($retd, $t, 4);
                }
            }
            $ret .= chr(0).chr(1);
        }
        else {
            $ret .= $retd;
        }
        if($BitCount == 24) {
            for($z = 0; $z < $Zbytek; $z++) {
                $Dopl .= chr(0);
            }
            for($y = $Height-1; $y >= 0; $y--) {
                for($x = 0;
                $x < $Width;
                $x++) {
                    $color = imagecolorsforindex($img, ImageColorAt($img, $x, $y));
                    $ret .= chr($color["blue"]).chr($color["green"]).chr($color["red"]);
                }
                $ret .= $Dopl;
            }
        }
        if(fwrite(fopen($file, "wb"), $ret)) {
            return true;
        }
        else {
            return false;
        }
    }

    /* INT 2 WORD */
    function int_to_word($n) {
        return chr($n&255).chr(($n >> 8)&255);
    }

    /* INT 2 DWORD */
    function int_to_dword($n) {
        return chr($n&255).chr(($n >> 8)&255).chr(($n >> 16)&255).chr(($n >> 24)&255);
    }

    /* INT 2 BYTE */
    function inttobyte($n) {
        return chr($n);
    }

    /* DECIMAL 2 BIN */
    function decbinx($d, $n) {
        $bin = decbin($d);
        $sbin = strlen($bin);
        for($j = 0; $j < $n-$sbin; $j++) {
            $bin = "0$bin";
        }
        return $bin;
    }

    /*///////////////////////////////////////////////*/
    /*//            Function :: arrcmp()           //*/
    /*///////////////////////////////////////////////*/
    function arrcmp($b, $s, $l) {
        for($i = 0; $i < $l; $i++) {
            if($s {
                $i
            } != $b {
                $i
            }) {
                return false;
            }
        }
        return true;
    }

    /*///////////////////////////////////////////////*/
    /*//           Function :: getbytes()          //*/
    /*///////////////////////////////////////////////*/
    function getbytes($l) {
        for($i = 0; $i < $l; $i++) {
            $bin = unpack('C*', fread($this->fin, 1));
            $this->buffer[$i] = $bin[1];
        }
        return $this->buffer;
    }

    /*///////////////////////////////////////////////*/
    /*//           Function :: putbytes()          //*/
    /*///////////////////////////////////////////////*/
    function putbytes($s, $l) {
        for($i = 0; $i < $l; $i++) {
            $this->fou .= pack('C*', $s[$i]);
        }
    }

    function getReport() {
        return $this->es;
    }
}
?>
