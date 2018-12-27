<?php

namespace Models;

class Album extends DatabaseModel
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
     * @var \DateTime $date
     */
    protected $date;

    /**
     * @var string $notes
     */
    protected $notes = '';

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
        $this->year = $year;
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
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getDateString()
    {
        return date(self::$DATE_FORMAT, $this->date->getTimestamp());
    }

    /**
     * @param string $date
     */
    public function setDate($date)
    {
        $datetime = \DateTime::createFromFormat(self::$DATE_FORMAT, $date);
        $this->date = $datetime;
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

}