<?php

namespace OGIVE\AlertBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExpressionInterest
 *
 * @ORM\Table(name="expression_interest")
 * @ORM\Entity(repositoryClass="\OGIVE\AlertBundle\Repository\ExpressionInterestRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ExpressionInterest extends AlertProcedure {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setType("ASMI");
    }

    public function getAbstractForSmsNotification() {
        if (strlen(trim($this->getObject())) > 58) {
            $object_abstract = trim(substr($this->getObject(), 0, 55));
            $dot = "...";
            if (substr($object_abstract, -1) === ".") {
                $dot = "..";
            }
        } else {
            $object_abstract = trim($this->getObject());
            $dot = ".";
            if (substr(trim($object_abstract), -1) === ".") {
                $dot = "";
            }
        }
        $subject_array = explode(" ", $this->getObject());
        //$abstract = $this->getReference() . " du " . date("d/m/Y", strtotime($this->getPublicationDate())) . " lancé par " . $this->getOwner() . " pour " . $object_abstract . $dot . " Dépôt des offres le " . date("d/m/Y", strtotime($this->getOpeningDate())) . " à " . date("H:i", strtotime($this->getOpeningDate())) . ".";
        $abstract = $this->getType() . " : " . "N°". explode("/", $this->getReference())[0] . " du " . date("d/m/Y", strtotime($this->getPublicationDate())) . " lancé par " . $this->getOwner(). " pour " .$subject_array[0]." ".$subject_array[1] . ". Dépôt des offres le " . date("d/m/Y", strtotime($this->getOpeningDate())) . " à " . date("H:i", strtotime($this->getOpeningDate())) . ". Détail à " . $this->getUrlDetails();
        return $abstract;
    }

}
