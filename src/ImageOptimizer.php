<?php

namespace ImageOptimizer;

require_once __DIR__.'/vendor/autoload.php';

class ImageOptimizer
{
  private $imagine;

  private $directory;

  private $log_file;

  public function __construct($directory = './images')
  {
    $this->imagine = new Imagine\Imagick\Imagine();
    $this->directory = $directory;
    $this->log_file = fopen("logs/log.txt", "w") or die("Unable to open file");
    echo "Test";
  }

  public function resizeAllImages($dir)
  {
    $files = scandir($dir);

    $this->getDateTime("Beginning optimizer", true);

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
              ->thumbnail(new \Imagine\Image\Box(224, 300))
              ->save($newPath);
            echo "done\n";
          } catch (\Throwable $th) {
            echo "error\n";
            throw $this->getDateTime("Something went wrong", true);
          }
        }
      } else if ($value != '.' && $value != "..") {
        $this->resizeAllImages($path);
      }
    }
    fclose($this->log_file);
  }

  public function getDateTime($message, $newline = false)
  {
    $text = "[" . date("d/m/Y g:ia",strtotime('now')) . "] " . $message;
    if ($newline) {
      $text = $text . "\n";
    }

    echo $text;
    fwrite($this->log_file, $text);
  }
}


$resizer = new ImageOptimizer();
$resizer->resizeAllImages('./Photos/UploadedToPi');