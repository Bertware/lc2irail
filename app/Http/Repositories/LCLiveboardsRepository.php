<?php

namespace App\Http\Repositories;

use App\Http\Models\IrailCarbon;
use App\Http\Models\LinkedConnection;
use App\Http\Models\Liveboard;
use App\Http\Models\Station;
use App\Http\Models\TrainArrival;
use App\Http\Models\TrainDeparture;
use App\Http\Models\TrainStop;
use App\Http\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Class LinkedConnectionsRepositories
 * A read-only repository for realtime train data in linkedconnections format
 *
 * @package App\Http\Controllers
 */
class LCLiveboardsRepository
{

    /**
     * Retrieve an array of LinkedConnection objects for a certain departure time
     *
     * @param \App\Http\Models\Station $station
     * @param Carbon                   $departureTime The time for which departures should be returned
     * @param string                   $language
     * @param int                      $window
     * @return \App\Http\Models\Liveboard
     */
    public function getDepartures(
        Station $station,
        Carbon $departureTime,
        string $language = '',
        int $window = 3600
    ): Liveboard
    {

        $repository = app(LinkedConnectionsRepositoryContract::class);

        /**
         * @var $linkedConnectionsData \App\Http\Models\LinkedConnectionPage
         */
        $linkedConnectionsData = $repository->getLinkedConnectionsInWindow($departureTime, $window);
        $linkedConnections = $linkedConnectionsData->getLinkedConnections();

        //Log::info("Got " . sizeof($linkedConnections) . " departures");

        /**
         * @var $departures TrainDeparture[]
         */
        $departures = [];
        /**
         * @var $stops TrainStop[]
         */
        $stops = [];
        /**
         * @var $arrivals TrainArrival[]
         */
        $arrivals = [];

        $numberOfDepartures = 0;
        $numberOfArrivals = 0;
        $departureIdAfterThisArrival = [];

        $stationUri = $station->getUri();

        foreach ($linkedConnections as $connection) {
            if ($connection->getDepartureStopUri() == $stationUri) {
                $departures[] = new TrainDeparture(
                    $connection->getId(),
                    new Vehicle(
                        $connection->getTrip(),
                        $connection->getTrip(),
                        "no name known",
                        new Station($connection->getArrivalStopUri(),
                            $language)
                    ),
                    0,
                    Carbon::createFromTimestamp($connection->getDepartureTime(),"Europe/Brussels"),
                    $connection->getDepartureDelay()
                );
                $numberOfDepartures++;
                continue;
            }
            if ($connection->getArrivalStopUri() == $stationUri) {
                $arrivals[] = new TrainArrival(
                    $connection->getId(),
                    new Vehicle(
                        $connection->getTrip(),
                        $connection->getTrip(),
                        "no name known",
                        new Station($connection->getArrivalStopUri(),
                            $language)
                    ),
                    0,
                    Carbon::createFromTimestamp($connection->getArrivalTime(), "Europe/Brussels"),
                    $connection->getArrivalDelay()
                );
                $departureIdAfterThisArrival[] = $numberOfDepartures;
                $numberOfArrivals++;
                continue;
            }
        }

        $wipeArrivalIds = [];
        $wipeDepartureIds = [];
        // Departures and Arrivals are already sorted chronologically
        foreach ($arrivals as $id => $arrival) {
            $arrivingTrip = $arrival->getVehicle()->getId();
            $matched = false;
            $i = $departureIdAfterThisArrival[$id];
            while ($i < $numberOfDepartures && $matched == false) {
                if ($departures[$i]->getVehicle()->getId() == $arrivingTrip) {
                    $stops[] = new TrainStop($departures[$i]->getUri(), $departures[$i]->getVehicle(),
                        $departures[$i]->getPlatform(),
                        $arrival->getArrivalTime(), $arrival->getArrivalDelay(),
                        $departures[$i]->getDepartureTime(), $departures[$i]->getDepartureDelay());

                    $wipeArrivalIds[] = $id;
                    $wipeDepartureIds[] = $id;
                    $matched = true;
                }
                $i++;
            }
        }

        foreach ($wipeDepartureIds as $id) {
            unset ($departures[$id]);
        }
        foreach ($wipeArrivalIds as $id) {
            unset ($arrivals[$id]);
        }

        //Log::info("Got " . sizeof($departures) . " relevant departures");

        return new Liveboard($station, $departures, $stops, $arrivals, $linkedConnectionsData->getCreatedAt(),
            $linkedConnectionsData->getExpiresAt(), $linkedConnectionsData->getEtag());
    }
}
