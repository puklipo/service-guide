<?php

namespace App\Livewire\Forms;

use App\Notifications\ContactNotification;
use App\Rules\Spammer;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ContactForm extends Form
{
    #[Validate(['required', 'string', 'max:255'])]
    public string $name;

    public string $email;

    #[Validate(['required', 'string', 'max:4000'])]
    public string $content;

    public function rules(): array
    {
        return [
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                new Spammer,
            ],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function submit(): void
    {
        $this->validate();

        Notification::route('mail', config('mail.contact.to'))
            ->notify(new ContactNotification(name: $this->name, email: $this->email, content: $this->content));

        session()->flash('mail_success');
    }
}
