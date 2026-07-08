<?php

declare(strict_types=1);

namespace LoveGem\Filesystem;

class FilesystemAdapter
{
    protected string $name;

    protected array $config;

    protected string $root;

    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->config = $config;
        $this->root = $config['root'] ?? storage_path("app/{$name}");
    }

    public function put(string $path, mixed $contents): bool
    {
        $path = $this->prefixPath($path);
        $this->ensureDirectoryExists(dirname($path));

        return file_put_contents($path, $contents) !== false;
    }

    public function get(string $path): ?string
    {
        $path = $this->prefixPath($path);

        if (!file_exists($path)) {
            return null;
        }

        return file_get_contents($path);
    }

    public function delete(string $path): bool
    {
        $path = $this->prefixPath($path);

        if (file_exists($path)) {
            return unlink($path);
        }

        return false;
    }

    public function exists(string $path): bool
    {
        return file_exists($this->prefixPath($path));
    }

    public function size(string $path): int
    {
        return filesize($this->prefixPath($path));
    }

    public function lastModified(string $path): int
    {
        return filemtime($this->prefixPath($path));
    }

    public function copy(string $source, string $destination): bool
    {
        return copy(
            $this->prefixPath($source),
            $this->prefixPath($destination)
        );
    }

    public function move(string $source, string $destination): bool
    {
        return rename(
            $this->prefixPath($source),
            $this->prefixPath($destination)
        );
    }

    public function files(string $directory = ''): array
    {
        $directory = $this->prefixPath($directory);
        $files = glob($directory . '/*');

        return array_filter($files, function ($file) {
            return is_file($file);
        });
    }

    public function allFiles(string $directory = ''): array
    {
        $directory = $this->prefixPath($directory);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        $files = [];

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    public function directories(string $directory = ''): array
    {
        $directory = $this->prefixPath($directory);
        $directories = glob($directory . '/*');

        return array_filter($directories, function ($dir) {
            return is_dir($dir);
        });
    }

    public function allDirectories(string $directory = ''): array
    {
        $directory = $this->prefixPath($directory);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        $directories = [];

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $directories[] = $file->getPathname();
            }
        }

        return $directories;
    }

    public function makeDirectory(string $path): bool
    {
        return mkdir($this->prefixPath($path), 0755, true);
    }

    public function deleteDirectory(string $directory): bool
    {
        $directory = $this->prefixPath($directory);

        if (!is_dir($directory)) {
            return true;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        return rmdir($directory);
    }

    public function url(string $path): string
    {
        $baseUrl = $this->config['url'] ?? '';

        return $baseUrl . '/' . ltrim($path, '/');
    }

    public function temporaryUrl(string $path, \DateTimeInterface $expiration, array $options = []): string
    {
        return $this->url($path);
    }

    public function mimeType(string $path): ?string
    {
        $path = $this->prefixPath($path);

        if (!file_exists($path)) {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($path);
    }

    public function append(string $path, string $data): void
    {
        $path = $this->prefixPath($path);
        $this->ensureDirectoryExists(dirname($path));

        file_put_contents($path, $data, FILE_APPEND);
    }

    public function prepend(string $path, string $data): void
    {
        $path = $this->prefixPath($path);
        $this->ensureDirectoryExists(dirname($path));

        $contents = file_exists($path) ? file_get_contents($path) : '';
        file_put_contents($path, $data . $contents);
    }

    public function setVisibility(string $path, string $visibility): void
    {
        $path = $this->prefixPath($path);

        chmod($path, $visibility === 'public' ? 0755 : 0644);
    }

    public function getVisibility(string $path): string
    {
        $path = $this->prefixPath($path);

        return (fileperms($path) & 0777) >= 0755 ? 'public' : 'private';
    }

    public function putFile(string $path, $file): string
    {
        $name = $file->getClientOriginalName();
        $path = $this->prefixPath($path . '/' . $name);

        $this->ensureDirectoryExists(dirname($path));

        move_uploaded_file($file->getPathname(), $path);

        return $path;
    }

    public function putFileAs(string $path, $file, string $name): string
    {
        $path = $this->prefixPath($path . '/' . $name);

        $this->ensureDirectoryExists(dirname($path));

        move_uploaded_file($file->getPathname(), $path);

        return $path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function prefixPath(string $path): string
    {
        return $this->root . '/' . ltrim($path, '/');
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}
