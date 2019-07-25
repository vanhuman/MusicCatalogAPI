<?php

namespace Models;

class Album extends BaseModel
{
    private static $DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var $title string
     */
    protected $title;

    /**
     * @var $artist Artist
     */
    protected $artist;

    /**
     * @var $year int
     */
    protected $year;

    /**
     * @var $genre Genre
     */
    protected $genre;

    /**
     * @var Label $label
     */
    protected $label;

    /**
     * @var Format $format
     */
    protected $format;

    /**
     * @var \DateTime
     */
    protected $dateAdded;

    /**
     * @var string
     */
    protected $notes = '';

    /**
     * @var string
     */
    protected $imageThumb;

    /**
     * @var string
     */
    protected $imageThumbLocal;

    /**
     * @var string
     */
    protected $image;

    /**
     * @var string
     */
    protected $imageLocal;

    /**
     * @var \DateTime
     */
    protected $imageFetchTimestamp;

    /**
     * @var bool
     */
    protected $imageLock;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return Artist
     */
    public function getArtist()
    {
        return $this->artist;
    }

    /**
     * @param Artist $artist
     */
    public function setArtist($artist)
    {
        $this->artist = $artist;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = (int)$year;
    }

    /**
     * @return Genre
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * @param Genre $genre
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;
    }

    /**
     * @return Label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param Label $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return Format
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param Format $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @param string $image
     */
    public function setImageThumb($image)
    {
        $this->imageThumb = $image;
    }

    /**
     * @return string | null
     */
    public function getImageThumb()
    {
        return $this->imageThumb;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return string | null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @return string
     */
    public function getDateAddedString()
    {
        return date(self::$DATE_FORMAT, $this->dateAdded->getTimestamp());
    }

    /**
     * @param string $date
     */
    public function setDateAdded($date)
    {
        $datetime = \DateTime::createFromFormat(self::$DATE_FORMAT, $date);
        $this->dateAdded = $datetime;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return string | null
     */
    public function getImageThumbLocal()
    {
        return $this->imageThumbLocal;
    }

    /**
     * @param string $imageThumbLocal
     */
    public function setImageThumbLocal(string $imageThumbLocal): void
    {
        $this->imageThumbLocal = $imageThumbLocal;
    }

    /**
     * @return string | null
     */
    public function getImageLocal()
    {
        return $this->imageLocal;
    }

    /**
     * @param string $imageLocal
     */
    public function setImageLocal(string $imageLocal): void
    {
        $this->imageLocal = $imageLocal;
    }


    /**
     * @return \DateTime
     */
    public function getImageFetchTimestamp(): \DateTime
    {
        return $this->imageFetchTimestamp;
    }

    /**
     * @return string
     */
    public function getImageFetchTimestampString()
    {
        if ($this->imageFetchTimestamp) {
            return date(self::$DATE_FORMAT, $this->imageFetchTimestamp->getTimestamp());
        }
        return null;
    }

    /**
     * @param \DateTime | null $imageFetchTimestamp
     */
    public function setImageFetchTimestamp($imageFetchTimestamp): void
    {
        if ($imageFetchTimestamp) {
            $datetime = \DateTime::createFromFormat(self::$DATE_FORMAT, $imageFetchTimestamp);
            $this->imageFetchTimestamp = $datetime;
        }
    }

    public function getImageLock(): bool
    {
        return $this->imageLock;
    }

    public function setImageLock(int $imageLock): void
    {
        $this->imageLock = $imageLock;
    }
}