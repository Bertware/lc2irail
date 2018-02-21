<?php
/**
 * Created by PhpStorm.
 * User: bert
 * Date: 27/09/17
 * Time: 15:02
 */

namespace App\Http\Models;

use Carbon\Carbon;

class LinkedConnectionPage
{
    /**
     * @var LinkedConnection[]
     */
    private $linkedConnections;

    /**
     * @var string
     */
    private $etag;

    /**
     * @var Carbon
     */
    private $createdAt;

    /**
     * @var Carbon
     */
    private $expiresAt;

    
    private $next;
    private $previous;

    /**
     * @return Carbon
     */
    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }


    public function __construct(array $linkedConnections, Carbon $createdAt, Carbon $expiresAt, string $etag, $previous, $next)
    {
        $this->linkedConnections = $linkedConnections;
        $this->etag = $etag;
        $this->createdAt = $createdAt;
        $this->expiresAt = $expiresAt;
        $this->previous = $previous;
        $this->next = $next;
    }

    /**
     * @return string
     */
    public function getEtag(): string
    {
        return $this->etag;
    }

    /**
     * @return LinkedConnection[]
     */
    public function getLinkedConnections(): array
    {
        return $this->linkedConnections;
    }

    /**
     * @return \Carbon\Carbon
     */
    public function getExpiresAt(): \Carbon\Carbon
    {
        return $this->expiresAt;
    }

    /**
     * @param Carbon $expiresAt
     */
    public function setExpiresAt(Carbon $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return mixed
     */
    public function getPreviousPointer()
    {
        return $this->previous;
    }

    /**
     * @return mixed
     */
    public function getNextPointer()
    {
        return $this->next;
    }

}