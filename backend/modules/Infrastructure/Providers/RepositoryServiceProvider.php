<?php

namespace Modules\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Application\UseCases\Auth\LoginUser\LoginUserHandler;
use Modules\Application\UseCases\Auth\LoginUser\LoginUserRepositoryInterface;
use Modules\Application\UseCases\Auth\LogoutUser\LogoutUserHandler;
use Modules\Application\UseCases\Auth\LogoutUser\LogoutUserRepositoryInterface;
use Modules\Application\UseCases\Auth\RegisterUser\RegisterUserHandler;
use Modules\Application\UseCases\Auth\RegisterUser\RegisterUserRepositoryInterface;
use Modules\Application\UseCases\Auth\SendVerificationEmail\SendVerificationEmailHandler;
use Modules\Application\UseCases\Auth\SendVerificationEmail\SendVerificationEmailRepositoryInterface;
use Modules\Application\UseCases\Auth\VerifyEmail\VerifyEmailHandler;
use Modules\Application\UseCases\Auth\VerifyEmail\VerifyEmailRepositoryInterface;
use Modules\Application\UseCases\Member\GetMember\GetMemberHandler;
use Modules\Application\UseCases\Member\GetMember\GetMemberRepositoryInterface;
use Modules\Application\UseCases\Member\UpdateProfileDetails\UpdateProfileDetailsHandler;
use Modules\Application\UseCases\Member\UpdateProfileDetails\UpdateProfileDetailsRepositoryInterface;
use Modules\Infrastructure\Http\Controllers\Auth\SendVerificationEmailController;
use Modules\Infrastructure\Http\Controllers\Auth\VerifyEmailController;
use Modules\Infrastructure\Mail\LaravelMailer;
use Modules\Infrastructure\Mail\MailerInterface;
use Modules\Infrastructure\Persistence\EloquentTransaction;
use Modules\Infrastructure\Persistence\Repositories\GetMemberRepository;
use Modules\Infrastructure\Persistence\Repositories\LoginUserRepository;
use Modules\Infrastructure\Persistence\Repositories\LogoutUserRepository;
use Modules\Infrastructure\Persistence\Repositories\RegisterUserRepository;
use Modules\Infrastructure\Persistence\Repositories\SendVerificationEmailRepository;
use Modules\Infrastructure\Persistence\Repositories\UpdateProfileDetailsRepository;
use Modules\Infrastructure\Persistence\Repositories\VerifyEmailRepository;
use Modules\Infrastructure\Persistence\TransactionInterface;
use Modules\Infrastructure\SignedUrl\SignedUrlInterface;
use Modules\Infrastructure\SignedUrl\SpatieSignedUrl;
use Spatie\UrlSigner\Sha256UrlSigner;

final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Infrastructure services
        $this->app->bind(
            TransactionInterface::class,
            EloquentTransaction::class
        );

        $this->app->bind(
            MailerInterface::class,
            LaravelMailer::class
        );

        $this->app->singleton(Sha256UrlSigner::class, function () {
            $signingKey = config('app.key');

            return new Sha256UrlSigner($signingKey);
        });

        $this->app->bind(SignedUrlInterface::class, function ($app) {
            return new SpatieSignedUrl($app->make(Sha256UrlSigner::class));
        });

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

        $this->app->bind(
            UpdateProfileDetailsRepositoryInterface::class,
            UpdateProfileDetailsRepository::class
        );

        $this->app->bind(
            SendVerificationEmailRepositoryInterface::class,
            SendVerificationEmailRepository::class
        );

        $this->app->bind(
            VerifyEmailRepositoryInterface::class,
            VerifyEmailRepository::class
        );

        // Bind use case handlers
        $this->app->bind(RegisterUserHandler::class, function ($app) {
            return new RegisterUserHandler(
                $app->make(RegisterUserRepositoryInterface::class),
                $app->make(MailerInterface::class),
                $app->make(SignedUrlInterface::class),
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

        $this->app->bind(SendVerificationEmailHandler::class, function ($app) {
            return new SendVerificationEmailHandler(
                $app->make(SendVerificationEmailRepositoryInterface::class),
                $app->make(MailerInterface::class),
                $app->make(SignedUrlInterface::class),
            );
        });

        $this->app->bind(VerifyEmailHandler::class, function ($app) {
            return new VerifyEmailHandler(
                $app->make(VerifyEmailRepositoryInterface::class),
                $app->make(TransactionInterface::class),
                $app->make(SignedUrlInterface::class),
            );
        });

        // Bind controllers
        $this->app->bind(SendVerificationEmailController::class, function ($app) {
            return new SendVerificationEmailController(
                $app->make(SendVerificationEmailHandler::class),
            );
        });

        $this->app->bind(VerifyEmailController::class, function ($app) {
            return new VerifyEmailController(
                $app->make(VerifyEmailHandler::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
