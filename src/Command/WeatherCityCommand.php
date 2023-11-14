<?php

namespace App\Command;

use App\Repository\LocationRepository;
use App\Service\WeatherUtil;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:city',
    description: 'Displays measurements for city in country',
)]
class WeatherCityCommand extends Command
{
    public function __construct(
        private readonly LocationRepository $locationRepository,
        private readonly WeatherUtil $weatherUtil,
        string $name = null,
    )
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('country_code', InputArgument::REQUIRED, 'Country code [eg. PL]')
            ->addArgument('city_name', InputArgument::REQUIRED, 'City name [eg. Szczecin]')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $cityName = $input->getArgument('city_name');
        $countryCode = $input->getArgument('country_code');

        $location = $this->locationRepository->findOneBy([
            'city' => $cityName,
            'country' => $countryCode,
        ]);

        $measurements = $this->weatherUtil->getWeatherForLocation($location);
        $io->writeln(sprintf('Location: %s', $location->getCity()));
        foreach ($measurements as $measurement) {
            $io->writeln(sprintf("\tData: %s \n \t temperatura: %s Celsius \n \t cisnienie: %s \n \t Czy bedzie deszcz: %s",
                $measurement->getDate()->format('Y-m-d'),
                $measurement->getCelsius(),
                $measurement->getPressure(),
                $measurement->getRain(),
            ));
        }

        return Command::SUCCESS;
    }
}