<?php

namespace App\Http\Models;

use Carbon\Carbon;

/**
 * Class TrainDeparture
 */
class TrainDeparture extends TrainStopBase implements \JsonSerializable
{
    private $departureTime;
    private $departureDelay;
    private $departureTimestamp;

    public function __construct(
        string $uri,
        Carbon $departureTime,
        int $departureDelay,
        int $platform,
        VehicleStub $vehicle = null,
        Station $station = null
    ) {
        parent::__construct($uri, $platform, $vehicle, $station);
        $this->departureTime = $departureTime;
        $this->departureTimestamp = $this->departureTime->toAtomString();
        $this->departureDelay = $departureDelay;
    }

    /**
     * @return Carbon
     */
    public function getDepartureTime(): Carbon
    {
        return $this->departureTime;
    }

    /**
     * @return int
     */
    public function getDepartureDelay(): int
    {
        return $this->departureDelay;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        unset ($vars['departureTime']);
        if ($this->getVehicle() == null) {
            unset($vars['vehicle']);
        }
        if ($this->getStation() == null) {
            unset($vars['station']);
        }
        return $vars;
    }
}