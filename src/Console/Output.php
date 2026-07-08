<?php

declare(strict_types=1);

namespace LoveGem\Console;

class Output
{
    public function write(string $message): void
    {
        fwrite(STDOUT, $message);
    }

    public function writeln(string $message): void
    {
        $this->write($message . PHP_EOL);
    }

    public function info(string $message): void
    {
        $this->writeln("\033[32m{$message}\033[0m");
    }

    public function line(string $message): void
    {
        $this->writeln($message);
    }

    public function error(string $message): void
    {
        $this->writeln("\033[31m{$message}\033[0m");
    }

    public function warn(string $message): void
    {
        $this->writeln("\033[33m{$message}\033[0m");
    }

    public function table(array $headers, array $rows): void
    {
        $widths = [];

        foreach ($headers as $index => $header) {
            $widths[$index] = strlen($header);
        }

        foreach ($rows as $row) {
            foreach ($row as $index => $cell) {
                $widths[$index] = max($widths[$index] ?? 0, strlen((string) $cell));
            }
        }

        $headerLine = '|';
        $separatorLine = '|';

        foreach ($headers as $index => $header) {
            $headerLine .= ' ' . str_pad($header, $widths[$index]) . ' |';
            $separatorLine .= str_repeat('-', $widths[$index] + 2) . '|';
        }

        $this->writeln($headerLine);
        $this->writeln($separatorLine);

        foreach ($rows as $row) {
            $line = '|';
            foreach ($row as $index => $cell) {
                $line .= ' ' . str_pad((string) $cell, $widths[$index]) . ' |';
            }
            $this->writeln($line);
        }
    }

    public function newLine(int $count = 1): void
    {
        $this->writeln(str_repeat(PHP_EOL, $count));
    }

    public function progress(int $total, int $current): void
    {
        $percentage = round(($current / $total) * 100);
        $filled = round($percentage / 2);
        $empty = 50 - $filled;

        $progress = str_repeat('█', $filled) . str_repeat('░', $empty);
        $this->write("\r\033[32m[{$progress}]\033[0m {$percentage}% ({$current}/{$total})");
    }

    public function clearLine(): void
    {
        $this->write("\r\033[2K");
    }
}
