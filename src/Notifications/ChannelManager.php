<?php

declare(strict_types=1);

namespace LoveGem\Notifications;

use LoveGem\Core\Application;

class ChannelManager
{
    protected Application $app;

    protected array $channels = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function channel(string $name = null): Channel
    {
        $name = $name ?? $this->getDefaultChannel();

        return $this->channels[$name] ?? $this->resolve($name);
    }

    protected function resolve(string $name): Channel
    {
        $driver = $this->app['config']->get("notifications.channels.{$name}.driver");

        return match ($driver) {
            'database' => new DatabaseChannel(),
            'mail' => new MailChannel(),
            'broadcast' => new BroadcastChannel(),
            'sms' => new SmsChannel(),
            default => new DatabaseChannel(),
        };
    }

    public function getDefaultChannel(): string
    {
        return $this->app['config']->get('notifications.default', 'database');
    }

    public function send(object $notifiable, object $notification): void
    {
        $notification->setNotifiable($notifiable);

        foreach ($notification->via($notifiable) as $channel) {
            $this->channel($channel)->send($notifiable, $notification);
        }
    }
}
