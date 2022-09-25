<?php
declare(strict_types=1);

namespace Kami;

use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Adapter\Guzzle;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'ddns:update-a-record')]
class UpdateARecord extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Config
        $apiKey = $_ENV['API_KEY'];
        $apiEmail = $_ENV['API_EMAIL'];
        $zoneId = $_ENV['ZONE_ID'];
        $domain = $_ENV['DOMAIN'];

        $io = new SymfonyStyle($input, $output);

        // Get public IP
        $externalIp = file_get_contents('https://ipecho.net/plain');

        // Setup Cloudflare SDK
        $key = new APIKey($apiEmail, $apiKey);
        $adapter = new Guzzle($key);
        $dns = new DNS($adapter);

        // Missing A record
        $recordId = $dns->getRecordID($zoneId, 'A', $domain);
        if ((bool) $recordId === false) {
            $output->writeln('A Record not found, creating a new one...');
            $result = $dns->addRecord($zoneId, 'A', $domain, $externalIp, 0, false);

            if ($result) {
                $output->writeln('Successfully added a new A record!');

                return Command::SUCCESS;
            }

            $output->writeln('Unable to add new A record!');

            return Command::FAILURE;
        }

        // Check A record
        $output->writeln('Existing A record found:');
        $output->writeln('========================');
        $details = $dns->getRecordDetails($zoneId, $recordId);
        $io->listing([
            'Current A record IP: ' . $details->content,
            'Current external IP: ' . $externalIp,
            'Last updated: ' . (new DateTimeImmutable($details->modified_on))->format('Y-m-d @ H:i'),
        ]);

        if ($details->content === $externalIp) {
            $output->writeln('IP change is not needed!');

            return Command::SUCCESS;
        }

        $output->writeln('IP has changed, updating...');

        // Update A record
        $resp = $dns->updateRecordDetails($zoneId, $recordId, [
            'type' => 'A',
            'content' => $externalIp,
            'name' => $domain,
            'ttl' => 1,
        ]);

        if ((bool) $resp->success === true) {
            $output->writeln('IP has been updated!');

            return Command::SUCCESS;
        }

        $output->writeln('Error occured trying to update existing A record!');

        return Command::FAILURE;
    }
}
