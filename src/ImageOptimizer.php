<?php

namespace ImageOptimizer;

require_once 'vendor/autoload.php';

use Imagine\Imagick\Imagine;
use Imagine\Image\Box;

class ImageOptimizer
{
  /**
   * @var Imagine
   */
  private $imagine;

  /**
   * @var string
   */
  private string $directory;

  /**
   * @var string
   */
  private $log_file;

  public function __construct($directory = './images')
  {
    $this->imagine = new Imagine();
    $this->directory = $directory;
    $this->log_file = fopen("logs/log.log", "w") or die("Unable to open file");
  }

  /**
   * Recursively checks directories and sub-directories
   * and resizes images to custom size
   *
   * @param int $width
   * @param int $height
   *
   * @throws InvalidArgumentException
   * @throws Exception
   */
  public function resizeAllImages($width = 200, $height = 200)
  {
    try {
      $dir = $this->directory;
      $this->getDateTime("Beginning optimizer", false, true);
      if (!is_dir($dir)) {
        $this->getDateTime("Error reading from directory. Does it exist?", false, true);
        throw new \Exception("Error reading from directory. Does it exist?");
      }
      $files = scandir($dir);

      foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
          // resize image
          $ext = pathinfo($path, PATHINFO_EXTENSION);
          $file = pathinfo($path, PATHINFO_FILENAME);
          if (in_array($ext, ['jpg', 'png', 'jpeg'])) {
            try {
              $newPath = $this->directory . '/_thumb_' . $file . '.' . $ext;
              echo $this->getDateTime("Converting image " . $file . '...');
              $this->imagine->open($path)
                ->thumbnail(new Box($width, $height))
                ->save($newPath);
              echo "done\n";
              fwrite($this->log_file, "done\n");
            } catch (\InvalidArgumentException $err) {
              $this->getDateTime("Something went wrong: " . $err, true);
            } catch (\Exception $err) {
              $this->getDateTime("Something went wrong: " . $err, true);
            }
          }
        } else if ($value != '.' && $value != "..") {
          $this->resizeAllImages($path);
        }
      }
    } catch (\Exception $err) {
      echo $err;
    }
    fclose($this->log_file);
  }

  private function getDateTime($message, $prependNewLine = false, $appendNewLine = false)
  {
    if ($prependNewLine) {
      $text = "\n[" . date("d/m/Y g:ia",strtotime('now')) . "] " . $message . "\n";
    }

    $text = "[" . date("d/m/Y g:ia",strtotime('now')) . "] " . $message;

    if ($appendNewLine) {
      $text = $text . "\n";
    }

    echo $text;
    fwrite($this->log_file, $text);
  }
}