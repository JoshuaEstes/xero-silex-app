<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

$console = new Application('My Silex Application', 'n/a');
$console->getDefinition()->addOption(
    new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev')
);
$console->setDispatcher($app['dispatcher']);
$console
    ->register('my-command')
    ->setDefinition(array(
        // new InputOption('some-option', null, InputOption::VALUE_NONE, 'Some help'),
    ))
    ->setDescription('My command description')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        // do something
    });

$console
    ->register('generate:keypairs')
    ->setDefinition()
    ->setDescription('Generate keypairs for use with xero')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $certDir = __DIR__ . '/../config/certs';
        $commands = array(
            sprintf('openssl genrsa -out %s/privatekey.pem 1024', $certDir),
            sprintf(
                'openssl req -new -x509 -key %s/privatekey.pem -out %s/publickey.cer -days 1825',
                $certDir,
                $certDir
            ),
        );

        foreach ($commands as $cmd) {
            $process = new Process($cmd);
            $process->run(function ($type, $buffer) {
                if (Process::ERR == $type) {
                    echo $buffer . PHP_EOL;
                } else {
                    echo $buffer . PHP_EOL;
                }
            });
        }
    });

return $console;
