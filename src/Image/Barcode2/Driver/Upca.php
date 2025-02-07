<?php
namespace Vipkwd\Utils\Image\Barcode2\Driver;

/**
 * Image_Barcode2_Driver_Upca class
 *
 * Renders UPC-A barcodes
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Image
 * @package   Image_Barcode2
 * @author    Jeffrey K. Brown <jkb@darkfantastic.net>
 * @author    Didier Fournout <didier.fournout@nyc.fr>
 * @copyright 2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link      http://pear.php.net/package/Image_Barcode2
 */

//require_once 'Image/Barcode2/Driver.php';
//require_once 'Image/Barcode2/Common.php';
//require_once 'Image/Barcode2/Exception.php';
use Vipkwd\Utils\Image\Barcode2\Driver as Image_Barcode2_Driver;
use Vipkwd\Utils\Image\Barcode2\Common as Image_Barcode2_Common;
use Vipkwd\Utils\Image\Barcode2\BException as Image_Barcode2_Exception;
use Vipkwd\Utils\Image\Barcode2\Writer as Image_Barcode2_Writer;
#use Vipkwd\Utils\Image\Barcode2\DualWidth as Image_Barcode2_DualWidth;
/**
 * UPC-A
 *
 * Package which provides a method to create UPC-A barcode using GD library.
 *
 * Slightly Modified Ean13.php to get Upca.php I needed a way to print
 * UPC-A bar codes on a PHP page.  The Image_Barcode2 class seemed like
 * the best way to do it, so I modified ean13 to print in the UPC-A format.
 * Checked the bar code tables against some documentation below (no errors)
 * and validated the changes with my trusty cue-cat.
 * http://www.indiana.edu/~atmat/units/barcodes/bar_t4.htm
 *
 * @category  Image
 * @package   Image_Barcode2
 * @author    Jeffrey K. Brown <jkb@darkfantastic.net>
 * @author    Didier Fournout <didier.fournout@nyc.fr>
 * @copyright 2005 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Image_Barcode2
 */
class Upca extends Image_Barcode2_Common implements Image_Barcode2_Driver
{
    /**
     * Coding map
     * @var array
     */
    private $_codingmap = array(
        '0' => array(
            'L' => array(0,0,0,1,1,0,1),
            'R' => array(1,1,1,0,0,1,0)
        ),
        '1' => array(
            'L' => array(0,0,1,1,0,0,1),
            'R' => array(1,1,0,0,1,1,0)
        ),
        '2' => array(
            'L' => array(0,0,1,0,0,1,1),
            'R' => array(1,1,0,1,1,0,0)
        ),
        '3' => array(
            'L' => array(0,1,1,1,1,0,1),
            'R' => array(1,0,0,0,0,1,0)
        ),
        '4' => array(
            'L' => array(0,1,0,0,0,1,1),
            'R' => array(1,0,1,1,1,0,0)
        ),
        '5' => array(
            'L' => array(0,1,1,0,0,0,1),
            'R' => array(1,0,0,1,1,1,0)
        ),
        '6' => array(
            'L' => array(0,1,0,1,1,1,1),
            'R' => array(1,0,1,0,0,0,0)
        ),
        '7' => array(
            'L' => array(0,1,1,1,0,1,1),
            'R' => array(1,0,0,0,1,0,0)
        ),
        '8' => array(
            'L' => array(0,1,1,0,1,1,1),
            'R' => array(1,0,0,1,0,0,0)
        ),
        '9' => array(
            'L' => array(0,0,0,1,0,1,1),
            'R' => array(1,1,1,0,1,0,0)
        )
    );

    /**
     * Class constructor
     *
     * @param Image_Barcode2_Writer $writer Library to use.
     */
    public function __construct(Image_Barcode2_Writer $writer) 
    {
        parent::__construct($writer);
        $this->setBarcodeHeight(50);
        $this->setBarcodeWidth(1);
    }


    /**
     * Validate barcode
     *
     * @return void
     * @throws Image_Barcode2_Exception
     */
    public function validate()
    {
        // Check barcode for invalid characters
        if (!preg_match('/^[0-9]{12}$/', $this->getBarcode())) {
            throw new Image_Barcode2_Exception('Invalid barcode(...need chars match /^[0-9]{12}$/)');
        }
    }


