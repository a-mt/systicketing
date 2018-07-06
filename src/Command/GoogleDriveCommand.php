<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create the file config/google_credentials.json from config/google_client_secret.json
 * You should define the environment variable GOOGLE_CREDENTIALS with the content of the generated file
 */
class GoogleDriveCommand extends Command
{
    protected function configure()
    {
        $this
        ->setName('app:create-drive-credentials')
        ->setDescription('Create the credentials.json file for Google Drive.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Source: https://developers.google.com/drive/api/v3/quickstart/php?authuser=1
        // To remove credentials: https://myaccount.google.com/permissions

        global $kernel;
        $configPath = $kernel->getRootDir() . '/../config/';

        // Create Google Drive Client
        $client = new \Google_Client();
        $client->setApplicationName('Google Drive API PHP Quickstart');
        $client->setScopes(\Google_Service_Drive::DRIVE);
        $client->setAuthConfig($configPath . 'google_client_secret.json');
        $client->setAccessType('offline');

        // Get user credentials
        $credentialsPath = $configPath . 'google_credentials.json';
        if(file_exists($credentialsPath)) {
            $accessToken = json_decode(file_get_contents($credentialsPath), true);

        } else {

            // Display verification link
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);

            // Retrieve verification code
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Use it to request access token
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            file_put_contents($credentialsPath, json_encode($accessToken));
            printf("Credentials saved to %s\n", $credentialsPath);
        }
        $client->setAccessToken($accessToken);

        // Refresh token if expired
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
        }

        printf("Credentials path: %s\n", $credentialsPath);
    }
}