<?php

namespace App\Conversations;

use Validator;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Conversations\Conversation;

class OnboardingConversation extends Conversation
{
    public function askName()
    {
        $this->ask('Hello! What is your name?', function (Answer $answer) {
            $this->bot->userStorage()->delete();

            $this->bot->userStorage()->save([
                'name' => $answer->getText(),
            ]);

            $this->bot->types();
            $this->say('Nice to meet you ' . $answer->getText());

            $this->bot->types();
            $this->askEmail();
        });
    }

    public function askEmail()
    {
        $this->ask('What is your email?', function (Answer $answer) {
            $validator = Validator::make(['email' => $answer->getText()], [
                'email' => 'email',
            ]);

            if ($validator->fails()) {
                return $this->repeat('That doesn\'t look like a valid email. Please enter a valid email.');
            }

            $this->bot->userStorage()->save([
                'email' => $answer->getText(),
            ]);

            $this->bot->types();
            $this->askMobile();
        });
    }

    public function askMobile()
    {
        $this->ask('Great. What is your mobile?', function (Answer $answer) {
            $validator = Validator::make(['phone' => $answer->getText()], [
                'phone' => 'numeric',
            ]);

            if ($validator->fails()) {
                return $this->repeat('That doesn\'t look like a valid phone. Please enter a valid phone.');
            }

            $this->bot->userStorage()->save([
                'mobile' => $answer->getText(),
            ]);

            $this->bot->types();
            $this->say('Great!');

            $this->bot->types();
            $this->bot->startConversation(new SelectServiceConversation());
        });
    }

    public function run()
    {
        $this->askName();
    }
}
