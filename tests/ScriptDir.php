<?php

declare(strict_types=1);

namespace NaokiTsuchiya\RayDiPsrContainer;

use function array_filter;
use function glob;
use function is_dir;
use function is_string;
use function mkdir;
use function rmdir;
use function unlink;

use const DIRECTORY_SEPARATOR;

final class ScriptDir
{
    public function init(string $path): void
    {
        $this->cleanup($path);
        mkdir($path, 0777, true);
    }

    public function cleanup(string $path): void
    {
        $this->deleteFiles($path);
    }

    private function deleteFiles(string $path): void
    {
        $files = array_filter((array) glob($path . DIRECTORY_SEPARATOR . '*'), static function ($file) {
            return is_string($file);
        });

        foreach ($files as $file) {
            is_dir($file) ? $this->deleteFiles($file) : unlink($file);
            @rmdir($file);
        }

        if (is_dir($path)) {
            rmdir($path);
        }
    }
}
