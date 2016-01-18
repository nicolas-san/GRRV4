<?php
/**
 * @author Bouteillier Nicolas <contact@kaizendo.fr>
 * Date: 17/09/15
 */
namespace Grr\Event;

use Symfony\Component\EventDispatcher\Event;


class EditEntryHandlerForCreate extends Event
{
    private $data;

    public function __construct(array $data, $idEntry = null)
    {
        $this->data = $data;
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
    public function getIdEntry()
    {
        return $this->idEntry;
    }

    /**
     * @param mixed $idEntry
     */
    public function setIdEntry($idEntry)
    {
        $this->idEntry = $idEntry;
    }


}