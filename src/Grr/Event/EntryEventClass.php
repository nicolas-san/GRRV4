<?php
/**
 * @author Bouteillier Nicolas <contact@kaizendo.fr>
 * Date: 17/09/15
 */
namespace Grr\Event;

use Symfony\Component\EventDispatcher\Event;


class EntryEventClass extends Event
{
    private $id;
    private $idArea;
    private $idSite;
    private $tpl;

    public function __construct($idSite, $idArea, $id, $tpl)
    {
        $this->id = $id;
        $this->idArea = $idArea;
        $this->idSite = $idSite;
        $this->tpl = $tpl;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTpl()
    {
        return $this->tpl;
    }

    /**
     * @param mixed $tpl
     */
    public function setTpl($tpl)
    {
        $this->tpl = $tpl;
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
     * @return mixed
     */
    public function getIdSite()
    {
        return $this->idSite;
    }

    /**
     * @param mixed $idSite
     */
    public function setIdSite($idSite)
    {
        $this->idSite = $idSite;
    }


}