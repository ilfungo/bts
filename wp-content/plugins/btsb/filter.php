<?php
/**
 * Instagram filters with PHP and ImageMagick
 *
 * @package    Instagraph
 * @author     Webarto &lt;dejan.marjanovic@gmail.com>
 * @copyright  NetTuts+
 * @license    http://creativecommons.org/licenses/by-nc/3.0/ CC BY-NC
 */
class Instagraph
{

    public $_image = NULL;
    public $_output = NULL;
    public $_prefix = 'IMG';
    private $_width = NULL;
    private $_height = NULL;
    private $_tmp = NULL;

    public static function factory($image, $output)
    {
        return new Instagraph($image, $output);
    }

    public function __construct($image, $output)
    {
        if(file_exists($image))
        {
            $this->_image = $image;
            list($this->_width, $this->_height) = getimagesize($image);
            $this->_output = $output;
        }
        else
        {
            throw new Exception('File not found. Aborting.');
        }
    }

    public function tempfile()
    {
        # copy original file and assign temporary name
        $this->_tmp = $this->_prefix.rand();
        copy($this->_image, $this->_tmp);
    }

    public function output()
    {
        # rename working temporary file to output filename
        rename($this->_tmp, $this->_output);
    }

    public function execute($command)
    {
        # remove newlines and convert single quotes to double to prevent errors
        $command = str_replace(array("\n", "'"), array('', '"'), $command);
        $command = escapeshellcmd($command);
        # execute convert program
        exec($command);
    }

    /** ACTIONS */

