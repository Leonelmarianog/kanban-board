<?php

namespace Modules\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Application\UseCases\Auth\LoginUser\LoginUserHandler;
use Modules\Application\UseCases\Auth\LoginUser\LoginUserRepositoryInterface;
use Modules\Application\UseCases\Auth\LogoutUser\LogoutUserHandler;
use Modules\Application\UseCases\Auth\LogoutUser\LogoutUserRepositoryInterface;
use Modules\Application\UseCases\Auth\RegisterUser\RegisterUserHandler;
use Modules\Application\UseCases\Auth\RegisterUser\RegisterUserRepositoryInterface;
use Modules\Application\UseCases\Member\GetMember\GetMemberHandler;
use Modules\Application\UseCases\Member\GetMember\GetMemberRepositoryInterface;
use Modules\Application\UseCases\Member\UpdateProfileDetails\UpdateProfileDetailsHandler;
use Modules\Application\UseCases\Member\UpdateProfileDetails\UpdateProfileDetailsRepositoryInterface;
use Modules\Infrastructure\Persistence\Repositories\GetMemberRepository;
use Modules\Infrastructure\Persistence\Repositories\LoginUserRepository;
use Modules\Infrastructure\Persistence\Repositories\LogoutUserRepository;
use Modules\Infrastructure\Persistence\Repositories\RegisterUserRepository;
use Modules\Infrastructure\Persistence\Repositories\UpdateProfileDetailsRepository;

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

        $this->app->bind(
            GetMemberRepositoryInterface::class,
            GetMemberRepository::class
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

        $this->app->bind(
            GetMemberRepositoryInterface::class,
            GetMemberRepository::class
        );

        $this->app->bind(
            UpdateProfileDetailsRepositoryInterface::class,
            UpdateProfileDetailsRepository::class
        );

        $this->app->bind(GetMemberHandler::class, function ($app) {
            return new GetMemberHandler(
                $app->make(GetMemberRepositoryInterface::class),
            );
        });

        $this->app->bind(UpdateProfileDetailsHandler::class, function ($app) {
            return new UpdateProfileDetailsHandler(
                $app->make(UpdateProfileDetailsRepositoryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
