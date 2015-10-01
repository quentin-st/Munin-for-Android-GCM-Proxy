<?php

namespace AppBundle\Service;

use AppBundle\Entity\MuninMaster;

class ConfigService
{
    public function generateConfig(MuninMaster $master)
    {
        return json_encode([
            'device' => [
                'friendly_name' => $master->getAndroidDevice()->getName(),
                'reg_id' => $master->getAndroidDevice()->getRegistrationId()
            ],
            'master' => $master->getHex()
        ]);
    }
}
