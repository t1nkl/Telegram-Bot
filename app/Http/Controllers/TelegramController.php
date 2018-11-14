<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use Illuminate\Support\Facades\Lang;
use BotMan\BotMan\Cache\SymfonyCache;
use BotMan\BotMan\Drivers\DriverManager;
use App\Conversations\ExampleConversation;
use App\Conversations\OnboardingConversation;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class TelegramController
{
    public function __invoke()
    {
        $config = [
            'telegram' => [
               'token' => env('TELEGRAM_TOKEN')
            ]
        ];

        DriverManager::loadDriver(\BotMan\Drivers\Telegram\TelegramDriver::class);

        $adapter = new FilesystemAdapter();
        $botman = BotManFactory::create($config, new SymfonyCache($adapter));

        $botman->hears('/start', function (BotMan $bot) {
            $bot->types();
            $firstName = $bot->getUser()->getFirstName();
            $bot->reply('Hey ' . $firstName . ', it\'s test version of my bot :) Do you want some /options ?');
        });

        $botman->hears('/time', function (BotMan $bot) {
            $bot->types();
            $results = \Carbon\Carbon::now('Europe/Kiev')->toRfc2822String();
            $bot->reply($results);
        });

        $botman->hears('/options', function (BotMan $bot) {
            $bot->types();
            $results = $this->getOptions();
            $bot->reply($results);
        });

        $botman->hears('/test_conversation', function (BotMan $bot) {
            $bot->startConversation(new ExampleConversation());
        });

        $botman->hears('/onboarding_conversation', function (BotMan $bot) {
            $bot->startConversation(new OnboardingConversation());
        });

        $botman->fallback(function ($bot) {
            $bot->types();
            $bot->reply('Sorry, I did not understand these commands. Please retype again...');
        });

        $botman->listen();
    }

    private function getOptions()
    {
        $data = "Here's the commands you can use in this test-bot:\n";
        $commands = Lang::get('commands', [], 'en');

        foreach ($commands as $name => $trans) {
            $data .= $name . ' - ' . $trans . "\n";
        }

        return $data;
    }
}
