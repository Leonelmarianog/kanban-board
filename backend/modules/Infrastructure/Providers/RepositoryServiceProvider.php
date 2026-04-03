<?php

namespace Modules\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Application\UseCases\Auth\CancelEmailChange\CancelEmailChangeHandler;
use Modules\Application\UseCases\Auth\CancelEmailChange\CancelEmailChangeRepositoryInterface;
use Modules\Application\UseCases\Auth\ChangeEmail\ChangeEmailHandler;
use Modules\Application\UseCases\Auth\ChangeEmail\ChangeEmailRepositoryInterface;
use Modules\Application\UseCases\Auth\ChangePassword\ChangePasswordHandler;
use Modules\Application\UseCases\Auth\ChangePassword\ChangePasswordRepositoryInterface;
use Modules\Application\UseCases\Auth\ConfirmEmailChange\ConfirmEmailChangeHandler;
use Modules\Application\UseCases\Auth\ConfirmEmailChange\ConfirmEmailChangeRepositoryInterface;
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
use Modules\Infrastructure\Http\Controllers\Auth\CancelEmailChangeController;
use Modules\Infrastructure\Http\Controllers\Auth\ChangeEmailController;
use Modules\Infrastructure\Http\Controllers\Auth\ChangePasswordController;
use Modules\Infrastructure\Http\Controllers\Auth\ConfirmEmailChangeController;
use Modules\Infrastructure\Http\Controllers\Auth\SendVerificationEmailController;
use Modules\Infrastructure\Http\Controllers\Auth\VerifyEmailController;
use Modules\Infrastructure\Mail\LaravelMailer;
use Modules\Infrastructure\Mail\MailerInterface;
use Modules\Infrastructure\Persistence\EloquentTransaction;
use Modules\Infrastructure\Persistence\Repositories\CancelEmailChangeRepository;
use Modules\Infrastructure\Persistence\Repositories\ChangeEmailRepository;
use Modules\Infrastructure\Persistence\Repositories\ChangePasswordRepository;
use Modules\Infrastructure\Persistence\Repositories\ConfirmEmailChangeRepository;
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

        $this->app->bind(
            ChangeEmailRepositoryInterface::class,
            ChangeEmailRepository::class
        );

        $this->app->bind(
            ConfirmEmailChangeRepositoryInterface::class,
            ConfirmEmailChangeRepository::class
        );

        $this->app->bind(
            CancelEmailChangeRepositoryInterface::class,
            CancelEmailChangeRepository::class
        );

        $this->app->bind(
            ChangePasswordRepositoryInterface::class,
            ChangePasswordRepository::class
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

        $this->app->bind(ChangeEmailHandler::class, function ($app) {
            return new ChangeEmailHandler(
                $app->make(ChangeEmailRepositoryInterface::class),
                $app->make(MailerInterface::class),
                $app->make(SignedUrlInterface::class),
                $app->make(TransactionInterface::class),
            );
        });

        $this->app->bind(ConfirmEmailChangeHandler::class, function ($app) {
            return new ConfirmEmailChangeHandler(
                $app->make(ConfirmEmailChangeRepositoryInterface::class),
                $app->make(TransactionInterface::class),
                $app->make(SignedUrlInterface::class),
            );
        });

        $this->app->bind(CancelEmailChangeHandler::class, function ($app) {
            return new CancelEmailChangeHandler(
                $app->make(CancelEmailChangeRepositoryInterface::class),
                $app->make(SignedUrlInterface::class),
            );
        });

        $this->app->bind(ChangePasswordHandler::class, function ($app) {
            return new ChangePasswordHandler(
                $app->make(ChangePasswordRepositoryInterface::class),
                $app->make(TransactionInterface::class),
                $app->make(MailerInterface::class),
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

        $this->app->bind(ChangeEmailController::class, function ($app) {
            return new ChangeEmailController(
                $app->make(ChangeEmailHandler::class),
            );
        });

        $this->app->bind(ConfirmEmailChangeController::class, function ($app) {
            return new ConfirmEmailChangeController(
                $app->make(ConfirmEmailChangeHandler::class),
            );
        });

        $this->app->bind(CancelEmailChangeController::class, function ($app) {
            return new CancelEmailChangeController(
                $app->make(CancelEmailChangeHandler::class),
            );
        });

        $this->app->bind(ChangePasswordController::class, function ($app) {
            return new ChangePasswordController(
                $app->make(ChangePasswordHandler::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
