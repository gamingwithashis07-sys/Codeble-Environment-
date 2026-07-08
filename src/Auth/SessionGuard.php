<?php

declare(strict_types=1);

namespace LoveGem\Auth;

use LoveGem\Core\Application;
use LoveGem\Session\Store;

class SessionGuard implements Guard
{
    protected Application $app;

    protected Store $session;

    protected ?object $user = null;

    protected bool $loggedOut = false;

    protected bool $viaRemember = false;

    public function __construct(Application $app, Store $session)
    {
        $this->app = $app;
        $this->session = $session;
    }

    public function check(): bool
    {
        return !is_null($this->user());
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function user(): ?object
    {
        if ($this->loggedOut) {
            return null;
        }

        if (!is_null($this->user)) {
            return $this->user;
        }

        $id = $this->session->get('login_user_'.get_class($this));

        if (!is_null($id)) {
            $this->user = $this->resolveUser($id);
        }

        return $this->user;
    }

    public function id(): mixed
    {
        $user = $this->user();

        return $user ? $user->getKey() : null;
    }

    public function validate(array $credentials = []): bool
    {
        $user = $this->retrieveByCredentials($credentials);

        if ($user && $this->validateCredentials($user, $credentials)) {
            $this->setUser($user);
            return true;
        }

        return false;
    }

    public function attempt(array $credentials, bool $remember = false): bool
    {
        $user = $this->retrieveByCredentials($credentials);

        if ($user && $this->validateCredentials($user, $credentials)) {
            $this->login($user, $remember);
            return true;
        }

        return false;
    }

    public function login(object $user, bool $remember = false): void
    {
        $this->updateSession($user->getKey());

        $this->setUser($user);

        if ($remember) {
            $this->queueRecallerCookie($user);
        }

        $this->fireLoginEvent($user, true);
    }

    public function loginUsingId(mixed $id, bool $remember = false): ?object
    {
        $user = $this->resolveUser($id);

        if ($user) {
            $this->login($user, $remember);
        }

        return $user;
    }

    public function onceUsingId(mixed $id): ?object
    {
        $user = $this->resolveUser($id);

        if ($user) {
            $this->setUser($user);
        }

        return $user;
    }

    public function logout(): void
    {
        $user = $this->user();

        $this->clearUserDataFromStorage();

        if (!is_null($user)) {
            $this->fireLogoutEvent($user);
        }

        $this->user = null;
        $this->loggedOut = true;
    }

    protected function updateSession(mixed $id): void
    {
        $this->session->put('login_user_'.get_class($this), $id);
        $this->session->regenerate(true);
    }

    protected function clearUserDataFromStorage(): void
    {
        $this->session->forget('login_user_'.get_class($this));
        $this->session->forget('remember_web_user_'.get_class($this));
    }

    protected function retrieveByCredentials(array $credentials): ?object
    {
        $model = $this->createModel();

        return $model->where('email', $credentials['email'] ?? null)->first();
    }

    protected function validateCredentials(object $user, array $credentials): bool
    {
        $plain = $credentials['password'] ?? '';

        return app('hash')->check($plain, $user->password);
    }

    protected function resolveUser(mixed $id): ?object
    {
        $model = $this->createModel();

        return $model->find($id);
    }

    protected function createModel(): object
    {
        $model = $this->app['config']->get('auth.providers.users.model', 'App\\Models\\User');

        return new $model();
    }

    protected function setUser(object $user): void
    {
        $this->user = $user;
        $this->loggedOut = false;
    }

    protected function queueRecallerCookie(object $user): void
    {
        $this->session->put('remember_web_user_'.get_class($this), $user->getKey());
    }

    protected function fireLoginEvent(object $user, bool $remember = false): void
    {
        $this->app['events']->dispatch('login', [$user, $remember]);
    }

    protected function fireLogoutEvent(object $user): void
    {
        $this->app['events']->dispatch('logout', [$user]);
    }

    public function viaRememberToken(): bool
    {
        return $this->viaRemember;
    }
}
