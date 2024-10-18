<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class NewUserWelcome extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url('/reset-password/' . $this->token);

        return (new MailMessage)
            ->subject(Lang::get('Bienvenue sur notre plateforme'))
            ->line(Lang::get('Bienvenue sur notre plateforme, :name !', ['name' => $notifiable->name]))
            ->line(Lang::get('Votre compte a été créé avec succès. Voici vos informations de connexion :'))
            ->line(Lang::get('Email: :email', ['email' => $notifiable->email]))
            ->line(Lang::get('Pour des raisons de sécurité, nous vous recommandons de modifier votre mot de passe dès que possible.'))
            ->action(Lang::get('Modifier mon mot de passe'), $url)
            ->line(Lang::get('Si vous avez des questions, n\'hésitez pas à nous contacter.'))
            ->salutation(Lang::get('Cordialement,'));
    }
}
