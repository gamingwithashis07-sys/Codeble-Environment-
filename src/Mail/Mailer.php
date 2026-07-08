<?php

declare(strict_types=1);

namespace LoveGem\Mail;

use LoveGem\Core\Application;
use LoveGem\View\Factory;

class Mailer
{
    protected Application $app;

    protected Factory $views;

    protected array $from = [];

    public function __construct(Application $app, Factory $views)
    {
        $this->app = $app;
        $this->views = $views;
    }

    public function to(array|string $recipients): static
    {
        $this->to = $this->parseRecipients($recipients);
        return $this;
    }

    public function cc(array|string $recipients): static
    {
        $this->cc = $this->parseRecipients($recipients);
        return $this;
    }

    public function bcc(array|string $recipients): static
    {
        $this->bcc = $this->parseRecipients($recipients);
        return $this;
    }

    public function from(array|string $address, string $name = null): static
    {
        $this->from = is_string($address)
            ? ['address' => $address, 'name' => $name ?? '']
            : $address;

        return $this;
    }

    public function subject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function view(string $view, array $data = []): static
    {
        $this->view = $view;
        $this->viewData = $data;
        return $this;
    }

    public function text(string $text, array $data = []): static
    {
        $this->text = $text;
        $this->textData = $data;
        return $this;
    }

    public function html(string $html): static
    {
        $this->html = $html;
        return $this;
    }

    public function send(callable $callback): void
    {
        $message = new Message();

        $message->from(
            $this->from['address'] ?? config('mail.from.address'),
            $this->from['name'] ?? config('mail.from.name')
        );

        foreach ($this->to as $recipient) {
            $message->to($recipient['address'], $recipient['name']);
        }

        foreach ($this->cc as $recipient) {
            $message->cc($recipient['address'], $recipient['name']);
        }

        foreach ($this->bcc as $recipient) {
            $message->bcc($recipient['address'], $recipient['name']);
        }

        $message->subject($this->subject);

        if (isset($this->view)) {
            $message->html($this->renderView($this->view, $this->viewData));
        } elseif (isset($this->html)) {
            $message->html($this->html);
        }

        $callback($message);

        $this->dispatch($message);
    }

    public function raw(string $text, array $data = []): void
    {
        $message = new Message();
        $message->subject($this->subject);
        $message->text($this->renderView($this->text ?? '', $data));

        $this->dispatch($message);
    }

    protected function renderView(string $view, array $data): string
    {
        return $this->views->make($view, $data)->render();
    }

    protected function parseRecipients(array|string $recipients): array
    {
        if (is_string($recipients)) {
            return [['address' => $recipients, 'name' => '']];
        }

        $parsed = [];

        foreach ($recipients as $address => $name) {
            if (is_int($address)) {
                $parsed[] = ['address' => $name, 'name' => ''];
            } else {
                $parsed[] = ['address' => $address, 'name' => $name];
            }
        }

        return $parsed;
    }

    protected function dispatch(Message $message): void
    {
        $transport = new Transport('smtp');
        $transport->send($message);
    }
}
