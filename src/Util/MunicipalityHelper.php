<?php


namespace App\Util;

use App\Entity\Municipality;

class MunicipalityHelper
{

    public function createMunicipality(string $name): Municipality
    {
        $municipality = new Municipality();
        $municipality->setName($name);
        
        return $municipality;
    }
}