    public function colortone($input, $color, $level, $type = 0)
    {
        $args[0] = $level;
        $args[1] = 100 - $level;
        $negate = $type == 0? '-negate': '';

        $this->execute("convert
        {$input}
        ( -clone 0 -fill '$color' -colorize 100% )
        ( -clone 0 -colorspace gray $negate )
        -compose blend -define compose:args=$args[0],$args[1] -composite
        {$input}");
    }

    public function border($input, $color = 'black', $width = 20)
    {
        $this->execute("convert $input -bordercolor $color -border {$width}x{$width} $input");
    }

    public function frame($input, $frame)
    {
        $this->execute("convert $input ( '$frame' -resize {$this->_width}x{$this->_height}! -unsharp 1.5×1.0+1.5+0.02 ) -flatten $input");
    }

    public function vignette($input, $color_1 = 'none', $color_2 = 'black', $crop_factor = 1.5, $method = 'ColorBurn')
    {
        $crop_x = floor($this->_width * $crop_factor);
        $crop_y = floor($this->_height * $crop_factor);

        $crop = max($crop_x, $crop_y);

        echo "convert ( {$input} ) ( -size {$crop_x}x{$crop_y} radial-gradient: -function Polynomial '2.8, -1.2' '$color_1-$color_2' -gravity center -crop {$this->_width}x{$this->_height}+0+0 +repage ) -compose $method -composite {$input}";

        $this->execute("convert
        ( {$input} )
        ( -size {$crop_x}x{$crop_y}
        radial-gradient: -function Polynomial '2.8, -1.2'  $color_1-$color_2
        -gravity center -crop {$this->_width}x{$this->_height}+0+0 +repage )
        -compose $method -composite
        {$input}");
    }

    /** RESERVED FOR FILTER METHODS */

    public function amaro($vignette=false)
    {
        $this->tempfile();

        if($vignette)
            $this->vignette($this->_tmp, 'none', 'black',1.9);
        $command =  "convert -units PixelsPerInch {$this->_tmp} -density 300 ".  "-brightness-contrast 20x12% ".
                                               "( +clone -fill rgba(100%,97%,85%,1.00) -colorize 100 ) -compose multiply -composite ".
                                               "-channel B +level 45%,100%  ".


                    $this->_tmp;
        $this->execute($command);

        $this->output();
    }

    public function nashville($vignette=false)
    {
        $this->tempfile();
        $command =  "convert -units PixelsPerInch {$this->_tmp} -density 300 ".  "-channel B +level 23%,100%  ".
                                            "-channel RGB ".
                                               "( +clone -fill rgba(97%,87%,68%,1.00) -colorize 100 ) -compose multiply -composite ".
                                               "-brightness-contrast 14 ".
                                               "-modulate 100,100,107 ".
                    $this->_tmp;

        if($vignette)
            $this->vignette($this->_tmp, 'none', 'black',1.9);

        $this->execute($command);

        $this->output();
    }

    public function hudson($vignette=false)
    {
        $this->tempfile();
        $command =  "convert -units PixelsPerInch {$this->_tmp} -density 300 "."-channel B +level 28%,100%  ".
                                             "-channel G +level 15,100% " .
                                             "-channel RGB  -brightness-contrast 10x10% -background black ".
                    $this->_tmp;

        if($vignette)
            $this->vignette($this->_tmp, 'none', 'black',1.8);

        $this->execute($command);
        $this->output();
    }

    public function brannan($vignette=false)
    {
        $this->tempfile();

        $command =  "convert -units PixelsPerInch {$this->_tmp} -density 300 "." -brightness-contrast 6x50% ".
        "( +clone -fill rgba(93%,87%,62%,0.59) -colorize 100 ) ".
        //"( +clone -fill rgba(93%,87%,62%,0.59) -colorize 100 -alpha set -channel a -evaluate set 40% +channel ) ".
        "-compose multiply -composite ".
        " +level 18,100% -brightness-contrast 14x14% -modulate 100,92 ".
                    $this->_tmp;
        if($vignette)
            $this->vignette($this->_tmp, 'none', 'black',1.8);

        $this->execute($command);

        //$this->colortone($this->_tmp, '#eddd9e', 50, 1);

        $this->output();
    }

    public function sierra($vignette=false)
    {
        $this->tempfile();

        $command =  "convert -units PixelsPerInch {$this->_tmp} -density 300 "." +level 21,100%  -channel R +level 8%,100% -channel RGB -brightness-contrast 0x23% ".
                             //        "-recolor ' 0001 0000 0000
                             //                    0000 0.96 0000
                             //                    0000 0000 0001 ' ".
                    $this->_tmp;

        if($vignette)
            $this->vignette($this->_tmp, 'none', 'black',1.8);

        $this->execute($command);

        //$this->colortone($this->_tmp, '#eddd9e', 50, 1);

        $this->output();
    }

    public function inkwell($vignette=false)
    {
        $this->tempfile();

        $command =  "convert -units PixelsPerInch {$this->_tmp} -density 300 ".
                                     "-recolor '0.299 0.587 0.114
                                                0.299 0.587 0.114
                                                0.299 0.587 0.114' ".
                                                "-sigmoidal-contrast 4x50% ".
                                               // "-background black -vignette 0x0%-40%-40% ".

                    $this->_tmp;

        if($vignette)
            $this->vignette($this->_tmp, 'none', 'black', 1.8);

        $this->execute($command);



        //$this->colortone($this->_tmp, '#eddd9e', 50, 1);

        $this->output();
    }

public function willow($vignette=false)
    {
        $this->tempfile();

        $command =  "convert -units PixelsPerInch {$this->_tmp} -density 300 ".
                                    "-brightness-contrast 20x10 ".
        $this->_tmp;

        if($vignette)
            $this->vignette($this->_tmp, 'none', 'black',1.8);

        $this->execute($command);



        //$this->colortone($this->_tmp, '#eddd9e', 50, 1);

        $this->output();
    }




}



$input = $argv[1];
$output = $argv[2];
$outputdir =pathinfo($output, PATHINFO_DIRNAME);
$filter = $argv[3];
$vignette = (bool)$argv[4];



    if (!file_exists($outputdir)) {
    mkdir($outputdir, 0777, true);
}

try
{

    $instagraph = Instagraph::factory($input, $output);
}
catch (Exception $e)
{
    echo $e->getMessage();
    die;
}


$instagraph->$filter($vignette);





?>
