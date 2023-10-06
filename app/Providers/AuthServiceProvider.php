<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class AuthServiceProvider extends ServiceProvider
{
  /**
   * The model to policy mappings for the application.
   *
   * @var array<class-string, class-string>
   */
  protected $policies = [
    // 'App\Models\Model' => 'App\Policies\ModelPolicy',
  ];

  /**
   * Register any authentication / authorization services.
   *
   * @return void
   */
  public function boot()
  {
    $this->registerPolicies();


    VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
      $spaUrl = env('SPA_URL');
      $domainSPA = "{$spaUrl}/#/verifyEmail/" . $notifiable->getKey() . "/" . sha1($notifiable->getEmailForVerification());
      return (new MailMessage)
        ->subject('Verify Email Address')
        ->line('Click the button below to verify your email address.')
        ->action('Verify Email Address', $domainSPA);
    });

    ResetPassword::createUrlUsing(function (User $user, string $token) {
      $spaUrl = env('SPA_URL');
      return "{$spaUrl}/#/reset-password?token=" . $token;
    });
  }
}