    /**
     * Draws a UPC-A image barcode
     *
     * @return resource            The corresponding UPC-A image barcode
     *
     * @author  Jeffrey K. Brown <jkb@darkfantastic.net>
     * @author  Didier Fournout <didier.fournout@nyc.fr>
     */
    public function draw()
    {
        $text     = $this->getBarcode();
        $writer   = $this->getWriter();
        $fontsize = $this->getFontSize();
        $width    = $this->getBarcodeWidth();
        $height   = $this->getBarcodeHeight();

        $extraW = 0;
        if ($width > 1) {
            $extraW = round(11 * ($width - 1));
        }

        // Calculate the barcode width
        $barcodewidth = (strlen($text)) * (7 * $width)
            + 3 // left
            + 5 // center
            + 3 // right
            + $writer->imagefontwidth($fontsize) + 1
            + $writer->imagefontwidth($fontsize) + 1 // check digit padding
            + $extraW
            ;


        $barcodelongheight = (int)($writer->imagefontheight($fontsize) / 2)
            + $height;

        // Create the image
        $img = $writer->imagecreate(
            $barcodewidth,
            $barcodelongheight + $writer->imagefontheight($fontsize) + 1
        );

        // Alocate the black and white colors
        $black = $writer->imagecolorallocate($img, 0, 0, 0);
        $white = $writer->imagecolorallocate($img, 255, 255, 255);

        // Fill image with white color
        $writer->imagefill($img, 0, 0, $white);

        // get the first digit which is the key for creating the first 6 bars
        $key = substr($text, 0, 1);

        // Initiate x position
        $xpos = 0;

        // print first digit
        if ($this->showText) {
            $writer->imagestring(
                $img,
                $fontsize,
                $xpos,
                $height,
                $key,
                $black
            );
        }
        $xpos = $writer->imagefontwidth($fontsize) + 1;


        // Draws the left guard pattern (bar-space-bar)
        // bar
        $writer->imagefilledrectangle(
            $img,
            $xpos,
            0,
            $xpos + $width - 1,
            $barcodelongheight,
            $black
        );

        $xpos += $width;
        // space
        $xpos += $width;
        // bar
        $writer->imagefilledrectangle(
            $img,
            $xpos,
            0,
            $xpos + $width - 1,
            $barcodelongheight,
            $black
        );

        $xpos += $width;


        foreach ($this->_codingmap[$key]['L'] as $bar) {
            if ($bar) {
                $writer->imagefilledrectangle(
                    $img,
                    $xpos,
                    0,
                    $xpos + $width - 1,
                    $barcodelongheight,
                    $black
                );
            }
            $xpos += $width;
        }



        // Draw left $text contents
        for ($idx = 1; $idx < 6; $idx ++) {
            $value = substr($text, $idx, 1);

            if ($this->showText) {
                $writer->imagestring(
                    $img,
                    $fontsize,
                    $xpos + 1,
                    $height,
                    $value,
                    $black
                );
            }

            foreach ($this->_codingmap[$value]['L'] as $bar) {
                if ($bar) {
                    $writer->imagefilledrectangle(
                        $img,
                        $xpos,
                        0,
                        $xpos + $width - 1,
                        $height,
                        $black
                    );
                }
                $xpos += $width;
            }
        }


        // Draws the center pattern (space-bar-space-bar-space)
        // space
        $xpos += $width;
        // bar
        $writer->imagefilledrectangle(
            $img,
            $xpos,
            0,
            $xpos + $width - 1,
            $barcodelongheight,
            $black
        );
        $xpos += $width;
        // space
        $xpos += $width;
        // bar
        $writer->imagefilledrectangle(
            $img,
            $xpos,
            0,
            $xpos + $width - 1,
            $barcodelongheight,
            $black
        );
        $xpos += $width;
        // space
        $xpos += $width;


        // Draw right $text contents
        for ($idx = 6; $idx < 11; $idx ++) {
            $value = substr($text, $idx, 1);

            if ($this->showText) {
                $writer->imagestring(
                    $img,
                    $fontsize,
                    $xpos + 1,
                    $height,
                    $value,
                    $black
                );
            }

            foreach ($this->_codingmap[$value]['R'] as $bar) {
                if ($bar) {
                    $writer->imagefilledrectangle(
                        $img,
                        $xpos,
                        0,
                        $xpos + $width - 1,
                        $height,
                        $black
                    );
                }
                $xpos += $width;
            }
        }



        $value = substr($text, 11, 1);
        foreach ($this->_codingmap[$value]['R'] as $bar) {
            if ($bar) {
                $writer->imagefilledrectangle(
                    $img,
                    $xpos,
                    0,
                    $xpos + $width - 1,
                    $barcodelongheight,
                    $black
                );

            }
            $xpos += $width;
        }



        // Draws the right guard pattern (bar-space-bar)
        // bar
        $writer->imagefilledrectangle(
            $img,
            $xpos,
            0,
            $xpos + $width - 1,
            $barcodelongheight,
            $black
        );

        $xpos += $width;
        // space
        $xpos += $width;
        // bar
        $writer->imagefilledrectangle(
            $img,
            $xpos,
            0,
            $xpos + $width - 1,
            $barcodelongheight,
            $black
        );

        $xpos += $width;


        // Print Check Digit
        if ($this->showText) {
            $writer->imagestring(
                $img,
                $fontsize,
                $xpos + 1,
                $height,
                $value,
                $black
            );
        }

        return $img;
    }

} // class
