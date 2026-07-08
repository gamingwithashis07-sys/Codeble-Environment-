<?php

declare(strict_types=1);

namespace LoveGem\Filesystem;

class Filesystem
{
    protected string $defaultDisk = 'local';

    protected array $disks = [];

    public function disk(string $name = null): FilesystemAdapter
    {
        $name = $name ?? $this->defaultDisk;

        return $this->disks[$name] ?? $this->resolveDisk($name);
    }

    protected function resolveDisk(string $name): FilesystemAdapter
    {
        $config = $this->getConfig("filesystems.disks.{$name}");

        return $this->disks[$name] = new FilesystemAdapter($name, $config);
    }

    protected function getConfig(string $key): array
    {
        return app('config')->get($key, []);
    }

    public function put(string $path, mixed $contents, string $disk = null): bool
    {
        return $this->disk($disk)->put($path, $contents);
    }

    public function get(string $path, string $disk = null): ?string
    {
        return $this->disk($disk)->get($path);
    }

    public function delete(string $path, string $disk = null): bool
    {
        return $this->disk($disk)->delete($path);
    }

    public function exists(string $path, string $disk = null): bool
    {
        return $this->disk($disk)->exists($path);
    }

    public function size(string $path, string $disk = null): int
    {
        return $this->disk($disk)->size($path);
    }

    public function lastModified(string $path, string $disk = null): int
    {
        return $this->disk($disk)->lastModified($path);
    }

    public function copy(string $source, string $destination, string $disk = null): bool
    {
        return $this->disk($disk)->copy($source, $destination);
    }

    public function move(string $source, string $destination, string $disk = null): bool
    {
        return $this->disk($disk)->move($source, $destination);
    }

    public function files(string $directory = '', string $disk = null): array
    {
        return $this->disk($disk)->files($directory);
    }

    public function allFiles(string $directory = '', string $disk = null): array
    {
        return $this->disk($disk)->allFiles($directory);
    }

    public function directories(string $directory = '', string $disk = null): array
    {
        return $this->disk($disk)->directories($directory);
    }

    public function allDirectories(string $directory = '', string $disk = null): array
    {
        return $this->disk($disk)->allDirectories($directory);
    }

    public function makeDirectory(string $path, string $disk = null): bool
    {
        return $this->disk($disk)->makeDirectory($path);
    }

    public function deleteDirectory(string $directory, string $disk = null): bool
    {
        return $this->disk($disk)->deleteDirectory($directory);
    }

    public function url(string $path, string $disk = null): string
    {
        return $this->disk($disk)->url($path);
    }

    public function temporaryUrl(string $path, \DateTimeInterface $expiration, array $options = [], string $disk = null): string
    {
        return $this->disk($disk)->temporaryUrl($path, $expiration, $options);
    }

    public function mime(string $path, string $disk = null): ?string
    {
        return $this->disk($disk)->mimeType($path);
    }

    public function append(string $path, string $data, string $disk = null): void
    {
        $this->disk($disk)->append($path, $data);
    }

    public function prepend(string $path, string $data, string $disk = null): void
    {
        $this->disk($disk)->prepend($path, $data);
    }

    public function setVisibility(string $path, string $visibility, string $disk = null): void
    {
        $this->disk($disk)->setVisibility($path, $visibility);
    }

    public function getVisibility(string $path, string $disk = null): string
    {
        return $this->disk($disk)->getVisibility($path);
    }

    public function upload(string $path, $file, string $disk = null): string
    {
        return $this->disk($disk)->putFile($path, $file);
    }

    public function putFileAs(string $path, $file, string $name, string $disk = null): string
    {
        return $this->disk($disk)->putFileAs($path, $file, $name);
    }
}
