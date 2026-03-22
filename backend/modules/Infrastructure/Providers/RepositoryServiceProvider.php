<?php

namespace Modules\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Application\UseCases\Auth\LoginUser\LoginUserHandler;
use Modules\Application\UseCases\Auth\LoginUser\LoginUserRepositoryInterface;
use Modules\Application\UseCases\Auth\LogoutUser\LogoutUserHandler;
use Modules\Application\UseCases\Auth\LogoutUser\LogoutUserRepositoryInterface;
use Modules\Application\UseCases\Auth\RegisterUser\RegisterUserHandler;
use Modules\Application\UseCases\Auth\RegisterUser\RegisterUserRepositoryInterface;
use Modules\Infrastructure\Persistence\Repositories\LoginUserRepository;
use Modules\Infrastructure\Persistence\Repositories\LogoutUserRepository;
use Modules\Infrastructure\Persistence\Repositories\RegisterUserRepository;

final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(
            RegisterUserRepositoryInterface::class,
            RegisterUserRepository::class
        );

        $this->app->bind(
            LoginUserRepositoryInterface::class,
            LoginUserRepository::class
        );

        $this->app->bind(
            LogoutUserRepositoryInterface::class,
            LogoutUserRepository::class
        );

        // Bind use case handlers
        $this->app->bind(RegisterUserHandler::class, function ($app) {
            return new RegisterUserHandler(
                $app->make(RegisterUserRepositoryInterface::class),
            );
        });

        $this->app->bind(LoginUserHandler::class, function ($app) {
            return new LoginUserHandler(
                $app->make(LoginUserRepositoryInterface::class),
            );
        });

        $this->app->bind(LogoutUserHandler::class, function ($app) {
            return new LogoutUserHandler(
                $app->make(LogoutUserRepositoryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
