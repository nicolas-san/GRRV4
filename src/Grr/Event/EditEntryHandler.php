<?php
/**
 * @author Bouteillier Nicolas <contact@kaizendo.fr>
 * Date: 17/09/15
 */
namespace Grr\Event;

use Symfony\Component\EventDispatcher\Event;


class EditEntryHandler extends Event
{
    private $data;
    private $idArea;

    public function __construct($idArea, array $data, $idEntry = null)
    {
        $this->data = $data;
        $this->idArea = $idArea;
        $this->idEntry = $idEntry;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getIdArea()
    {
        return $this->idArea;
    }

    /**
     * @param mixed $idArea
     */
    public function setIdArea($idArea)
    {
        $this->idArea = $idArea;
    }

    /**
     * @return null
     */
    public function getIdEntry()
    {
        return $this->idEntry;
    }

    /**
     * @param null $idEntry
     */
    public function setIdEntry($idEntry)
    {
        $this->idEntry = $idEntry;
    }

}