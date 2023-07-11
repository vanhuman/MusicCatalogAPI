<?php

namespace Models;

use DateTime;
use Enums\LoggingType;

class Logging extends BaseModel
{
    private static $DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var LoggingType $type
     */
    protected $type;

    /**
     * @var DateTime $dateCreated
     */
    protected $dateCreated;

    /**
     * @var int $userId
     */
    protected $userId;

    /**
     * @var string $ipAddress
     */
    protected $ipAddress;

    /**
     * @var string $data
     */
    protected $data;

    public function getType(): LoggingType
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = LoggingType::byValue($type);
    }

    public function getDateCreated(): DateTime
    {
        return $this->dateCreated;
    }

    public function getDatedCreatedString()
    {
        return date(self::$DATE_FORMAT, $this->dateCreated->getTimestamp());
    }

    public function setDateCreated(string $dateCreated): void
    {
        $datetime = DateTime::createFromFormat(self::$DATE_FORMAT, $dateCreated);
        $this->dateCreated = $datetime;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }
}
